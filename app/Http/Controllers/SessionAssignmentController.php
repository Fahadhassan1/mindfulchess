<?php

namespace App\Http\Controllers;

use App\Models\ChessSession;
use App\Models\StudentProfile;
use App\Models\User;
use App\Notifications\SessionAssignmentRequest;
use App\Notifications\SessionAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionAssignmentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Allow the signed route without auth
        $this->middleware('auth')->except(['assignTeacher']);
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
        
        // Assign the teacher to the session
        try {
            $session->teacher_id = $teacherId;
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
                'teacher_id' => $teacherId
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
