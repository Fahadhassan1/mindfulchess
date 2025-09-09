<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\ChessSession;
use App\Models\TeacherAvailability;
use App\Http\Controllers\SessionAssignmentController;
use App\Notifications\AdditionalSessionBooked;
use App\Notifications\RateIncreaseNotification;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherBookingController extends Controller
{
    // Session durations and pricing (same as student booking)
    const SESSION_PRICES = [
        '60' => ['price' => 4500, 'name' => 'Online 1 Hour', 'description' => '60 minute online chess lesson'],
        '45' => ['price' => 3500, 'name' => 'Online 45 Minutes', 'description' => '45 minute online chess lesson'],
        '30' => ['price' => 2500, 'name' => 'Online 30 Minutes', 'description' => '30 minute online chess lesson']
    ];
    
    // Pricing for high-level teachers after 10 sessions (prices in cents)
    const HIGH_LEVEL_PRICES = [
        '60' => ['price' => 5000, 'name' => 'Online 1 Hour (Premium)', 'description' => '60 minute online chess lesson with high-level coach'],
        '45' => ['price' => 3875, 'name' => 'Online 45 Minutes (Premium)', 'description' => '45 minute online chess lesson with high-level coach'],
        '30' => ['price' => 2750, 'name' => 'Online 30 Minutes (Premium)', 'description' => '30 minute online chess lesson with high-level coach']
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'role:teacher']);
    }

    /**
     * Show the calendar booking page for teachers to book sessions for their students
     */
    public function showCalendar($studentId)
    {
        $teacher = auth()->user();
        $student = User::with(['studentProfile'])->find($studentId);
        
        // Verify this student is assigned to this teacher
        if (!$student || !$student->studentProfile || $student->studentProfile->teacher_id != $teacher->id) {
            return redirect()->route('teacher.students')->with('error', 'This student is not assigned to you.');
        }

        // Reload teacher with availability
        $teacher = User::with(['teacherProfile', 'availability'])->find($teacher->id);
        
        // Get teacher availability for the next 30 days
        $availability = $this->getTeacherAvailabilityForCalendar($teacher);
        
        // Check if student has a previous payment method
        $hasStoredPaymentMethod = $this->hasStoredPaymentMethod($student);
        
        // Determine if premium pricing applies
        $usesPremiumPricing = $this->shouldUsePremiumPricing($student, $teacher);
        
        // Debug information
        $sessionCount = ChessSession::where('student_id', $student->id)
                                   ->where('teacher_id', $teacher->id)
                                   ->whereIn('status', ['completed', 'booked'])
                                   ->count();
        
        Log::info('Teacher booking premium pricing check', [
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'session_count' => $sessionCount,
            'is_high_level' => $teacher->teacherProfile ? $teacher->teacherProfile->high_level_teacher : 'no_profile',
            'uses_premium_pricing' => $usesPremiumPricing
        ]);
        
        // Prepare session prices for the view
        $sessionPrices = [
            '30' => ['price' => $this->getSessionPricing($student, $teacher, '30')['price'] / 100, 'name' => '30 minutes'],
            '45' => ['price' => $this->getSessionPricing($student, $teacher, '45')['price'] / 100, 'name' => '45 minutes'],
            '60' => ['price' => $this->getSessionPricing($student, $teacher, '60')['price'] / 100, 'name' => '60 minutes']
        ];
        
        return view('teacher.booking.calendar', compact('teacher', 'student', 'availability', 'hasStoredPaymentMethod', 'sessionPrices', 'usesPremiumPricing'));
    }

    /**
     * Get teacher availability formatted for calendar display
     */
    private function getTeacherAvailabilityForCalendar($teacher)
    {
        $availability = [];
        $dayMapping = [
            'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4,
            'friday' => 5, 'saturday' => 6, 'sunday' => 0
        ];

        // Get the current day and next 30 days
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addDays(30);
        
        // Get all existing bookings for this teacher within the date range
        $existingBookings = ChessSession::where('teacher_id', $teacher->id)
            ->where('scheduled_at', '>=', $startDate)
            ->where('scheduled_at', '<=', $endDate)
            ->whereIn('status', ['pending', 'booked'])
            ->get()
            ->map(function ($session) {
                return [
                    'date' => $session->scheduled_at->format('Y-m-d'),
                    'start_time' => $session->scheduled_at->format('H:i:s'),
                    'end_time' => $session->scheduled_at->copy()->addMinutes($session->duration)->format('H:i:s'),
                ];
            });

        // Process teacher availability
        while ($startDate <= $endDate) {
            $dayOfWeek = $startDate->dayOfWeek;
            $dayName = strtolower($startDate->format('l'));
            
            // Get teacher's availability for this day
            $teacherSlots = $teacher->availability->where('day_of_week', $dayName)
                                                ->where('is_available', true);
            
            foreach ($teacherSlots as $slot) {
                $slotDate = $startDate->format('Y-m-d');
                $slotStartTime = $slot->start_time;
                $slotEndTime = $slot->end_time;
                
                // Check if this slot conflicts with any existing booking
                $isBooked = $existingBookings->contains(function ($booking) use ($slotDate, $slotStartTime, $slotEndTime) {
                    if ($booking['date'] !== $slotDate) {
                        return false;
                    }
                    
                    // Check for time overlap
                    $bookingStart = Carbon::createFromFormat('H:i:s', $booking['start_time']);
                    $bookingEnd = Carbon::createFromFormat('H:i:s', $booking['end_time']);
                    $slotStart = Carbon::createFromFormat('H:i:s', $slotStartTime);
                    $slotEnd = Carbon::createFromFormat('H:i:s', $slotEndTime);
                    
                    // Check if there's any overlap between the booking and the slot
                    return ($bookingStart < $slotEnd && $bookingEnd > $slotStart);
                });

                if (!$isBooked) {
                    $availability[] = [
                        'date' => $slotDate,
                        'day' => $startDate->format('l'),
                        'start_time' => $slotStartTime,
                        'end_time' => $slotEndTime,
                        'formatted_start' => Carbon::createFromFormat('H:i:s', $slotStartTime)->format('g:i A'),
                        'formatted_end' => Carbon::createFromFormat('H:i:s', $slotEndTime)->format('g:i A'),
                    ];
                }
            }
            
            $startDate->addDay();
        }
        
        return collect($availability)->groupBy('date');
    }

    /**
     * Check if student has a stored payment method from previous bookings
     */
    private function hasStoredPaymentMethod($student)
    {
        $defaultPayment = Payment::getDefaultForCustomer($student->email);
        if ($defaultPayment) {
            return true;
        }
        
        // Fallback to latest payment if no default is set
        $latestPayment = Payment::getLatestForCustomer($student->email);
        return $latestPayment !== null;
    }

    /**
     * Process the booking request (teacher booking for student)
     */
    public function processBooking(Request $request, $studentId)
    {
        $request->validate([
            'selected_date' => 'required|date|after_or_equal:today',
            'selected_time' => 'required',
            'duration' => 'required|in:30,45,60',
            'session_type' => 'required|in:adult,kids'
        ]);

        $teacher = auth()->user();
        $student = User::with(['studentProfile'])->find($studentId);

        // Verify this student is assigned to this teacher
        if (!$student || !$student->studentProfile || $student->studentProfile->teacher_id != $teacher->id) {
            return response()->json(['error' => 'This student is not assigned to you.'], 403);
        }

        $duration = $request->duration;
        $sessionDetails = $this->getSessionPricing($student, $teacher, $duration);
        
        // Calculate scheduled time
        $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', $request->selected_date . ' ' . $request->selected_time);

        // Check if the time slot is still available (not already booked)
        $endTime = $scheduledAt->copy()->addMinutes($duration);
        $conflictingSession = ChessSession::where('teacher_id', $teacher->id)
            ->where('scheduled_at', '>=', $scheduledAt->copy()->subMinutes($duration))
            ->where('scheduled_at', '<', $endTime)
            ->whereIn('status', ['pending', 'booked'])
            ->first();

        if ($conflictingSession) {
            return response()->json([
                'error' => 'This time slot is no longer available. Please select a different time.',
                'conflicting_session' => true
            ], 409);
        }

        try {
            // Check if student has stored payment method in profile first, then fallback to payment table
            $profilePaymentMethod = $student->studentProfile && $student->studentProfile->payment_method_id 
                ? $student->studentProfile 
                : null;
                
            $defaultPayment = null;
            if ($profilePaymentMethod) {
                // Create a payment object-like structure from profile data
                $defaultPayment = (object) [
                    'customer_id' => $profilePaymentMethod->customer_id,
                    'payment_method_id' => $profilePaymentMethod->payment_method_id
                ];
            } else {
                // Fallback to payment table lookup
                $defaultPayment = Payment::getDefaultForCustomer($student->email);
                if (!$defaultPayment) {
                    $defaultPayment = Payment::getLatestForCustomer($student->email);
                }
            }

            if ($defaultPayment && $defaultPayment->customer_id && $defaultPayment->payment_method_id) {
                // Store payment method for later charging when teacher completes session
                $session = $this->createChessSessionWithStoredPayment($defaultPayment, $student, $teacher, $duration, $request->session_type, $scheduledAt, $sessionDetails);
                
                // Update student profile with latest payment method
                $this->updateStudentPaymentMethod($student, $defaultPayment->customer_id, $defaultPayment->payment_method_id);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Session booked successfully for ' . $student->name . '! Payment will be processed when you complete the session.',
                    'session_id' => $session->id,
                    'redirect_url' => route('teacher.sessions.show', $session)
                ]);
            } else {
                // No stored payment method, redirect to payment page
                return response()->json([
                    'success' => false,
                    'needs_payment' => true,
                    'message' => 'Student needs to provide payment information.',
                    'redirect_url' => route('teacher.booking.payment', [
                        'student' => $studentId,
                        'date' => $request->selected_date,
                        'time' => $request->selected_time,
                        'duration' => $duration,
                        'session_type' => $request->session_type
                    ])
                ]);
            }

        } catch (Exception $e) {
            Log::error('Teacher booking process error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your booking.'], 500);
        }
    }

    /**
     * Show payment page for teacher booking (for students without stored payment)
     */
    public function showPayment(Request $request, $studentId)
    {
        $teacher = auth()->user();
        $student = User::with(['studentProfile'])->find($studentId);

        // Verify this student is assigned to this teacher
        if (!$student || !$student->studentProfile || $student->studentProfile->teacher_id != $teacher->id) {
            return redirect()->route('teacher.students')->with('error', 'This student is not assigned to you.');
        }

        $duration = $request->query('duration', 60);
        $sessionDetails = $this->getSessionPricing($student, $teacher, $duration);
        $price = $sessionDetails['price'] / 100; // Convert to pounds

        return view('teacher.booking.payment', [
            'date' => $request->query('date'),
            'time' => $request->query('time'),
            'duration' => $duration,
            'session_type' => $request->query('session_type'),
            'price' => $price,
            'session_name' => $sessionDetails['name'],
            'teacher' => $teacher,
            'student' => $student
        ]);
    }

    /**
     * Process payment for teacher booking
     */
    public function processPayment(Request $request, $studentId)
    {
        $request->validate([
            'payment_method' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'duration' => 'required|in:30,45,60',
            'session_type' => 'required'
        ]);

        $teacher = auth()->user();
        $student = User::with(['studentProfile'])->find($studentId);
        
        // Verify this student is assigned to this teacher
        if (!$student || !$student->studentProfile || $student->studentProfile->teacher_id != $teacher->id) {
            return back()->with('error', 'This student is not assigned to you.');
        }

        $duration = $request->duration;
        $sessionDetails = $this->getSessionPricing($student, $teacher, $duration);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Create or get customer
            $customer = $this->createOrGetStripeCustomer($student);

            // Store payment method for later charging but create session without payment
            $paymentMethod = PaymentMethod::create([
                'type' => 'card',
                'card' => [
                    'token' => $request->payment_method,
                ],
            ]);

            // Attach payment method to customer
            $paymentMethod->attach(['customer' => $customer->id]);

            // Update student profile with new payment method
            $this->updateStudentPaymentMethod($student, $customer->id, $paymentMethod->id);

            // Create session with stored payment method info (no payment yet)
            $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time);
            $session = ChessSession::create([
                'payment_id' => null, // No payment yet
                'is_paid' => false, // Session not paid yet
                'session_type' => $request->session_type,
                'duration' => $duration,
                'session_name' => $sessionDetails['name'],
                'status' => 'booked',
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'scheduled_at' => $scheduledAt,
                'suggested_availability' => null,
            ]);

            // Check if this triggers rate increase notification
            $this->checkAndSendRateIncreaseNotification($session);

            return redirect()->route('teacher.sessions.show', $session)->with('success', 'Session booked successfully for ' . $student->name . '! Payment will be processed when you complete the session.');

        } catch (Exception $e) {
            Log::error('Teacher payment processing error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while processing the payment.');
        }
    }

    /**
     * Create a chess session with stored payment method (without charging immediately)
     */
    private function createChessSessionWithStoredPayment($storedPayment, $student, $teacher, $duration, $sessionType, $scheduledAt, $sessionDetails)
    {
        $session = ChessSession::create([
            'payment_id' => null, // No payment yet
            'is_paid' => false, // Session not paid yet
            'session_type' => $sessionType,
            'duration' => $duration,
            'session_name' => $sessionDetails['name'],
            'status' => 'booked', // Session is booked, payment will be processed when teacher completes
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'scheduled_at' => $scheduledAt,
            'suggested_availability' => null,
        ]);

        Log::info('Teacher created session with deferred payment', [
            'session_id' => $session->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'scheduled_at' => $scheduledAt,
            'amount' => $sessionDetails['price'] / 100,
            'booked_by' => 'teacher'
        ]);
        
        // Send notification to student about the session booking (without payment confirmation)
        try {
            $student->notify(new \App\Notifications\AdditionalSessionBooked($session, $teacher, null));
            Log::info('Teacher booking notification sent to student', [
                'student_id' => $student->id,
                'session_id' => $session->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send teacher booking notification', [
                'error' => $e->getMessage(),
                'student_id' => $student->id
            ]);
        }

        // Check if this triggers rate increase notification
        $this->checkAndSendRateIncreaseNotification($session);

        return $session;
    }

    /**
     * Update student profile with latest payment method
     */
    private function updateStudentPaymentMethod($student, $customerId, $paymentMethodId)
    {
        if ($student->studentProfile) {
            $student->studentProfile->update([
                'customer_id' => $customerId,
                'payment_method_id' => $paymentMethodId,
                'payment_method_updated_at' => now(),
            ]);

            Log::info('Student payment method updated by teacher', [
                'student_id' => $student->id,
                'customer_id' => $customerId,
                'payment_method_id' => $paymentMethodId
            ]);
        }
    }

    /**
     * Create or get Stripe customer
     */
    private function createOrGetStripeCustomer($student)
    {
        $existingPayment = Payment::where('customer_email', $student->email)
                                 ->whereNotNull('customer_id')
                                 ->latest()
                                 ->first();

        if ($existingPayment && $existingPayment->customer_id) {
            try {
                return Customer::retrieve($existingPayment->customer_id);
            } catch (Exception $e) {
                // Customer not found, create new one
            }
        }

        return Customer::create([
            'email' => $student->email,
            'name' => $student->name,
        ]);
    }

    /**
     * Check if this triggers rate increase notification
     */
    private function checkAndSendRateIncreaseNotification(ChessSession $session)
    {
        $teacher = $session->teacher;
        $student = $session->student;

        // Check if this is a high-level teacher
        if (!$teacher->teacherProfile || $teacher->teacherProfile->is_high_level !== 1) {
            return;
        }

        // Count total sessions between this student and teacher
        $totalSessions = ChessSession::where('student_id', $student->id)
                                    ->where('teacher_id', $teacher->id)
                                    ->count();

        // If this is exactly the 10th session, send rate increase notification
        if ($totalSessions == 10) {
            try {
                // Prepare new rates for premium pricing
                $newRates = [
                    '30' => self::HIGH_LEVEL_PRICES['30']['price'] / 100,
                    '45' => self::HIGH_LEVEL_PRICES['45']['price'] / 100,
                    '60' => self::HIGH_LEVEL_PRICES['60']['price'] / 100,
                ];
                
                $student->notify(new RateIncreaseNotification($session, $teacher, $newRates, $totalSessions));
                Log::info('Rate increase notification sent', [
                    'student_id' => $student->id,
                    'teacher_id' => $teacher->id,
                    'session_count' => $totalSessions
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send rate increase notification', [
                    'error' => $e->getMessage(),
                    'student_id' => $student->id,
                    'teacher_id' => $teacher->id
                ]);
            }
        }
    }

    /**
     * Check if the student has reached the premium pricing threshold (10+ sessions) with a high-level teacher
     */
    private function shouldUsePremiumPricing($student, $teacher)
    {
        // Only apply premium pricing for high-level teachers
        if (!$teacher->teacherProfile || !$teacher->teacherProfile->high_level_teacher) {
            return false;
        }
        
        // Count completed sessions with this specific high-level teacher
        $sessionsCount = ChessSession::where('student_id', $student->id)
            ->where('teacher_id', $teacher->id)
            ->whereIn('status', ['completed', 'booked'])
            ->count();
        
        // If 10 or more sessions, use premium pricing
        return $sessionsCount >= 10;
    }
    
    /**
     * Get the appropriate pricing based on teacher level and session count
     */
    private function getSessionPricing($student, $teacher, $duration)
    {
        if ($this->shouldUsePremiumPricing($student, $teacher)) {
            return self::HIGH_LEVEL_PRICES[$duration];
        }
        
        return self::SESSION_PRICES[$duration];
    }
}
