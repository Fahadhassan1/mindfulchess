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

class StudentBookingController extends Controller
{
    // Session durations and pricing
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
        $this->middleware(['auth', 'role:student']);
    }

    /**
     * Show the calendar booking page for students with assigned teachers
     */
    public function showCalendar()
    {
        $student = auth()->user();
        
        // Reload the student with relationships
        $student = User::with(['studentProfile.teacher.teacherProfile', 'studentProfile.teacher.availability'])
                      ->find($student->id);
        
        if (!$student->studentProfile || !$student->studentProfile->teacher_id) {
            return redirect()->route('student.teachers')->with('error', 'You need to have an assigned teacher before booking additional sessions.');
        }

        $teacher = $student->studentProfile->teacher;
        
        // Get teacher availability for the next 30 days
        $availability = $this->getTeacherAvailabilityForCalendar($teacher);
        
        // Check if student has a previous payment method
        $hasStoredPaymentMethod = $this->hasStoredPaymentMethod($student);
        
        // Determine if premium pricing applies
        $usesPremiumPricing = $this->shouldUsePremiumPricing($student, $teacher);
        
        // Prepare session prices for the view
        $sessionPrices = [
            '30' => ['price' => $this->getSessionPricing($student, $teacher, '30')['price'] / 100, 'name' => '30 minutes'],
            '45' => ['price' => $this->getSessionPricing($student, $teacher, '45')['price'] / 100, 'name' => '45 minutes'],
            '60' => ['price' => $this->getSessionPricing($student, $teacher, '60')['price'] / 100, 'name' => '60 minutes']
        ];
        
        return view('student.booking.calendar', compact('teacher', 'availability', 'hasStoredPaymentMethod', 'sessionPrices', 'usesPremiumPricing'));
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
        
        // Log raw availability data from database
        Log::debug('Raw teacher availability from DB: ' . json_encode($teacher->availability->where('is_available', true)->toArray()));

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

        // Special handling: Always add today with at least one slot to ensure it's never disabled
        $today = Carbon::now()->startOfDay();
        $todayName = strtolower($today->format('l'));
        $todayAvailability = $teacher->availability->where('day_of_week', $todayName)->where('is_available', true)->first();
        
        if ($todayAvailability) {
            $now = Carbon::now();
            $todayDateString = $today->format('Y-m-d');
            $slotStartTime = $todayAvailability->start_time;
            $slotEndTime = $todayAvailability->end_time;
            
            // Check if the slot is completely in the past
            $slotDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $todayDateString . ' ' . $slotStartTime);
            $slotEndDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $todayDateString . ' ' . $slotEndTime);
            
            // If the slot end time is in the future, add it (we allow partial slots)
            if ($slotEndDateTime->gt($now)) {
                Log::debug("Explicitly adding TODAY slot for {$todayDateString}");
                
                // Adjust start time if it's in the past
                if ($slotDateTime->lt($now)) {
                    // Round to the next 15 minutes
                    $minutes = ceil($now->minute / 15) * 15;
                    $adjustedStartTime = $now->copy()->setTime($now->hour, $minutes, 0)->format('H:i:s');
                    $slotStartTime = $adjustedStartTime;
                }
                
                $availability[] = [
                    'date' => $todayDateString,
                    'day' => $today->format('l'),
                    'start_time' => $slotStartTime,
                    'end_time' => $slotEndTime,
                    'formatted_start' => Carbon::createFromFormat('H:i:s', $slotStartTime)->format('g:i A'),
                    'formatted_end' => Carbon::createFromFormat('H:i:s', $slotEndTime)->format('g:i A'),
                ];
            }
        }
        
        // Special handling: Add upcoming Saturdays explicitly
        $saturdayAvailability = $teacher->availability->where('day_of_week', 'saturday')->where('is_available', true)->first();
        if ($saturdayAvailability) {
            // Find the next 4 Saturdays and add them manually
            $nextSaturday = Carbon::now()->startOfDay();
            while ($nextSaturday->dayOfWeek != 6) { // 6 is Saturday in Carbon
                $nextSaturday->addDay();
            }
            
            // Add Saturday slots for the next 4 weeks
            for ($i = 0; $i < 4; $i++) {
                $slotDate = $nextSaturday->copy()->addDays($i * 7)->format('Y-m-d');
                Log::debug("Explicitly adding Saturday slot for {$slotDate}");
                
                $availability[] = [
                    'date' => $slotDate,
                    'day' => 'Saturday',
                    'start_time' => $saturdayAvailability->start_time,
                    'end_time' => $saturdayAvailability->end_time,
                    'formatted_start' => Carbon::createFromFormat('H:i:s', $saturdayAvailability->start_time)->format('g:i A'),
                    'formatted_end' => Carbon::createFromFormat('H:i:s', $saturdayAvailability->end_time)->format('g:i A'),
                ];
            }
        }
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayName = strtolower($date->format('l'));
            
            // Skip Saturdays as we've added them manually above
            if ($dayName == 'saturday') continue;
            
            // Process all other days
            $teacherSlots = $teacher->availability->where('day_of_week', $dayName)->where('is_available', true);
            
            // Debug log to check availability
            Log::debug("Checking availability for {$dayName} - Found {$teacherSlots->count()} slots");
            
            foreach ($teacherSlots as $slot) {
                $slotDate = $date->format('Y-m-d');
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
                
                // Check if the slot is in the past (for today's date)
                $isInPast = false;
                if ($date->isToday()) {
                    $now = Carbon::now();
                    $slotDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $slotDate . ' ' . $slotStartTime);
                    $isInPast = $now->gt($slotDateTime);
                }
                
                // Debug info for Saturday slots
                if ($dayName == 'saturday') {
                    Log::debug("Saturday slot check - Date: $slotDate, Time: $slotStartTime-$slotEndTime, IsBooked: " . ($isBooked ? 'Yes' : 'No') . ", IsInPast: " . ($isInPast ? 'Yes' : 'No'));
                }
                
                // Only add the slot if it's not already booked and not in the past
                if (!$isBooked && !$isInPast) {
                    $availability[] = [
                        'date' => $slotDate,
                        'day' => $date->format('l'),
                        'start_time' => $slotStartTime,
                        'end_time' => $slotEndTime,
                        'formatted_start' => Carbon::createFromFormat('H:i:s', $slotStartTime)->format('g:i A'),
                        'formatted_end' => Carbon::createFromFormat('H:i:s', $slotEndTime)->format('g:i A'),
                    ];
                }
            }
        }
        
        // Log the final processed availability that will be displayed in the calendar
        $groupedAvailability = collect($availability)->groupBy('date');
        Log::debug('Final processed availability: ' . json_encode($groupedAvailability->all()));
        
        return $groupedAvailability;
        
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
     * Process the booking request
     */
    public function processBooking(Request $request)
    {
        $request->validate([
            'selected_date' => 'required|date|after_or_equal:today',
            'selected_time' => 'required',
            'duration' => 'required|in:30,45,60',
            'session_type' => 'required|in:adult,kids'
        ]);

        $student = auth()->user();
        $student = User::with(['studentProfile.teacher'])->find($student->id);

        if (!$student->studentProfile || !$student->studentProfile->teacher_id) {
            return response()->json(['error' => 'No assigned teacher found.'], 400);
        }

        $teacher = $student->studentProfile->teacher;
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
                    'message' => 'Session booked successfully! Payment will be processed when your teacher completes the session.',
                    'session_id' => $session->id,
                    'redirect_url' => route('student.sessions.show', $session)
                ]);
            } else {
                // No stored payment method, redirect to payment page
                return response()->json([
                    'success' => false,
                    'needs_payment' => true,
                    'message' => 'Please provide payment information.',
                    'redirect_url' => route('student.booking.payment', [
                        'date' => $request->selected_date,
                        'time' => $request->selected_time,
                        'duration' => $duration,
                        'session_type' => $request->session_type
                    ])
                ]);
            }

        } catch (Exception $e) {
            Log::error('Booking process error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your booking.'], 500);
        }
    }

    /**
     * Charge the stored payment method
     */
    private function chargeStoredPaymentMethod($storedPayment, $sessionDetails, $student, $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => $sessionDetails['price'],
                'currency' => 'gbp',
                'customer' => $storedPayment->customer_id,
                'payment_method' => $storedPayment->payment_method_id,
                'confirm' => true,
                'return_url' => route('student.sessions'),
                'metadata' => [
                    'duration' => $request->duration,
                    'session_type' => $request->session_type,
                    'scheduled_date' => $request->selected_date,
                    'scheduled_time' => $request->selected_time,
                    'booking_type' => 'additional_session'
                ]
            ]);

            if ($paymentIntent->status === 'succeeded') {
                // Save payment information
                $payment = Payment::create([
                    'payment_id' => $paymentIntent->id,
                    'customer_id' => $storedPayment->customer_id,
                    'customer_email' => $student->email,
                    'customer_name' => $student->name,
                    'amount' => $sessionDetails['price'] / 100,
                    'currency' => 'gbp',
                    'status' => 'succeeded',
                    'payment_method_type' => 'card',
                    'payment_method_id' => $storedPayment->payment_method_id,
                    'is_default' => true, // Keep as default since we used the stored method
                    'stripe_data' => $paymentIntent->toArray(),
                    'paid_at' => now(),
                ]);

                // Set this as the new default payment (updates existing defaults)
                $payment->setAsDefault();

                return ['success' => true, 'payment' => $payment];
            }

            return ['success' => false, 'error' => 'Payment failed'];

        } catch (Exception $e) {
            Log::error('Stored payment method charge failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Show payment page for new payment method
     */
    public function showPayment(Request $request)
    {
        $student = auth()->user();
        $student = User::with(['studentProfile.teacher.teacherProfile'])->find($student->id);

        if (!$student->studentProfile || !$student->studentProfile->teacher_id) {
            return redirect()->route('student.teachers')->with('error', 'No assigned teacher found.');
        }

        $duration = $request->query('duration', 60);
        $teacher = $student->studentProfile->teacher;
        $sessionDetails = $this->getSessionPricing($student, $teacher, $duration);
        $price = $sessionDetails['price'] / 100; // Convert to pounds

        return view('student.booking.payment', [
            'date' => $request->query('date'),
            'time' => $request->query('time'),
            'duration' => $duration,
            'session_type' => $request->query('session_type'),
            'price' => $price,
            'session_name' => $sessionDetails['name'],
            'teacher' => $student->studentProfile->teacher,
            'student' => $student
        ]);
    }

    /**
     * Process new payment for booking
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'duration' => 'required|in:30,45,60',
            'session_type' => 'required'
        ]);

        $student = auth()->user();
        $student = User::with(['studentProfile.teacher.teacherProfile'])->find($student->id);
        $teacher = $student->studentProfile->teacher;
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

            return redirect()->route('student.sessions.show', $session)->with('success', 'Session booked successfully! Payment will be processed when your teacher completes the session.');

        } catch (Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while processing your payment.');
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
    
    /**
     * Create a chess session
     */
    private function createChessSession($payment, $student, $teacher, $duration, $sessionType, $scheduledAt, $suggestedAvailability = null)
    {
        $sessionDetails = $this->getSessionPricing($student, $teacher, $duration);

        $session = ChessSession::create([
            'payment_id' => $payment->id,
            'is_paid' => true, // Session is already paid
            'session_type' => $sessionType,
            'duration' => $duration,
            'session_name' => $sessionDetails['name'],
            'status' => 'booked',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'scheduled_at' => $scheduledAt,
            'suggested_availability' => $suggestedAvailability,
        ]);

        Log::info('Additional session created', [
            'session_id' => $session->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'scheduled_at' => $scheduledAt
        ]);
        
        // Send notification to student about the additional session booking
        try {
            $student->notify(new \App\Notifications\AdditionalSessionBooked($session, $teacher, $payment));
            Log::info('Additional session booking notification sent', [
                'student_id' => $student->id,
                'session_id' => $session->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send additional session booking notification', [
                'error' => $e->getMessage(),
                'student_id' => $student->id
            ]);
        }

        // Check if this triggers rate increase notification
        $this->checkAndSendRateIncreaseNotification($session);

        return $session;
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

        Log::info('Session created with deferred payment', [
            'session_id' => $session->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'scheduled_at' => $scheduledAt,
            'amount' => $sessionDetails['price'] / 100
        ]);
        
        // Send notification to student about the session booking (without payment confirmation)
        try {
            $student->notify(new \App\Notifications\AdditionalSessionBooked($session, $teacher, null));
            Log::info('Session booking notification sent', [
                'student_id' => $student->id,
                'session_id' => $session->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send session booking notification', [
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

            Log::info('Student payment method updated', [
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
     * Check if student has reached 10 sessions with a high-level teacher and send rate increase notification
     *
     * @param  \App\Models\ChessSession  $session
     * @return void
     */
    private function checkAndSendRateIncreaseNotification(ChessSession $session)
    {
        $teacher = $session->teacher;
        $student = $session->student;

        // Only proceed if this is a high-level teacher
        if (!$teacher->teacherProfile || !$teacher->teacherProfile->high_level_teacher) {
            Log::info('No rate increase check - teacher is not high-level', [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id
            ]);
            return;
        }

        // Count total sessions between this student and teacher (including the new one just created)
        $totalSessions = ChessSession::where('student_id', $student->id)
            ->where('teacher_id', $teacher->id)
            ->whereIn('status', ['completed', 'booked','cancelled']) // Include cancelled to avoid loopholes
            ->count();
 

        // Check if this is exactly the 10th session and we haven't notified yet
        if ($totalSessions === 10) {
            $studentProfile = $student->studentProfile;
            
            if ($studentProfile && !$studentProfile->rate_increase_notified) {
                // Get new rates (high-level rates)
                $newRates = self::HIGH_LEVEL_PRICES;

                // Mark as notified in student profile
                $studentProfile->update([
                    'rate_increase_notified' => true,
                    'rate_increase_notified_at' => now()
                ]);

                // Send rate increase notification to student
                $student->notify(new RateIncreaseNotification(
                    $session,
                    $teacher,
                    $newRates,
                    $totalSessions
                ));

                Log::info('Rate increase notification sent at booking', [
                    'student_id' => $student->id,
                    'teacher_id' => $teacher->id,
                    'session_id' => $session->id,
                    'total_sessions' => $totalSessions
                ]);
            } else {
                Log::info('Rate increase notification not sent - already notified or no profile', [
                    'student_id' => $student->id,
                    'teacher_id' => $teacher->id,
                    'has_profile' => !is_null($studentProfile),
                    'already_notified' => $studentProfile ? $studentProfile->rate_increase_notified : null
                ]);
            }
        }
    }
}
