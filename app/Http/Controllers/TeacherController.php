<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\TeacherAvailability;
use App\Models\ChessSession;
use App\Models\Homework;
use App\Models\Transfer;
use App\Models\Payment;
use App\Notifications\HomeworkAssigned;
use App\Notifications\SessionCompleted;
use App\Notifications\PaymentTransferred;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Transfer as StripeTransfer;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Exception;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    // Session pricing constants (same as StudentBookingController)
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:teacher|admin']);
    }

    /**
     * Show the teacher dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $teacher = Auth::user();
        
        // Get assigned students count
        $assignedStudents = User::role('student')
            ->whereHas('studentProfile', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->count();
            
        // Get sessions data for statistics
        $sessions = ChessSession::where('teacher_id', $teacher->id)->get();
        $totalSessions = $sessions->count();
        
        // Group sessions by student to find recurring students
        $studentSessions = $sessions->groupBy('student_id');
        $totalStudents = $studentSessions->count();
        $recurringStudents = $studentSessions->filter(function ($sessions) {
            return $sessions->count() > 1;
        })->count();
        
        // Calculate recurring student percentage
        $recurringPercentage = $totalStudents > 0 
            ? round(($recurringStudents / $totalStudents) * 100, 2) 
            : 0;
            
        // Calculate monthly session counts for the past 6 months
        $monthlySessions = [];
        $monthlyLabels = [];
        $monthlyData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M Y');
            $monthlyLabels[] = $monthName;
            
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            $count = $sessions->filter(function ($session) use ($startOfMonth, $endOfMonth) {
                return $session->created_at >= $startOfMonth && $session->created_at <= $endOfMonth;
            })->count();
            
            $monthlyData[] = $count;
            $monthlySessions[$monthName] = $count;
        }
        
        return view('teacher.dashboard', compact(
            'teacher', 
            'assignedStudents',
            'totalSessions',
            'totalStudents',
            'recurringStudents',
            'recurringPercentage',
            'monthlySessions',
            'monthlyLabels',
            'monthlyData'
        ));
    }

    /**
     * Show the teacher's students.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function students(Request $request)
    {
        $teacher = Auth::user();
        
        // Start with a query for assigned students
        $query = User::role('student')->whereHas('studentProfile', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->with('studentProfile');
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhereHas('studentProfile', function($subQuery) use ($searchTerm) {
                      $subQuery->where('school', 'like', $searchTerm)
                               ->orWhere('level', 'like', $searchTerm)
                               ->orWhere('parent_name', 'like', $searchTerm);
                  });
            });
        }
        
        // Filter by level if provided
        if ($request->has('level') && !empty($request->level)) {
            $query->whereHas('studentProfile', function($q) use ($request) {
                $q->where('level', $request->level);
            });
        }
        
        // Get paginated results
        $students = $query->paginate(10);
        
        // Get all sessions for this teacher
        $sessions = ChessSession::where('teacher_id', $teacher->id)->get();
        
        // Calculate session stats for each student
        $studentStats = [];
        foreach ($students as $student) {
            // Get all sessions for this student with this teacher
            $studentSessions = $sessions->where('student_id', $student->id);
            $sessionCount = $studentSessions->count();
            
            // Check if the student is recurring (more than one session)
            $isRecurring = $sessionCount > 1;
            
            // Add stats to the array
            $studentStats[$student->id] = [
                'session_count' => $sessionCount,
                'is_recurring' => $isRecurring,
                'first_session' => $sessionCount > 0 ? $studentSessions->sortBy('created_at')->first()->created_at->format('M d, Y') : null,
                'last_session' => $sessionCount > 0 ? $studentSessions->sortByDesc('created_at')->first()->created_at->format('M d, Y') : null,
            ];
        }
        
        // Get all possible student levels for filter dropdown
        $levels = ['beginner', 'intermediate', 'advanced'];
        
        return view('teacher.students', compact('students', 'levels', 'teacher', 'studentStats'));
    }

    /**
     * Show the teacher profile page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile()
    {
        $teacher = Auth::user();
        $profile = $teacher->teacherProfile ?? new TeacherProfile();
        
        return view('teacher.profile', compact('teacher', 'profile'));
    }

    /**
     * Update the teacher's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $teacher = Auth::user();
        
        // Validate the request data
        $request->validate([
            'teaching_type' => 'nullable|string|in:adult,kids',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle file upload for profile image
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($teacher->teacherProfile && $teacher->teacherProfile->profile_image) {
                Storage::delete('public/profile_images/' . $teacher->teacherProfile->profile_image);
            }
            
            // Store new image
            $imageName = time() . '_' . $request->file('profile_image')->getClientOriginalName();
            $request->file('profile_image')->storeAs('public/profile_images', $imageName);
        }

        // Update or create teacher profile
        $profileData = $request->only([
            'teaching_type',
            'bio'
        ]);

        if (isset($imageName)) {
            $profileData['profile_image'] = $imageName;
        }

        $teacher->teacherProfile()->updateOrCreate(
            ['user_id' => $teacher->id],
            $profileData
        );

        return redirect()->route('teacher.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Toggle the active status of the teacher.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive()
    {
        $teacher = Auth::user();
        
        if (!$teacher->teacherProfile) {
            $teacher->teacherProfile()->create(['is_active' => true]);
        }
        
        $teacher->teacherProfile->update([
            'is_active' => !$teacher->teacherProfile->is_active
        ]);
        
        $status = $teacher->teacherProfile->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('teacher.profile')
                         ->with('success', "Your profile has been {$status}. " . 
                                         ($teacher->teacherProfile->is_active 
                                           ? "You will now receive notifications." 
                                           : "You will no longer receive notifications."));
    }
    
    /**
     * Show the teacher's availability page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function availability()
    {
        $teacher = Auth::user();
        $availabilitySlots = $teacher->availability()->orderBy('day_of_week')->orderBy('start_time')->get();
        
        // Group availability by day of week
        $groupedAvailability = collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
            ->mapWithKeys(function ($day) use ($availabilitySlots) {
                return [
                    $day => $availabilitySlots->where('day_of_week', $day)->values()
                ];
            });
        
        return view('teacher.availability', compact('teacher', 'groupedAvailability'));
    }
    
    /**
     * Store teacher's availability.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAvailability(Request $request)
    {
        $teacher = Auth::user();
        
        // Validate the request data
        $request->validate([
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);
        
        // Create the availability slot
        $teacher->availability()->create([
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_available' => true,
        ]);
        
        return redirect()->route('teacher.availability')->with('success', 'Availability slot added successfully.');
    }
    
    /**
     * Delete an availability slot.
     *
     * @param  \App\Models\TeacherAvailability  $availability
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAvailability(TeacherAvailability $availability)
    {
        // Authorization check - ensure the teacher only deletes their own slots
        if ($availability->user_id !== Auth::id()) {
            return redirect()->route('teacher.availability')
                             ->with('error', 'You are not authorized to delete this availability slot.');
        }
        
        $availability->delete();
        
        return redirect()->route('teacher.availability')
                         ->with('success', 'Availability slot deleted successfully.');
    }

    /**
     * Show the teacher's booked sessions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function sessions(Request $request)
    {
        $teacher = Auth::user();
        
        // Get all booked sessions for this teacher
        $sessionsQuery = ChessSession::where('teacher_id', $teacher->id)
            ->with(['student', 'payment', 'transfer'])
            ->orderBy('scheduled_at', 'desc');
            
        // Filter by status if provided
        if ($request->filled('status')) {
            $sessionsQuery->where('status', $request->status);
        }
        
        // Filter by student if provided
        if ($request->filled('student_id')) {
            $sessionsQuery->where('student_id', $request->student_id);
        }
        
        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $sessionsQuery->whereDate('scheduled_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $sessionsQuery->whereDate('scheduled_at', '<=', $request->date_to);
        }
        
        $sessions = $sessionsQuery->paginate(10);
        
        // Get session statistics
        $totalSessions = ChessSession::where('teacher_id', $teacher->id)->count();
        $confirmedSessions = ChessSession::where('teacher_id', $teacher->id)->where('status', 'booked')->count();
        $completedSessions = ChessSession::where('teacher_id', $teacher->id)->where('status', 'completed')->count();
        $pendingSessions = ChessSession::where('teacher_id', $teacher->id)->where('status', 'pending')->count();
        
        return view('teacher.sessions', compact(
            'sessions', 
            'totalSessions', 
            'confirmedSessions', 
            'completedSessions', 
            'pendingSessions'
        ));
    }

    /**
     * Book a session.
     *
     * @param  \App\Models\ChessSession  $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmSession(ChessSession $session)
    {
        // Authorization check - ensure the teacher only books their own sessions
        if ($session->teacher_id !== Auth::id()) {
            return redirect()->route('teacher.sessions')
                             ->with('error', 'You are not authorized to book this session.');
        }

        $session->update(['status' => 'booked']);

        return redirect()->route('teacher.sessions')
                         ->with('success', 'Session booked successfully.');
    }

    /**
     * Mark a session as completed.
     *
     * @param  \App\Models\ChessSession  $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeSession(ChessSession $session)
    {
        // Authorization check - ensure the teacher only completes their own sessions
        if ($session->teacher_id !== Auth::id()) {
            return redirect()->route('teacher.sessions')
                             ->with('error', 'You are not authorized to complete this session.');
        }

        // Check if session is already completed
        if ($session->status === 'completed') {
            return redirect()->route('teacher.sessions')
                             ->with('info', 'This session is already marked as completed.');
        }

        $originalStatus = $session->status;

        try {
            DB::beginTransaction();

            // Process payment if session needs payment processing
            if ($session->status === 'booked' && !$session->is_paid) {
                // Process the deferred payment first
                $payment = $this->processSessionPayment($session);
                
                // Update session with payment info
                $session->update([
                    'payment_id' => $payment->id,
                    'is_paid' => true,
                    'status' => 'booked' // Ensure status is booked after payment
                ]);
            }

            // Update session status to completed
            $session->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            // Load necessary relationships
            $session->load(['student', 'payment']);

            // Process teacher payment transfer (this will throw exception if it fails)
            $this->processTeacherPayment($session);

            // Send feedback email to student only if transfer succeeds
            $session->student->notify(new SessionCompleted($session));

            DB::commit();

            return redirect()->route('teacher.sessions')
                             ->with('success', 'Session completed successfully! Payment has been processed and transferred to your Stripe account.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Rollback session status if transfer failed
            $session->update([
                'status' => $originalStatus,
                'completed_at' => null
            ]);

            \Illuminate\Support\Facades\Log::error('Error completing session: ' . $e->getMessage());
            return redirect()->route('teacher.sessions')
                             ->with('error', 'Error completing session: ' . $e->getMessage() . ' Session status has been reverted.');
        }
    }

    /**
     * Process deferred payment for a session
     *
     * @param  \App\Models\ChessSession  $session
     * @return \App\Models\Payment
     * @throws \Exception
     */
    private function processSessionPayment(ChessSession $session)
    {
        $student = $session->student;
        if (!$student->studentProfile || !$student->studentProfile->payment_method_id || !$student->studentProfile->customer_id) {
            throw new \Exception('Missing payment information for student: ' . $student->id);
        }

        // Calculate session amount based on student and teacher
        $teacher = $session->teacher;
        $sessionDetails = $this->getSessionPricing($student, $teacher, $session->duration);
        $sessionAmount = $sessionDetails['price'] / 100; // Convert to pounds

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Create payment intent with stored payment method from student profile
            $paymentIntent = PaymentIntent::create([
                'amount' => $sessionDetails['price'], // Amount in cents
                'currency' => 'gbp',
                'customer' => $student->studentProfile->customer_id,
                'payment_method' => $student->studentProfile->payment_method_id,
                'confirm' => true,
                'return_url' => route('student.sessions'),
                'metadata' => [
                    'session_id' => $session->id,
                    'duration' => $session->duration,
                    'session_type' => $session->session_type,
                    'booking_type' => 'additional_session_deferred'
                ]
            ]);

            if ($paymentIntent->status !== 'succeeded') {
                throw new \Exception('Payment failed with status: ' . $paymentIntent->status);
            }

            // Create payment record
            $payment = Payment::create([
                'payment_id' => $paymentIntent->id,
                'customer_id' => $student->studentProfile->customer_id,
                'customer_email' => $student->email,
                'customer_name' => $student->name,
                'amount' => $sessionAmount,
                'currency' => 'gbp',
                'status' => 'succeeded',
                'payment_method_type' => 'card',
                'payment_method_id' => $student->studentProfile->payment_method_id,
                'is_default' => false, // Don't override existing defaults
                'stripe_data' => $paymentIntent->toArray(),
                'paid_at' => now(),
            ]);

            Log::info('Deferred payment processed successfully', [
                'session_id' => $session->id,
                'payment_id' => $payment->id,
                'amount' => $sessionAmount
            ]);

            // Update student profile payment method timestamp
            $this->updateStudentPaymentMethod($session->student, $student->studentProfile->customer_id, $student->studentProfile->payment_method_id);

            return $payment;

        } catch (ApiErrorException $e) {
            Log::error('Deferred payment failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'payment_method_id' => $student->studentProfile->payment_method_id
            ]);
            
            // Send payment failure notification to student
            try {
                $student->notify(new \App\Notifications\PaymentFailedNotification($session, $e->getMessage()));
                Log::info('Payment failure notification sent to student', [
                    'session_id' => $session->id,
                    'student_id' => $student->id
                ]);
            } catch (\Exception $notificationError) {
                Log::error('Failed to send payment failure notification', [
                    'session_id' => $session->id,
                    'student_id' => $student->id,
                    'error' => $notificationError->getMessage()
                ]);
            }
            
            throw new \Exception('Payment processing failed: ' . $e->getMessage());
        }
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

            Log::info('Student payment method updated via teacher payment processing', [
                'student_id' => $student->id,
                'customer_id' => $customerId,
                'payment_method_id' => $paymentMethodId
            ]);
        }
    }

    /**
     * Process teacher payment for completed session.
     *
     * @param  \App\Models\ChessSession  $session
     * @return void
     * @throws \Exception
     */
    private function processTeacherPayment(ChessSession $session)
    {
        // Check if successful transfer already exists
        if ($session->transfer && $session->transfer->status === 'completed') {
            throw new \Exception('Transfer already completed for this session.');
        }

        // If failed transfer exists, delete it so we can retry
        if ($session->transfer && $session->transfer->status === 'failed') {
            $session->transfer->delete();
        }

        if (!$session->payment) {
            throw new \Exception('No payment found for session: ' . $session->id);
        }

        // Load teacher profile to get Stripe Connect account
        $session->load(['teacher.teacherProfile']);
        
        if (!$session->teacher->teacherProfile || !$session->teacher->teacherProfile->stripe_account_id) {
            throw new \Exception('Teacher does not have a Stripe Connect account configured.');
        }

        // Calculate payment breakdown
        $totalAmount = $session->payment->amount; // Keep in cents for Stripe
        
        // Check if this is a premium session (high-level teacher with student who has 10+ sessions)
        $isPremiumSession = $this->isPremiumSession($session);
        
        $paymentBreakdown = Transfer::calculateTeacherPayment($totalAmount / 100, $session->duration, $isPremiumSession);
        $teacherAmountCents = round($paymentBreakdown['teacher_amount'] * 100); // Convert to cents

        // Set Stripe API key
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Create Stripe transfer
            $stripeTransfer = StripeTransfer::create([
                'amount' => $teacherAmountCents,
                'currency' => 'gbp', // Assuming GBP based on your payment structure
                'destination' => $session->teacher->teacherProfile->stripe_account_id,
                'description' => 'Payment for chess session: ' . $session->session_name . ' (Session ID: ' . $session->id . ')',
                'metadata' => [
                    'session_id' => $session->id,
                    'teacher_id' => $session->teacher_id,
                    'student_id' => $session->student_id,
                    'duration' => $session->duration,
                ]
            ]);

            // Create transfer record in database
            $transfer = Transfer::create([
                'teacher_id' => $session->teacher_id,
                'session_id' => $session->id,
                'amount' => $paymentBreakdown['teacher_amount'],
                'application_fee' => $paymentBreakdown['app_fee'],
                'total_session_amount' => $totalAmount / 100,
                'stripe_transfer_id' => $stripeTransfer->id,
                'status' => 'completed',
                'transferred_at' => now(),
                'notes' => 'Stripe Connect transfer for completed session'
            ]);

            // Send payment notification to teacher
            $session->teacher->notify(new PaymentTransferred($transfer));

            // Log successful transfer
            \Illuminate\Support\Facades\Log::info('Stripe transfer completed successfully', [
                'transfer_id' => $transfer->id,
                'stripe_transfer_id' => $stripeTransfer->id,
                'teacher_id' => $session->teacher_id,
                'session_id' => $session->id,
                'amount' => $paymentBreakdown['teacher_amount'],
                'stripe_account_id' => $session->teacher->teacherProfile->stripe_account_id
            ]);

        } catch (ApiErrorException $e) {
            // Log Stripe error
            \Illuminate\Support\Facades\Log::error('Stripe transfer failed', [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
                'teacher_id' => $session->teacher_id,
                'stripe_account_id' => $session->teacher->teacherProfile->stripe_account_id,
                'amount' => $teacherAmountCents
            ]);

            // Create transfer record with failed status
            Transfer::create([
                'teacher_id' => $session->teacher_id,
                'session_id' => $session->id,
                'amount' => $paymentBreakdown['teacher_amount'],
                'application_fee' => $paymentBreakdown['app_fee'],
                'total_session_amount' => $totalAmount / 100,
                'stripe_transfer_id' => null,
                'status' => 'failed',
                'transferred_at' => null,
                'notes' => 'Stripe transfer failed: ' . $e->getMessage()
            ]);

            throw new \Exception('Failed to process Stripe transfer: ' . $e->getMessage());
        }
    }

    /**
     * Show session details.
     *
     * @param  \App\Models\ChessSession  $session
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function showSession(ChessSession $session)
    {
        // Authorization check - ensure the teacher only views their own sessions
        if ($session->teacher_id !== Auth::id()) {
            return redirect()->route('teacher.sessions')
                             ->with('error', 'You are not authorized to view this session.');
        }

        $session->load(['student', 'payment']);

        return view('teacher.session-details', compact('session'));
    }

    /**
     * Update session notes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ChessSession  $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSessionNotes(Request $request, ChessSession $session)
    {
        // Authorization check - ensure the teacher only updates their own sessions
        if ($session->teacher_id !== Auth::id()) {
            return redirect()->route('teacher.sessions')
                             ->with('error', 'You are not authorized to update this session.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $session->update([
            'notes' => $request->notes
        ]);

        return redirect()->route('teacher.sessions.show', $session)
                         ->with('success', 'Session notes updated successfully.');
    }

    /**
     * Show the homework assignment form for a session.
     *
     * @param  \App\Models\ChessSession  $session
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function showAssignHomework(ChessSession $session)
    {
        // Authorization check
        if ($session->teacher_id !== Auth::id()) {
            return redirect()->route('teacher.sessions')
                             ->with('error', 'You are not authorized to assign homework for this session.');
        }

        $session->load(['student', 'homework']);

        return view('teacher.assign-homework', compact('session'));
    }

    /**
     * Store homework assignment for a session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ChessSession  $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeHomework(Request $request, ChessSession $session)
    {
        // Authorization check
        if ($session->teacher_id !== Auth::id()) {
            return redirect()->route('teacher.sessions')
                             ->with('error', 'You are not authorized to assign homework for this session.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructions' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:10240', // 10MB max
        ]);

        $homeworkData = [
            'session_id' => $session->id,
            'teacher_id' => Auth::id(),
            'student_id' => $session->student_id,
            'title' => $request->title,
            'description' => $request->description,
            'instructions' => $request->instructions,
            'status' => 'assigned',
        ];

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('homework/attachments', $fileName, 'public');
            
            $homeworkData['attachment_path'] = $filePath;
        }

        $homework = Homework::create($homeworkData);

        // Send email notification to student
        $this->sendHomeworkNotification($homework);

        return redirect()->route('teacher.sessions.show', $session)
                         ->with('success', 'Homework assigned successfully and notification sent to student.');
    }

    /**
     * Send homework assignment notification email to student.
     *
     * @param  \App\Models\Homework  $homework
     * @return void
     */
    private function sendHomeworkNotification(Homework $homework)
    {
        try {
            // Send the homework assignment notification to the student
            $homework->student->notify(new HomeworkAssigned($homework));
            
            \Illuminate\Support\Facades\Log::info('Homework assignment notification sent successfully', [
                'student_email' => $homework->student->email,
                'homework_title' => $homework->title,
                'teacher_name' => $homework->teacher->name,
                'session_date' => $homework->session->scheduled_at ? $homework->session->scheduled_at->format('M d, Y') : 'Recent session',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send homework notification: ' . $e->getMessage());
        }
    }

    /**
     * Show teacher transfers and earnings.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function transfers()
    {
        $teacher = Auth::user();
        
        // Get all transfers for this teacher
        $transfers = Transfer::where('teacher_id', $teacher->id)
            ->with(['session.student'])
            ->orderBy('transferred_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Calculate summary statistics
        $totalEarnings = Transfer::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->sum('amount');
            
        $monthlyEarnings = Transfer::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->whereMonth('transferred_at', now()->month)
            ->whereYear('transferred_at', now()->year)
            ->sum('amount');
            
        $totalSessions = Transfer::where('teacher_id', $teacher->id)
            ->where('status', 'completed')
            ->count();
            
        $pendingAmount = Transfer::where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->sum('amount');
        
        return view('teacher.transfers', compact(
            'transfers', 
            'totalEarnings', 
            'monthlyEarnings', 
            'totalSessions', 
            'pendingAmount'
        ));
    }

    /**
     * Show Stripe Connect setup page.
     *
     * @return \Illuminate\Http\Response
     */
    public function showStripeSetup()
    {
        return view('teacher.stripe-setup');
    }

    /**
     * Update teacher's Stripe Connect account ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStripeAccount(Request $request)
    {
        $request->validate([
            'stripe_account_id' => 'required|string|starts_with:acct_'
        ]);

        $teacher = Auth::user();
        
        // Create or update teacher profile
        $teacher->teacherProfile()->updateOrCreate(
            ['user_id' => $teacher->id],
            ['stripe_account_id' => $request->stripe_account_id]
        );

        return redirect()->route('teacher.stripe.setup')
                         ->with('success', 'Stripe Connect account updated successfully! You can now receive payments.');
    }

    /**
     * Show payment invoice for a transfer.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\Response
     */
    public function showInvoice(Transfer $transfer)
    {
        // Ensure teacher can only view their own invoices
        if ($transfer->teacher_id !== Auth::id()) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $transfer->load(['session.student', 'session.payment']);
        
        return view('teacher.invoice', compact('transfer'));
    }

    /**
     * Check if a session qualifies for premium pricing
     * (High-level teacher with student who has 10+ sessions with that teacher)
     */
    private function isPremiumSession(ChessSession $session)
    {
        $teacher = $session->teacher;
        $student = $session->student;

        // Must be a high-level teacher
        if (!$teacher->teacherProfile || !$teacher->teacherProfile->high_level_teacher) {
            return false;
        }

        // Check if student has 10+ sessions with this teacher
        $sessionCount = ChessSession::where('student_id', $student->id)
            ->where('teacher_id', $teacher->id)
            ->whereIn('status', ['completed', 'booked'])
            ->count();

        return $sessionCount >= 10;
    }
}
