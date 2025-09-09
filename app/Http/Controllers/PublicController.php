<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\StudentRateRejectionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;

class PublicController extends Controller
{
    /**
     * Handle rate increase rejection without authentication
     * Uses a secure token to verify the request authenticity
     */
    public function rejectRateIncrease(Request $request, $studentId, $teacherId, $token)
    {
        // Find student and teacher
        $student = User::findOrFail($studentId);
        $teacher = User::findOrFail($teacherId);
        
        // Verify the token for security
        $expectedToken = $this->generateRateRejectionToken($studentId, $teacherId);
        if (!hash_equals($expectedToken, $token)) {
            abort(403, 'Invalid or expired link. Please contact support if you need assistance.');
        }
        
        $studentProfile = $student->studentProfile;
        if (!$studentProfile) {
            return view('public.rate-rejection-result', [
                'success' => false,
                'message' => 'Student profile not found. Please contact support.',
                'student' => $student
            ]);
        }
        
        // Check if already rejected for this teacher
        if ($studentProfile->rate_rejected && $studentProfile->rate_rejected_teacher_id == $teacherId) {
            return view('public.rate-rejection-result', [
                'success' => true,
                'message' => 'You have already rejected the rate increase for ' . $teacher->name . '. An administrator will contact you soon.',
                'student' => $student,
                'teacher' => $teacher
            ]);
        }
        
        // Update the student profile with rejection
        $studentProfile->update([
            'rate_rejected' => true,
            'rate_rejected_teacher_id' => $teacherId,
            'rate_rejected_at' => now(),
            'rate_rejection_reason' => 'Student rejected rate increase via public email link'
        ]);
        
        // Get the new rates for the notification
        $newRates = \App\Http\Controllers\StudentBookingController::HIGH_LEVEL_PRICES;
        
        // Notify admin about the rejection using email from env variable
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
   
        \Illuminate\Support\Facades\Notification::route('mail', $adminEmail)
            ->notify(new StudentRateRejectionNotification(
                $student,
                $teacher,
                $newRates
            ));
        
        
        return view('public.rate-rejection-result', [
            'success' => true,
            'message' => 'Thank you for your response. You have declined the new rates for ' . $teacher->name . '. An administrator has been notified and will contact you within 2 business days to discuss teacher reassignment options.',
            'student' => $student,
            'teacher' => $teacher
        ]);
    }
    
    /**
     * Generate a secure token for rate rejection links
     */
    private function generateRateRejectionToken($studentId, $teacherId)
    {
        // Create a hash based on student ID, teacher ID, and app key for security
        $data = $studentId . '|' . $teacherId . '|' . config('app.key');
        return hash('sha256', $data);
    }
}
