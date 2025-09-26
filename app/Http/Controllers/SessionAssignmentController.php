<?php

namespace App\Http\Controllers;

use App\Models\ChessSession;
use App\Models\StudentProfile;
use App\Models\User;
use App\Notifications\SessionAssignmentRequest;
use App\Notifications\SessionAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SessionAssignmentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Allow routes without auth for the teacher acceptance flow
        $this->middleware('auth')->except(['assignTeacher', 'confirmSessionTime']);
        
        // Enable web middleware for session support on all routes
        $this->middleware('web');
    }
    /**
     * Send assignment requests to eligible teachers.
     *
     * @param  \App\Models\ChessSession  $session
     * @return void
     */
    public function sendAssignmentRequests(ChessSession $session)
    {
        // Get the student information
        $student = User::find($session->student_id);
        
        if (!$student) {
            Log::error('Failed to send teacher assignment requests: Student not found', [
                'session_id' => $session->id,
                'student_id' => $session->student_id
            ]);
            return;
        }
        
        $studentInfo = [
            'name' => $student->name,
            'email' => $student->email
        ];
        
        // Get all active teachers who match the session type
        $teachers = User::role('teacher')
            ->with(['teacherProfile', 'availability'])
            ->whereHas('teacherProfile', function ($query) use ($session) {
                $query->where('is_active', true);
            })
            ->get();
        
        if ($teachers->isEmpty()) {
            Log::warning('No eligible teachers found for session assignment', [
                'session_id' => $session->id,
                'session_type' => $session->session_type
            ]);
            return;
        }
        
        // Send notifications to eligible teachers based on their preferences
        foreach ($teachers as $teacher) {
            // Skip if teacher has disabled session notifications completely
            if (!$teacher->teacherProfile->receive_session_notifications) {
                Log::info('Skipping teacher - notifications disabled', [
                    'teacher_id' => $teacher->id,
                    'session_id' => $session->id
                ]);
                continue;
            }
            
            // Check teacher's notification preference
            $notificationPreference = $teacher->teacherProfile->session_notification_preference ?? 'all';
            
            if ($notificationPreference === 'availability_match') {
                // Only send notification if student's requested times match teacher's availability
                if (!$this->doesStudentAvailabilityMatchTeacher($session, $teacher)) {
                    Log::info('Skipping teacher - no availability match', [
                        'teacher_id' => $teacher->id,
                        'session_id' => $session->id,
                        'preference' => $notificationPreference
                    ]);
                    continue;
                }
            }
            
            try {
                $teacher->notify(new SessionAssignmentRequest($session, $studentInfo));
                
                Log::info('Session assignment request sent to teacher', [
                    'teacher_id' => $teacher->id,
                    'teacher_email' => $teacher->email,
                    'session_id' => $session->id,
                    'preference' => $notificationPreference,
                    'availability_checked' => $notificationPreference === 'availability_match'
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send session assignment request to teacher', [
                    'teacher_id' => $teacher->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Handle a teacher's acceptance of a session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $sessionId
     * @return \Illuminate\Http\Response
     */
    public function assignTeacher(Request $request, $sessionId)
    {
        // First verify the signature
        if (!$request->hasValidSignature()) {
            return response()->view('sessions.assignment-error', [
                'message' => 'The link has expired or is invalid.'
            ], 403);
        }
        
        $teacherId = $request->teacher;
        $session = ChessSession::find($sessionId);
        
        if (!$session) {
            return response()->view('sessions.assignment-error', [
                'message' => 'The requested session could not be found.'
            ], 404);
        }
        
        // Check if the session is already assigned to a teacher
        if ($session->teacher_id) {
            $assignedTeacher = User::find($session->teacher_id);
            return response()->view('sessions.already-assigned', [
                'session' => $session,
                'teacher' => $assignedTeacher
            ]);
        }
        
        // Get the student information
        $student = User::find($session->student_id);
        if (!$student) {
            return response()->view('sessions.assignment-error', [
                'message' => 'The student associated with this session could not be found.'
            ], 404);
        }
        
        // Process the suggested availability from the session
        $suggestedAvailability = [];
        if ($session->suggested_availability) {
            // Convert availability ranges to specific time slots
            $rawAvailability = $session->suggested_availability;
            
            // Handle different data formats
            if (is_array($rawAvailability)) {
                // First, merge overlapping time ranges for the same date
                $mergedAvailability = $this->mergeOverlappingTimeRanges($rawAvailability);
                
                foreach ($mergedAvailability as $item) {
                    if (isset($item['date']) && isset($item['time_from']) && isset($item['time_to'])) {
                        // Convert time range to specific slots
                        $timeSlots = $this->generateTimeSlots(
                            $item['date'], 
                            $item['time_from'], 
                            $item['time_to'], 
                            $session->duration
                        );
                        
                        if (!empty($timeSlots)) {
                            $suggestedAvailability[] = [
                                'date' => $item['date'],
                                'times' => $timeSlots
                            ];
                        }
                    }
                }
            }
        }
        
        // Store assignment details in session
        $request->session()->put('pending_session_assignment', [
            'session_id' => $session->id,
            'teacher_id' => $teacherId,
            'timestamp' => now()->timestamp,
            'expires_at' => now()->addHours(24)->timestamp // Expires after 24 hours
        ]);
        
        // Create a regular URL for the form submission
        $confirmUrl = route('sessions.confirm-time', ['session' => $session->id]);
        
        // Show the time selection form
        return response()->view('sessions.time-selection', [
            'session' => $session,
            'student' => $student,
            'suggestedAvailability' => $suggestedAvailability,
            'confirmUrl' => $confirmUrl,
            'teacherId' => $teacherId
        ]);
    }
    
    /**
     * Handle the teacher's selection of a time slot and confirm the session assignment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $sessionId
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function confirmSessionTime(Request $request, $sessionId)
    {
        // Verify the session assignment data
        $pendingAssignment = $request->session()->get('pending_session_assignment');
        
        if (!$pendingAssignment || 
            $pendingAssignment['session_id'] != $sessionId || 
            $pendingAssignment['expires_at'] < now()->timestamp) {
            
            Log::error('Invalid session assignment data', [
                'session_id' => $sessionId,
                'pending_data' => $pendingAssignment ?? 'No data',
                'timestamp' => now()->timestamp
            ]);
            
            return response()->view('sessions.form-error', [], 403);
        }
        
        $teacherId = $pendingAssignment['teacher_id'];
        $session = ChessSession::find($sessionId);
        
        if (!$session) {
            return response()->view('sessions.assignment-error', [
                'message' => 'The requested session could not be found.'
            ], 404);
        }
        
        // Check if the session is already assigned to a teacher
        if ($session->teacher_id) {
            $assignedTeacher = User::find($session->teacher_id);
            return response()->view('sessions.already-assigned', [
                'session' => $session,
                'teacher' => $assignedTeacher
            ]);
        }
        
        // Debug information
        Log::info('Session assignment form submitted', [
            'session_id' => $sessionId,
            'teacher_id' => $teacherId,
            'has_selected_time' => $request->has('selected_time'),
            'has_no_suggested_times' => $request->has('no_suggested_times'),
            'has_meeting_link' => $request->has('meeting_link'),
            'request_data' => $request->all()
        ]);
        
        // Validate meeting link
        try {
            $request->validate([
                'meeting_link' => 'required|url|max:255'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
        
        // Process the selected time if available
        if ($request->has('selected_time') && !$request->has('no_suggested_times')) {
            $selectedTime = explode('|', $request->selected_time);
            if (count($selectedTime) === 2) {
                $date = $selectedTime[0];
                $time = $selectedTime[1];
                
                // Set the scheduled time for the session
                $scheduledAt = \Carbon\Carbon::parse($date . ' ' . $time);
                $session->scheduled_at = $scheduledAt;
                
                Log::info('Session time scheduled', [
                    'session_id' => $sessionId,
                    'teacher_id' => $teacherId,
                    'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Assign the teacher to the session
        try {
            $session->teacher_id = $teacherId;
            $session->meeting_link = $request->meeting_link;
            $session->save();
            
            // Also update the student's profile to assign this teacher
            $student = User::find($session->student_id);
            if ($student) {
                // Ensure the student has a profile, create one if it doesn't exist
                if (!$student->studentProfile) {
                    $student->studentProfile()->create([
                        'user_id' => $student->id,
                        'teacher_id' => $teacherId
                    ]);
                    
                    Log::info('Created student profile with teacher assignment', [
                        'student_id' => $student->id,
                        'teacher_id' => $teacherId
                    ]);
                } else {
                    // Only assign if the student doesn't already have a teacher assigned
                    if (!$student->studentProfile->teacher_id) {
                        $student->studentProfile->update(['teacher_id' => $teacherId]);
                        
                        Log::info('Teacher assigned to existing student profile', [
                            'student_id' => $student->id,
                            'teacher_id' => $teacherId
                        ]);
                    }
                }
                
                // Send email notification to student about teacher assignment
                $teacher = User::find($teacherId);
                try {
                    $student->notify(new \App\Notifications\SessionAssigned($session, $teacher));
                    Log::info('Session assignment notification sent to student', [
                        'student_id' => $student->id,
                        'student_email' => $student->email,
                        'session_id' => $session->id,
                        'teacher_id' => $teacher->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send session assignment notification to student', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::info('Teacher assigned to session', [
                'session_id' => $session->id,
                'teacher_id' => $teacherId,
                'scheduled_at' => $session->scheduled_at ?? 'Not scheduled'
            ]);
            
            // Get the teacher and student information for the view
            $teacher = User::find($teacherId);
            
            return response()->view('sessions.assignment-success', [
                'session' => $session,
                'teacher' => $teacher,
                'student' => $student
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error assigning teacher to session', [
                'session_id' => $session->id,
                'teacher_id' => $teacherId,
                'error' => $e->getMessage()
            ]);
            
            return response()->view('sessions.assignment-error', [
                'message' => 'An error occurred while assigning the session. Please try again later.'
            ], 500);
        }
    }
    
    /**
     * Check if student's requested availability matches teacher's availability
     */
    private function doesStudentAvailabilityMatchTeacher(ChessSession $session, User $teacher)
    {
        // Get student's suggested availability
        if (!$session->suggested_availability || !is_array($session->suggested_availability)) {
            // If no student availability specified, consider it a match (fallback to old behavior)
            return true;
        }
        
        // Load teacher's availability
        $teacher->load('availability');
        if ($teacher->availability->isEmpty()) {
            // If teacher has no availability set, consider it a match (they can work any time)
            return true;
        }
        
        // Merge overlapping student time ranges first
        $mergedStudentAvailability = $this->mergeOverlappingTimeRanges($session->suggested_availability);
        
        foreach ($mergedStudentAvailability as $studentSlot) {
            if (!isset($studentSlot['date']) || !isset($studentSlot['time_from']) || !isset($studentSlot['time_to'])) {
                continue;
            }
            
            $studentDate = \Carbon\Carbon::parse($studentSlot['date']);
            $dayOfWeek = strtolower($studentDate->format('l'));
            
            // Check if teacher has availability on this day
            $teacherDaySlots = $teacher->availability->where('day_of_week', $dayOfWeek)
                                                    ->where('is_available', true);
            
            if ($teacherDaySlots->isEmpty()) {
                continue; // Teacher not available on this day, check next student slot
            }
            
            // Check if any teacher slot overlaps with student's time range
            foreach ($teacherDaySlots as $teacherSlot) {
                if ($this->timeRangesOverlap(
                    $studentSlot['time_from'], 
                    $studentSlot['time_to'],
                    $teacherSlot->start_time,
                    $teacherSlot->end_time
                )) {
                    // Found at least one matching time slot
                    Log::info('Availability match found', [
                        'teacher_id' => $teacher->id,
                        'session_id' => $session->id,
                        'student_date' => $studentSlot['date'],
                        'student_time' => $studentSlot['time_from'] . '-' . $studentSlot['time_to'],
                        'teacher_day' => $dayOfWeek,
                        'teacher_time' => $teacherSlot->start_time . '-' . $teacherSlot->end_time
                    ]);
                    return true;
                }
            }
        }
        
        Log::info('No availability match found', [
            'teacher_id' => $teacher->id,
            'session_id' => $session->id,
            'student_slots_count' => count($mergedStudentAvailability),
            'teacher_slots_count' => $teacher->availability->count()
        ]);
        
        return false;
    }

    /**
     * Merge overlapping time ranges for the same date
     */
    private function mergeOverlappingTimeRanges($availability)
    {
        if (!is_array($availability)) {
            return $availability;
        }
        
        // Group by date
        $groupedByDate = [];
        foreach ($availability as $item) {
            if (isset($item['date']) && isset($item['time_from']) && isset($item['time_to'])) {
                $date = $item['date'];
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [];
                }
                $groupedByDate[$date][] = [
                    'time_from' => $item['time_from'],
                    'time_to' => $item['time_to'],
                    'label' => $item['label'] ?? 'Time Range'
                ];
            }
        }
        
        $mergedAvailability = [];
        
        foreach ($groupedByDate as $date => $timeRanges) {
            // Sort time ranges by start time
            usort($timeRanges, function($a, $b) {
                return strcmp($a['time_from'], $b['time_from']);
            });
            
            $merged = [];
            $current = null;
            
            foreach ($timeRanges as $range) {
                if ($current === null) {
                    $current = $range;
                } else {
                    // Check if ranges overlap or are adjacent
                    if ($this->timeRangesOverlap($current['time_from'], $current['time_to'], $range['time_from'], $range['time_to'])) {
                        // Merge ranges
                        $current['time_to'] = max($current['time_to'], $range['time_to']);
                        $current['label'] = $this->combineLables($current['label'], $range['label']);
                    } else {
                        // No overlap, add current to merged and start new
                        $merged[] = $current;
                        $current = $range;
                    }
                }
            }
            
            // Add the last range
            if ($current !== null) {
                $merged[] = $current;
            }
            
            // Add merged ranges to final result
            foreach ($merged as $mergedRange) {
                $mergedAvailability[] = [
                    'date' => $date,
                    'time_from' => $mergedRange['time_from'],
                    'time_to' => $mergedRange['time_to'],
                    'label' => $mergedRange['label']
                ];
            }
        }
        
        return $mergedAvailability;
    }
    
    /**
     * Check if two time ranges overlap or are adjacent
     */
    private function timeRangesOverlap($start1, $end1, $start2, $end2)
    {
        try {
            // Helper function to parse time in either H:i or H:i:s format
            $parseTime = function($time) {
                // First try H:i:s format, then H:i format
                try {
                    return \Carbon\Carbon::createFromFormat('H:i:s', $time);
                } catch (\Exception $e) {
                    return \Carbon\Carbon::createFromFormat('H:i', $time);
                }
            };
            
            $s1 = $parseTime($start1);
            $e1 = $parseTime($end1);
            $s2 = $parseTime($start2);
            $e2 = $parseTime($end2);
            
            // Check if ranges overlap or are adjacent (touching)
            return $s1->lte($e2) && $s2->lte($e1);
        } catch (\Exception $e) {
            Log::error('Error checking time range overlap', [
                'start1' => $start1, 'end1' => $end1,
                'start2' => $start2, 'end2' => $end2,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Combine labels from merged time ranges
     */
    private function combineLables($label1, $label2)
    {
        if ($label1 === $label2) {
            return $label1;
        }
        
        // Create a combined label
        $labels = array_unique([$label1, $label2]);
        return implode(' + ', $labels);
    }
    
    /**
     * Generate specific time slots from a time range based on session duration
     */
    private function generateTimeSlots($date, $timeFrom, $timeTo, $duration)
    {
        $slots = [];
        
        try {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $timeFrom);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $timeTo);
            
            // Generate slots with the session duration
            $currentTime = $startTime->copy();
            
            while ($currentTime->copy()->addMinutes($duration)->lte($endTime)) {
                $slots[] = $currentTime->format('H:i');
                
                // Move to next slot on base of session duration
                $currentTime->addMinutes($duration);
            }
            
        } catch (\Exception $e) {
            Log::error('Error generating time slots', [
                'date' => $date,
                'time_from' => $timeFrom,
                'time_to' => $timeTo,
                'duration' => $duration,
                'error' => $e->getMessage()
            ]);
        }
        
        return $slots;
    }
}
