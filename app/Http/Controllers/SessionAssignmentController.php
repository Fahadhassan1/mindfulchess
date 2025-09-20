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
        
        // Send notifications to all eligible teachers
        foreach ($teachers as $teacher) {
            try {
                $teacher->notify(new SessionAssignmentRequest($session, $studentInfo));
                
                Log::info('Session assignment request sent to teacher', [
                    'teacher_id' => $teacher->id,
                    'teacher_email' => $teacher->email,
                    'session_id' => $session->id
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
            // Format the availability for the view
            if (isset($session->suggested_availability['preferences'])) {
                $suggestedAvailability = $session->suggested_availability['preferences'];
            } else {
                // Try to handle different formats of suggested availability
                foreach ($session->suggested_availability as $item) {
                    if (isset($item['date']) && isset($item['times'])) {
                        $suggestedAvailability[] = $item;
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
}
