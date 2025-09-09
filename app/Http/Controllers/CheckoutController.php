<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\ChessSession;
use App\Models\Coupon;
use App\Http\Controllers\SessionAssignmentController;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // Session durations and pricing
    const SESSION_PRICES = [
        '60' => ['price' => 4500, 'name' => 'Online 1 Hour', 'description' => '60 minute online chess lesson'],
        '45' => ['price' => 3500, 'name' => 'Online 45 Minutes', 'description' => '45 minute online chess lesson'],
        '30' => ['price' => 2500, 'name' => 'Online 30 Minutes', 'description' => '30 minute online chess lesson']
    ];
    
    // Session types
    const SESSION_TYPES = [
        'adult' => 'Adult',
        'kids' => 'Kids',
    ];
    
    /**
     * Display the checkout page with session details
     */
    public function index(Request $request)
    {
        $duration = $request->query('duration', 60);
        $sessionType = $request->query('session_type', 'kids');
        
        // Validate that the duration is one of the allowed values
        if (!array_key_exists($duration, self::SESSION_PRICES)) {
            $duration = 60; // Default to 60 minutes if invalid duration
        }
        
        // Validate that the session type is one of the allowed values
        if (!array_key_exists($sessionType, self::SESSION_TYPES)) {
            $sessionType = 'kids'; // Default to kids session if invalid type
        }
        
        $sessionDetails = self::SESSION_PRICES[$duration];
        
        return view('checkout', [
            'duration' => $duration,
            'sessionType' => $sessionType,
            'sessionTypes' => self::SESSION_TYPES,
            'price' => $sessionDetails['price'] / 100, // Convert from cents to dollars
            'name' => $sessionDetails['name'],
            'description' => $sessionDetails['description'],
            'stripe_key' => config('services.stripe.key')
        ]);
    }
    
    /**
     * Process the payment directly
     */
    public function processPayment(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'card_holder' => 'required|string',
            'duration' => 'required|in:30,45,60',
            'session_type' => 'required|string',
            'preferred_times' => 'required|json',
        ]);
        
         // Check if the user is already registered
        if ($request->input('email')) {
            // Check if a user with this email already exists
            $existingUser = User::where('email', $request->input('email'))->first();

            if ($existingUser) {
                // Redirect to login page with message
                return back()->with('error', 'You already have an account with us. Please login to book a session with your preferred teacher.');
            }
        }

        $duration = $request->input('duration');
        $sessionType = $request->input('session_type');
        
        // Validate session type
        if (!array_key_exists($sessionType, self::SESSION_TYPES)) {
            $sessionType = 'kids'; // Default to kids if invalid
        }
        
        // Validate that the duration is one of the allowed values
        if (!array_key_exists($duration, self::SESSION_PRICES)) {
            return back()->with('error', 'Invalid session duration selected.');
        }
        
        $sessionDetails = self::SESSION_PRICES[$duration];
        
        // Check if coupon code is applied
        $appliedCoupon = $request->input('applied_coupon');
        $discountAmount = 0;
        
        if ($appliedCoupon) {
            $coupon = \App\Models\Coupon::where('code', $appliedCoupon)->first();
            
            if ($coupon && $coupon->isValid()) {
                // Calculate discount
                $discountAmount = $sessionDetails['price'] * ($coupon->discount_percentage / 100);
                
                // Update the session price after discount
                $sessionDetails['price'] = $sessionDetails['price'] - $discountAmount;
                
                // Update coupon usage
                $coupon->incrementUsage();
            }
        }
        
        try {
            // Set your Stripe secret key
            Stripe::setApiKey(config('services.stripe.secret'));
            
            // Get or create a customer
            $customers = Customer::all([
                'email' => $request->email,
                'limit' => 1
            ]);
            
            if (count($customers->data) > 0) {
                $customer = $customers->data[0];
            } else {
                $customer = Customer::create([
                    'email' => $request->email,
                    'name' => $request->card_holder,
                ]);
            }
            
            // Attach the payment method to the customer before using it
            try {
                $paymentMethod = PaymentMethod::retrieve($request->payment_method);
                $paymentMethod->attach(['customer' => $customer->id]);
                
                // Set as the customer's default payment method
                Customer::update($customer->id, [
                    'invoice_settings' => ['default_payment_method' => $request->payment_method]
                ]);
            } catch (\Exception $e) {
                Log::error('Error attaching payment method to customer: ' . $e->getMessage());
                // Continue with payment processing even if setting default fails
            }
            
            // Prepare metadata
            $metadata = [
                'duration' => $duration,
                'session_name' => $sessionDetails['name'],
                'session_type' => $sessionType,
                'session_type_name' => self::SESSION_TYPES[$sessionType],
            ];
            
            // Add coupon information to metadata if applied
            if ($appliedCoupon && isset($coupon) && $coupon) {
                $metadata['coupon_code'] = $appliedCoupon;
                $metadata['discount_amount'] = $discountAmount;
                $metadata['discount_percentage'] = $coupon->discount_percentage;
            }
            
            // Create a payment intent for one-time payment
            $paymentIntent = PaymentIntent::create([
                'amount' => $sessionDetails['price'],
                'currency' => 'gbp',
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
                'description' => $sessionDetails['description'],
                'metadata' => $metadata,
                'receipt_email' => $request->email,
            ]);
            
            // Attach the payment method from the form
            $paymentIntent->confirm([
                'payment_method' => $request->payment_method,
                'return_url' => route('checkout.success'),
            ]);
            
            // You would typically save the payment and customer details to your database here
            
            // Handle the payment result based on the payment intent status
            if ($paymentIntent->status === 'succeeded') {
                // Save the payment and session details to our database
                $suggestedAvailability = json_decode($request->preferred_times, true);
                $this->savePaymentInformation($paymentIntent, $customer, $sessionType, $sessionDetails, $appliedCoupon, $coupon ?? null, $discountAmount ?? 0, $suggestedAvailability);
                
                $viewData = [
                    'paymentId' => $paymentIntent->id,
                    'customerEmail' => $customer->email,
                    'customerName' => $customer->name,
                    'planName' => $sessionDetails['name'],
                    'price' => $sessionDetails['price'] / 100,
                    'sessionType' => $sessionType,
                    'sessionTypeName' => self::SESSION_TYPES[$sessionType]
                ]; // Add coupon information if applied
                if ($appliedCoupon && isset($coupon) && $coupon) {
                    $viewData['couponCode'] = $appliedCoupon;
                    $viewData['discountPercentage'] = $coupon->discount_percentage;
                    $viewData['originalPrice'] = ($sessionDetails['price'] + $discountAmount) / 100;
                }
                
                return view('checkout-success', $viewData);
            } elseif ($paymentIntent->status === 'requires_action') {
                // Store the preferred times in session before redirecting for 3D Secure authentication
                session(['preferred_times' => $request->preferred_times]);
                
                // Redirect for 3D Secure authentication if needed
                return redirect($paymentIntent->next_action->redirect_to_url->url);
            } else {
                // Payment failed or requires additional action
                return back()->with('error', 'Payment could not be processed. Please try again.');
            }
            
        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Checkout Error: ' . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }
    
    /**
     * Handle successful checkout
     */
    public function success(Request $request)
    {
        // This handles the redirect back from Stripe for 3D Secure authentication
        $paymentIntentId = $request->query('payment_intent');
        
        if ($paymentIntentId) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
                
                if ($paymentIntent->status === 'succeeded') {
                    // Get the customer information from the payment intent
                    $customer = Customer::retrieve($paymentIntent->customer);
                    
                    // If payment method exists, ensure it's attached to the customer and set as default
                    if ($paymentIntent->payment_method) {
                        try {
                            $paymentMethod = PaymentMethod::retrieve($paymentIntent->payment_method);
                            
                            // Check if the payment method is already attached to this customer
                            $isAttached = false;
                            try {
                                $existingPaymentMethods = PaymentMethod::all([
                                    'customer' => $customer->id,
                                    'type' => 'card'
                                ]);
                                foreach ($existingPaymentMethods->data as $method) {
                                    if ($method->id === $paymentIntent->payment_method) {
                                        $isAttached = true;
                                        break;
                                    }
                                }
                            } catch (\Exception $e) {
                                // Ignore errors in checking existing methods
                            }
                            
                            // Attach only if not already attached
                            if (!$isAttached) {
                                $paymentMethod->attach(['customer' => $customer->id]);
                            }
                            
                            // Set as default payment method
                            Customer::update($customer->id, [
                                'invoice_settings' => ['default_payment_method' => $paymentIntent->payment_method]
                            ]);
                            
                            Log::info('Payment method set as default for customer', [
                                'customer_id' => $customer->id,
                                'payment_method_id' => $paymentIntent->payment_method
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Error setting payment method as default: ' . $e->getMessage());
                            // Continue processing even if setting default fails
                        }
                    }
                    
                    // Check if we already have this payment recorded (to avoid duplicates)
                    $existingPayment = Payment::where('payment_id', $paymentIntent->id)->first();
                    
                    if (!$existingPayment) {
                        // Determine session details
                        $duration = $paymentIntent->metadata['duration'] ?? 60;
                        $sessionDetails = self::SESSION_PRICES[$duration];
                        $sessionType = $paymentIntent->metadata['session_type'] ?? 'kids';
                        
                        // Get coupon information if available
                        $couponCode = $paymentIntent->metadata['coupon_code'] ?? null;
                        $discountAmount = $paymentIntent->metadata['discount_amount'] ?? 0;
                        $coupon = null;
                        
                        if ($couponCode) {
                            $coupon = Coupon::where('code', $couponCode)->first();
                        }
                        
                        // Save payment information (this also handles creating student account and sending email)
                        // If we have saved preferred times in session, retrieve them
                        $suggestedAvailability = null;
                        if (session()->has('preferred_times')) {
                            $suggestedAvailability = json_decode(session('preferred_times'), true);
                        }
                        
                        $this->savePaymentInformation($paymentIntent, $customer, $sessionType, $sessionDetails, $couponCode, $coupon, $discountAmount, $suggestedAvailability);
                    }
                    
                    $viewData = [
                        'paymentId' => $paymentIntent->id,
                        'customerEmail' => $customer->email,
                        'customerName' => $customer->name,
                        'planName' => $paymentIntent->description,
                        'price' => $paymentIntent->amount / 100
                    ];
                    
                    // Add session type info if available in metadata
                    if (isset($paymentIntent->metadata['session_type'])) {
                        $viewData['sessionType'] = $paymentIntent->metadata['session_type'];
                        $viewData['sessionTypeName'] = $paymentIntent->metadata['session_type_name'];
                    }
                    
                    // Add coupon information if it was applied
                    if (isset($paymentIntent->metadata['coupon_code'])) {
                        $viewData['couponCode'] = $paymentIntent->metadata['coupon_code'];
                        $viewData['discountPercentage'] = $paymentIntent->metadata['discount_percentage'];
                        
                        // Calculate original price
                        $discountAmount = $paymentIntent->metadata['discount_amount'];
                        $originalPrice = ($paymentIntent->amount + $discountAmount) / 100;
                        $viewData['originalPrice'] = $originalPrice;
                    }
                    
                    return view('checkout-success', $viewData);
                }
            } catch (\Exception $e) {
                Log::error('Payment verification error: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('checkout')->with('error', 'We could not verify your payment. Please try again.');
    }
    
    /**
     * Create a student account if it doesn't exist and send confirmation email
     * 
     * @param string $email
     * @param string $name
     * @param array $paymentData
     * @param array $sessionData
     * @param string|null $customerId
     * @param string|null $paymentMethodId
     * @return \App\Models\User|null
     */
    protected function createStudentAccountAndSendEmail($email, $name, $paymentData, $sessionData, $customerId = null, $paymentMethodId = null)
    {
        // Check if a user with this email already exists
        $user = User::where('email', $email)->first();
        $accountData = null;
        
        // If no user exists, create one
        if (!$user) {
            // Generate a default password
            $password = 'Password2025';
            
            try {
                // Create the user with student role
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt($password)
                ]);
                
                // Assign student role
                $user->assignRole('student');
                
                // Create student profile if needed
                if (!$user->studentProfile) {
                    $user->studentProfile()->create();
                    // Refresh the user to load the newly created student profile
                    $user->refresh();
                }
                
                // Store payment method information in student profile
                if ($customerId && $paymentMethodId && $user->studentProfile) {
                    $user->studentProfile->update([
                        'customer_id' => $customerId,
                        'payment_method_id' => $paymentMethodId,
                        'payment_method_updated_at' => now(),
                    ]);
                    
                    Log::info('Payment method stored in student profile during account creation', [
                        'student_id' => $user->id,
                        'customer_id' => $customerId,
                        'payment_method_id' => $paymentMethodId
                    ]);
                } else {
                    Log::warning('Payment method not stored in student profile during account creation', [
                        'student_id' => $user->id,
                        'has_customer_id' => !empty($customerId),
                        'has_payment_method_id' => !empty($paymentMethodId),
                        'has_student_profile' => !empty($user->studentProfile),
                        'customer_id' => $customerId,
                        'payment_method_id' => $paymentMethodId
                    ]);
                }
                
                // Store account data for email
                $accountData = [
                    'email' => $email,
                    'password' => $password
                ];
                
                Log::info('New student account created during checkout', ['email' => $email]);
                
            } catch (\Exception $e) {
                Log::error('Error creating student account: ' . $e->getMessage());
                // Continue even if account creation fails
            }
        }
        
        // Send confirmation email whether we created an account or not
        try {
            $user = $user ?? (new User(['email' => $email, 'name' => $name]));
            $user->notify(new \App\Notifications\PaymentConfirmation($paymentData, $sessionData, $accountData));
            Log::info('Payment confirmation email sent', ['email' => $email]);
        } catch (\Exception $e) {
            Log::error('Error sending payment confirmation: ' . $e->getMessage());
        }
        
        return $user;
    }
    
    /**
     * Save payment and session information to the database
     * 
     * @param \Stripe\PaymentIntent $paymentIntent
     * @param \Stripe\Customer $customer
     * @param string $sessionType
     * @param array $sessionDetails
     * @param string|null $couponCode
     * @param \App\Models\Coupon|null $coupon
     * @param float $discountAmount
     * @return \App\Models\Payment|null
     */
    protected function savePaymentInformation($paymentIntent, $customer, $sessionType, $sessionDetails, $couponCode = null, $coupon = null, $discountAmount = 0, $suggestedAvailability = null)
    {
        try {
            // Use a database transaction to ensure both payment and session are saved together
            return DB::transaction(function () use ($paymentIntent, $customer, $sessionType, $sessionDetails, $couponCode, $coupon, $discountAmount, $suggestedAvailability) {
                // Check if this is the customer's first payment
                $isFirstPayment = Payment::where('customer_email', $customer->email)
                                         ->where('status', 'succeeded')
                                         ->exists();
                
                // Extract payment method ID from Stripe data
                $paymentMethodId = null;
                if (isset($paymentIntent->payment_method)) {
                    $paymentMethodId = $paymentIntent->payment_method;
                }
                
                // Create the payment record
                $payment = Payment::create([
                    'payment_id' => $paymentIntent->id,
                    'customer_id' => $customer->id,
                    'customer_email' => $customer->email,
                    'customer_name' => $customer->name,
                    'amount' => $paymentIntent->amount / 100,
                    'original_amount' => ($discountAmount > 0) ? ($paymentIntent->amount + $discountAmount) / 100 : null,
                    'currency' => strtoupper($paymentIntent->currency),
                    'status' => $paymentIntent->status,
                    'payment_method_type' => $paymentIntent->payment_method_types[0] ?? null,
                    'payment_method_id' => $paymentMethodId,
                    'is_default' => $isFirstPayment, // Set as default if first payment
                    'coupon_code' => $couponCode,
                    'discount_percentage' => $coupon ? $coupon->discount_percentage : null,
                    'stripe_data' => $paymentIntent->toArray(),
                    'paid_at' => now(),
                ]);
                
                // Set payment method as default in both our database and Stripe
                if ($paymentMethodId) {
                    $payment->setAsDefault();
                    
                    // Make sure the payment method is attached to the customer in Stripe
                    try {
                        // Check if the payment method is already attached
                        $isAttached = false;
                        $existingPaymentMethods = PaymentMethod::all([
                            'customer' => $customer->id,
                            'type' => 'card'
                        ]);
                        
                        foreach ($existingPaymentMethods->data as $method) {
                            if ($method->id === $paymentMethodId) {
                                $isAttached = true;
                                break;
                            }
                        }
                        
                        // If not attached, attach it
                        if (!$isAttached) {
                            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
                            $paymentMethod->attach(['customer' => $customer->id]);
                        }
                        
                        // Set as default payment method in Stripe
                        Customer::update($customer->id, [
                            'invoice_settings' => ['default_payment_method' => $paymentMethodId]
                        ]);
                        
                        Log::info('Payment method set as default in Stripe', [
                            'payment_method_id' => $paymentMethodId,
                            'customer_id' => $customer->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error setting payment method as default in Stripe: ' . $e->getMessage());
                        // Continue processing even if setting default fails
                    }
                }
                
                Log::info('Payment saved with default status', [
                    'payment_id' => $payment->id,
                    'customer_email' => $customer->email,
                    'is_default' => $payment->is_default,
                    'payment_method_id' => $paymentMethodId
                ]);
                
                // Create the chess session record (without student_id for now)
                $chessSession = ChessSession::create([
                    'payment_id' => $payment->id,
                    'is_paid' => true, // First-time bookings are immediately paid
                    'session_type' => $sessionType,
                    'duration' => (int) ($paymentIntent->metadata['duration'] ?? 60),
                    'session_name' => $sessionDetails['name'] ?? ('Chess Session - ' . $sessionType),
                    'status' => 'booked',
                    'suggested_availability' => $suggestedAvailability,
                ]);
                
                // Prepare data for email
                $paymentData = [
                    'payment_id' => $payment->payment_id,
                    'amount' => $payment->amount,
                    'paid_at' => $payment->paid_at
                ];
                
                $sessionData = [
                    'session_type' => $sessionType,
                    'session_type_name' => self::SESSION_TYPES[$sessionType] ?? 'Chess Session',
                    'duration' => $chessSession->duration,
                    'status' => $chessSession->status
                ];
                
                // Create student account, send confirmation email, and get the student user
                $student = $this->createStudentAccountAndSendEmail(
                    $customer->email,
                    $customer->name,
                    $paymentData,
                    $sessionData,
                    $customer->id,
                    $paymentMethodId
                );
                
                // If we have a valid student user, update the chess session with the student_id
                if ($student && $student->id) {
                    $chessSession->update([
                        'student_id' => $student->id
                    ]);
                    
                    Log::info('Updated chess session with student ID', [
                        'session_id' => $chessSession->id,
                        'student_id' => $student->id
                    ]);
                    
                    // Send assignment requests to eligible teachers
                    try {
                        app(SessionAssignmentController::class)->sendAssignmentRequests($chessSession);
                    } catch (\Exception $e) {
                        Log::error('Error sending teacher assignment requests', [
                            'error' => $e->getMessage(),
                            'session_id' => $chessSession->id
                        ]);
                        // Continue processing as this is not critical for payment completion
                    }
                }
                
                return $payment;
            });
        } catch (\Exception $e) {
            Log::error('Error saving payment information: ' . $e->getMessage());
            return null;
        }
    }
}
