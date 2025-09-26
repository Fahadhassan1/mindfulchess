<?php

namespace App\Http\Controllers;

use App\Models\ChessSession;
use App\Models\Payment;
use App\Models\User;
use App\Models\Homework;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:student|teacher|admin']);
    }

    /**
     * Show the student dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('student.dashboard');
    }

    /**
     * Show the teachers page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function teachers()
    {
        $student = auth()->user();
        $teacher = null;
        
        // Reload the student with relationships
        $student = User::with(['studentProfile.teacher.teacherProfile', 'studentProfile.teacher.availability'])
                      ->find($student->id);
        
        if ($student->studentProfile && $student->studentProfile->teacher_id) {
            $teacher = $student->studentProfile->teacher;
        }
        
        return view('student.teachers', compact('teacher'));
    }

    /**
     * Show the student profile page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile()
    {
        $user = auth()->user();
        $profile = $user->studentProfile;
        
        return view('student.profile', compact('user', 'profile'));
    }

    /**
     * Update the student's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        // Get current profile to check session type preference
        $currentProfile = \App\Models\StudentProfile::where('user_id', $user->id)->first();
        $isAdult = $currentProfile && $currentProfile->session_type_preference === 'adult';
        
        $validationRules = [
            'learning_goals' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
        
        // Add conditional validation rules based on user type
        if ($isAdult) {
            $validationRules['chess_rating'] = 'nullable|string|max:255';
            $validationRules['chess_username'] = 'nullable|string|max:255';
        } else {
            $validationRules['age'] = 'nullable|integer|min:5|max:17';
            $validationRules['level'] = 'nullable|string|max:255';
            $validationRules['parent_name'] = 'nullable|string|max:255';
            $validationRules['parent_email'] = 'nullable|email|max:255';
            $validationRules['parent_phone'] = 'nullable|string|max:20';
            $validationRules['school'] = 'nullable|string|max:255';
        }
        
        $validated = $request->validate($validationRules);
        
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '.' . $image->extension();
            $image->storeAs('profile_images', $imageName, 'public');
            $validated['profile_image'] = $imageName;
        }
        
        // Find or create student profile
        $studentProfile = \App\Models\StudentProfile::firstOrNew(['user_id' => $user->id]);
        $studentProfile->fill($validated);
        $studentProfile->save();
        
        return redirect()->route('student.profile')->with('success', 'Profile updated successfully.');
    }
    
    /**
     * Show the student's sessions.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function sessions()
    {
        $user = auth()->user();
        
        $sessions = ChessSession::where('student_id', $user->id)
            ->with(['teacher', 'payment', 'homework'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('student.sessions', compact('sessions'));
    }
    
    /**
     * Show a specific session details.
     *
     * @param  \App\Models\ChessSession  $session
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showSession(ChessSession $session)
    {
        $user = auth()->user();
        
        // Check if the session belongs to the student
        if ($session->student_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        
        // Eager load related data
        $session->load(['teacher', 'payment', 'homework.teacher']);
        
        return view('student.sessions.show', compact('session'));
    }
    
    /**
     * Show the student's payment history.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function payments()
    {
        $user = auth()->user();
        
        $payments = Payment::where('customer_email', $user->email)
            ->with('chessSession')  // Eager load the chess session relationship
            ->orderBy('paid_at', 'desc')
            ->paginate(10);
            
        return view('student.payments', compact('payments'));
    }

    /**
     * Show the invoice for a specific payment.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function invoice(Payment $payment)
    {
        $user = auth()->user();
        
        // Check if the payment belongs to the student
        if ($payment->customer_email !== $user->email) {
            abort(403, 'Unauthorized');
        }
        
        // Eager load related data
        $payment->load('chessSession');
        
        return view('student.payments.invoice', compact('payment'));
    }

    /**
     * Show the student's payment methods.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function paymentMethods()
    {
        $user = auth()->user();
        $paymentMethod = null;
        
        // Get student profile with payment method ID
        $studentProfile = StudentProfile::where('user_id', $user->id)->first();
        
        if ($studentProfile && $studentProfile->payment_method_id) {
            try {
                // Initialize Stripe
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                
                // Retrieve payment method from Stripe
                $stripePaymentMethod = \Stripe\PaymentMethod::retrieve($studentProfile->payment_method_id);
                
                // Create a payment method object with card details
                $paymentMethod = (object) [
                    'id' => $stripePaymentMethod->id,
                    'type' => $stripePaymentMethod->type,
                    'card' => $stripePaymentMethod->card ?? null,
                    'created_at' => $studentProfile->payment_method_updated_at ?? $studentProfile->updated_at,
                    'is_default' => true, // Since it's the only one stored
                ];
            } catch (\Exception $e) {
                // If payment method retrieval fails, log the error
                Log::error('Failed to retrieve payment method from Stripe: ' . $e->getMessage());
            }
        }
        
        return view('student.payment-methods', compact('paymentMethod'));
    }

    /**
     * Set a payment method as default.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setDefaultPaymentMethod(Payment $payment)
    {
        $user = auth()->user();
        
        // Check if the payment belongs to the student
        if ($payment->customer_email !== $user->email) {
            abort(403, 'Unauthorized');
        }
        
        // Set as default
        $payment->setAsDefault();
        
        return redirect()->route('student.payment-methods')
                        ->with('success', 'Payment method set as default successfully!');
    }

    /**
     * Show the student's homework assignments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function homework(Request $request)
    {
        $student = auth()->user();
        
        // Get homework with filtering
        $homeworkQuery = Homework::where('student_id', $student->id)
            ->with(['session', 'teacher'])
            ->orderBy('created_at', 'desc');
            
        // Filter by status if provided
        if ($request->has('status') && $request->status !== '') {
            $homeworkQuery->where('status', $request->status);
        }
        
        $homework = $homeworkQuery->paginate(10);
        
        // Get homework statistics
        $totalHomework = Homework::where('student_id', $student->id)->count();
        $assignedHomework = Homework::where('student_id', $student->id)->where('status', 'assigned')->count();
        $inProgressHomework = Homework::where('student_id', $student->id)->where('status', 'in_progress')->count();
        $completedHomework = Homework::where('student_id', $student->id)->where('status', 'completed')->count();
        $overdueHomework = Homework::where('student_id', $student->id)
            ->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['completed', 'submitted'])
            ->count();
        
        return view('student.homework', compact(
            'homework', 
            'totalHomework', 
            'assignedHomework', 
            'inProgressHomework', 
            'completedHomework',
            'overdueHomework'
        ));
    }

    /**
     * Show individual homework details.
     *
     * @param  \App\Models\Homework  $homework
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function showHomework(Homework $homework)
    {
        $student = auth()->user();
        
        // Check if the homework belongs to the student
        if ($homework->student_id !== $student->id) {
            return redirect()->route('student.homework')
                             ->with('error', 'You are not authorized to view this homework.');
        }
        
        $homework->load(['session', 'teacher']);
        
        return view('student.homework-details', compact('homework'));
    }

    /**
     * Update homework status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Homework  $homework
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateHomeworkStatus(Request $request, Homework $homework)
    {
        $student = auth()->user();
        
        // Check if the homework belongs to the student
        if ($homework->student_id !== $student->id) {
            return redirect()->route('student.homework')
                             ->with('error', 'You are not authorized to update this homework.');
        }
        
        $request->validate([
            'status' => 'required|in:in_progress,completed',
            'student_notes' => 'nullable|string|max:1000',
        ]);
        
        $updateData = [
            'status' => $request->status,
            'student_notes' => $request->student_notes,
        ];
        
        if ($request->status === 'completed') {
            $updateData['completed_at'] = now();
        }
        
        $homework->update($updateData);
        
        return redirect()->route('student.homework.show', $homework)
                         ->with('success', 'Homework status updated successfully!');
    }

    /**
     * Download homework attachment.
     *
     * @param  \App\Models\Homework  $homework
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadHomework(Homework $homework)
    {
        $student = auth()->user();
        
        // Check if the homework belongs to the student
        if ($homework->student_id !== $student->id) {
            return redirect()->route('student.homework')
                             ->with('error', 'You are not authorized to download this file.');
        }
        
        // Check if homework has an attachment
        if (!$homework->attachment_path) {
            return redirect()->back()
                             ->with('error', 'No attachment found for this homework.');
        }
        
        $filePath = storage_path('app/public/' . $homework->attachment_path);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            return redirect()->back()
                             ->with('error', 'File not found. Please contact your teacher.');
        }
        
        // Extract original filename from the path
        $originalName = basename($homework->attachment_path);
        
        return response()->download($filePath, $originalName);
    }

    /**
     * Submit feedback for homework.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Homework  $homework
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitHomeworkFeedback(Request $request, Homework $homework)
    {
        $student = auth()->user();
        
        // Check if the homework belongs to the student
        if ($homework->student_id !== $student->id) {
            return redirect()->route('student.homework')
                             ->with('error', 'You are not authorized to update this homework.');
        }
        
        $request->validate([
            'student_feedback' => 'required|string|max:2000',
        ]);
        
        $homework->update([
            'student_feedback' => $request->student_feedback,
            'feedback_submitted_at' => now(),
        ]);
        
        return redirect()->route('student.homework.show', $homework)
                         ->with('success', 'Your feedback has been submitted successfully! Your teacher will be able to see your notes.');
    }

    /**
     * Show the payment method update form
     */
    public function showUpdatePaymentMethod()
    {
        $student = auth()->user();
        $studentProfile = $student->studentProfile;
        
        return view('student.update-payment-method', compact('student', 'studentProfile'));
    }

    /**
     * Update the student's payment method
     */
    public function updatePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $student = auth()->user();
        
        try {
            // Set Stripe API key
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            
            // Get or create customer
            $customer = $this->getOrCreateStripeCustomer($student);
            
            // Attach the new payment method to the customer
            $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);
            $paymentMethod->attach(['customer' => $customer->id]);
            
            // Update student profile with new payment method
            $student->studentProfile()->updateOrCreate(
                ['user_id' => $student->id],
                [
                    'customer_id' => $customer->id,
                    'payment_method_id' => $request->payment_method_id,
                    'payment_method_updated_at' => now(),
                ]
            );
            
            // Create payment record for tracking
            Payment::create([
                'payment_id' => 'pm_update_' . time(),
                'customer_id' => $customer->id,
                'customer_email' => $student->email,
                'customer_name' => $student->name,
                'amount' => 0, // No charge for updating payment method
                'currency' => 'gbp',
                'status' => 'succeeded',
                'payment_method_type' => 'card',
                'payment_method_id' => $request->payment_method_id,
                'is_default' => true,
                'stripe_data' => $paymentMethod->toArray(),
                'paid_at' => now(),
            ]);
            
            \Illuminate\Support\Facades\Log::info('Payment method updated successfully', [
                'student_id' => $student->id,
                'customer_id' => $customer->id,
                'payment_method_id' => $request->payment_method_id
            ]);
            
            return redirect()->route('student.payment-methods')
                ->with('success', 'Payment method updated successfully! We\'ll retry any failed payments automatically.');
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update payment method', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update payment method: ' . $e->getMessage());
        }
    }

    /**
     * Get or create Stripe customer for the student
     */
    private function getOrCreateStripeCustomer($student)
    {
        // Check if customer already exists
        if ($student->studentProfile && $student->studentProfile->customer_id) {
            try {
                return \Stripe\Customer::retrieve($student->studentProfile->customer_id);
            } catch (\Exception $e) {
                // Customer not found, create new one
            }
        }

        // Create new customer
        return \Stripe\Customer::create([
            'email' => $student->email,
            'name' => $student->name,
        ]);
    }
}
