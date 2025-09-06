<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\ChessSession;
use App\Models\TeacherAvailability;
use App\Http\Controllers\SessionAssignmentController;
use App\Notifications\AdditionalSessionBooked;
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
        
        return view('student.booking.calendar', compact('teacher', 'availability', 'hasStoredPaymentMethod'));
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

        // Get the next 30 days
        $startDate = Carbon::now();
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

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayName = strtolower($date->format('l'));
            $dayOfWeek = $dayMapping[$dayName] ?? null;

            if ($dayOfWeek !== null) {
                $teacherSlots = $teacher->availability->where('day_of_week', $dayName)->where('is_available', true);
                
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
                    
                    // Only add the slot if it's not already booked
                    if (!$isBooked) {
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
        $sessionDetails = self::SESSION_PRICES[$duration];
        
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
            // Check if student has stored payment method
            $defaultPayment = Payment::getDefaultForCustomer($student->email);
            if (!$defaultPayment) {
                $defaultPayment = Payment::getLatestForCustomer($student->email);
            }

            if ($defaultPayment && $defaultPayment->customer_id && $defaultPayment->payment_method_id) {
                // Try to charge the stored payment method
                $result = $this->chargeStoredPaymentMethod($defaultPayment, $sessionDetails, $student, $request);
                
                if ($result['success']) {
                    // Create the chess session
                    $session = $this->createChessSession($result['payment'], $student, $teacher, $duration, $request->session_type, $scheduledAt, null);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Session booked successfully!',
                        'session_id' => $session->id,
                        'redirect_url' => route('student.sessions.show', $session)
                    ]);
                } else {
                    // Payment failed, redirect to new payment
                    return response()->json([
                        'success' => false,
                        'payment_failed' => true,
                        'message' => 'Your stored payment method failed. Please provide a new payment method.',
                        'redirect_url' => route('student.booking.payment', [
                            'date' => $request->selected_date,
                            'time' => $request->selected_time,
                            'duration' => $duration,
                            'session_type' => $request->session_type
                        ])
                    ]);
                }
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
        $student = User::with(['studentProfile.teacher'])->find($student->id);

        if (!$student->studentProfile || !$student->studentProfile->teacher_id) {
            return redirect()->route('student.teachers')->with('error', 'No assigned teacher found.');
        }

        $duration = $request->query('duration', 60);
        $sessionDetails = self::SESSION_PRICES[$duration];
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
        $student = User::with(['studentProfile.teacher'])->find($student->id);
        $teacher = $student->studentProfile->teacher;
        $duration = $request->duration;
        $sessionDetails = self::SESSION_PRICES[$duration];

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Create or get customer
            $customer = $this->createOrGetStripeCustomer($student);

            $paymentIntent = PaymentIntent::create([
                'amount' => $sessionDetails['price'],
                'currency' => 'gbp',
                'customer' => $customer->id,
                'payment_method' => $request->payment_method,
                'confirm' => true,
                'return_url' => route('student.sessions'),
                'metadata' => [
                    'duration' => $duration,
                    'session_type' => $request->session_type,
                    'scheduled_date' => $request->date,
                    'scheduled_time' => $request->time,
                    'booking_type' => 'additional_session'
                ]
            ]);

            if ($paymentIntent->status === 'succeeded') {
                // Save payment
                $payment = Payment::create([
                    'payment_id' => $paymentIntent->id,
                    'customer_id' => $customer->id,
                    'customer_email' => $student->email,
                    'customer_name' => $student->name,
                    'amount' => $sessionDetails['price'] / 100,
                    'currency' => 'gbp',
                    'status' => 'succeeded',
                    'payment_method_type' => 'card',
                    'payment_method_id' => $request->payment_method,
                    'is_default' => true, // New payment methods become default
                    'stripe_data' => $paymentIntent->toArray(),
                    'paid_at' => now(),
                ]);

                // Set this as the new default payment method
                $payment->setAsDefault();

                Log::info('New payment method set as default', [
                    'payment_id' => $payment->id,
                    'customer_email' => $student->email,
                    'payment_method_id' => $request->payment_method
                ]);

                // Create session
                $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time);
                $session = $this->createChessSession($payment, $student, $teacher, $duration, $request->session_type, $scheduledAt, null);

                return redirect()->route('student.sessions.show', $session)->with('success', 'Session booked successfully!');
            }

            return back()->with('error', 'Payment failed. Please try again.');

        } catch (Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while processing your payment.');
        }
    }

    /**
     * Create a chess session
     */
    private function createChessSession($payment, $student, $teacher, $duration, $sessionType, $scheduledAt, $suggestedAvailability = null)
    {
        $sessionDetails = self::SESSION_PRICES[$duration];

        $session = ChessSession::create([
            'payment_id' => $payment->id,
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

        return $session;
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
}
