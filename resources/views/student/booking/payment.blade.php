<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">Payment for Additional Session</h1>
            <a href="{{ route('student.booking.calendar') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                Back to Calendar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Session Details -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Session Details</h3>
                            
                            <!-- Teacher Info -->
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <h4 class="font-medium mb-2">Your Teacher</h4>
                                <div class="flex items-center space-x-3">
                                    @if($teacher->teacherProfile && $teacher->teacherProfile->profile_image)
                                        <img src="{{ asset('storage/profile_images/' . $teacher->teacherProfile->profile_image) }}" 
                                            alt="{{ $teacher->name }}" class="w-12 h-12 object-cover rounded-full">
                                    @else
                                        <div class="w-12 h-12 bg-gray-200 flex items-center justify-center rounded-full">
                                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium">{{ $teacher->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $teacher->email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Session Info -->
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Session:</span>
                                    <span class="font-medium">{{ $session_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Date:</span>
                                    <span class="font-medium">{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Time:</span>
                                    <span class="font-medium">{{ \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Duration:</span>
                                    <span class="font-medium">{{ $duration }} minutes</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Type:</span>
                                    <span class="font-medium">{{ ucfirst($session_type) }}</span>
                                </div>
                                <hr class="my-4">
                                <div class="flex justify-between text-lg font-semibold">
                                    <span>Total:</span>
                                    <span>£{{ number_format($price, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Payment Information</h3>
                            
                            <form method="POST" action="{{ route('student.booking.payment.process') }}" id="payment-form">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="time" value="{{ $time }}">
                                <input type="hidden" name="duration" value="{{ $duration }}">
                                <input type="hidden" name="session_type" value="{{ $session_type }}">
                                <input type="hidden" name="payment_method" id="payment-method" value="">

                                <!-- Customer Details -->
                                <div class="mb-6">
                                    <h4 class="font-medium mb-3">Customer Details</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                            <input type="text" name="card_holder" id="card_holder" value="{{ $student->name }}" 
                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                            <input type="email" name="email" id="email" value="{{ $student->email }}" 
                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="mb-6">
                                    <h4 class="font-medium mb-3">Payment Method</h4>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Credit or Debit Card</label>
                                        <div id="card-element" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <!-- Stripe Elements will create form elements here -->
                                        </div>
                                        <div id="card-errors" role="alert" class="text-red-600 text-sm mt-2"></div>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <p class="text-sm text-gray-600">
                                        By completing this payment, you agree to book this chess session. Your payment method will be securely stored for future bookings.
                                    </p>
                                </div>

                                <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition duration-200 font-medium" id="submit-button">
                                    <div class="flex items-center justify-center">
                                        <div class="spinner hidden mr-2" id="spinner">
                                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                        <span id="button-text">Pay £{{ number_format($price, 2) }}</span>
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // Create a Stripe client
        const stripe = Stripe('{{ config('services.stripe.key') }}');

        // Create an instance of Elements
        const elements = stripe.elements();

        // Custom styling
        const style = {
            base: {
                color: '#424770',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#9e2146',
                iconColor: '#9e2146'
            }
        };

        // Create an instance of the card Element
        const cardElement = elements.create('card', {style: style});

        // Add an instance of the card Element into the `card-element` <div>
        cardElement.mount('#card-element');

        // Handle form submission
        const form = document.getElementById('payment-form');
        const cardErrors = document.getElementById('card-errors');
        const submitButton = document.getElementById('submit-button');
        const spinner = document.getElementById('spinner');
        const buttonText = document.getElementById('button-text');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Disable the submit button to prevent repeated clicks
            submitButton.disabled = true;
            spinner.classList.remove('hidden');
            buttonText.textContent = 'Processing...';

            // Create a payment method
            const result = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    name: document.getElementById('card_holder').value,
                    email: document.getElementById('email').value
                }
            });

            if (result.error) {
                // Show error to the customer
                cardErrors.textContent = result.error.message;
                submitButton.disabled = false;
                spinner.classList.add('hidden');
                buttonText.textContent = 'Pay £{{ number_format($price, 2) }}';
            } else {
                // Set the payment method ID in the hidden input
                document.getElementById('payment-method').value = result.paymentMethod.id;

                // Submit the form
                form.submit();
            }
        });
    </script>
</x-app-layout>
