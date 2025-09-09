<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('🔄 Update Payment Method') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                        <strong>💳 Update Your Payment Information</strong><br>
                        Please enter your new card details below. This will be used for all future session payments.
                    </div>

                    <form id="payment-form" action="{{ route('student.payment-methods.update.process') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method_id" id="payment-method-id">

                        <div class="mb-6">
                            <label for="card-element" class="block text-sm font-medium text-gray-700 mb-2">Card Information</label>
                            <div id="card-element" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" style="height: 45px;">
                                <!-- Stripe Elements will create form elements here -->
                            </div>
                            <div id="card-errors" role="alert" class="text-red-600 text-sm mt-2"></div>
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center">
                                <input id="save-card" type="checkbox" checked disabled class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="save-card" class="ml-2 block text-sm text-gray-700">
                                    Save this card for future payments
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Your card information is securely stored by Stripe.</p>
                        </div>

                        <div class="mb-6">
                            <button type="submit" id="submit-button" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 font-medium">
                                <span id="button-text">🔒 Update Payment Method</span>
                                <span id="spinner" class="hidden ml-2">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </form>

                    <div class="mb-6">
                        <a href="{{ route('student.payment-methods') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            ← Back to Payment Methods
                        </a>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h6 class="font-semibold text-gray-800 mb-2">🔒 Security Information</h6>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Your payment information is encrypted and securely processed by Stripe</li>
                            <li>• We never store your full card details on our servers</li>
                            <li>• You can update your payment method at any time</li>
                            <li>• Failed payments will be automatically retried once you update your card</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('{{ env('STRIPE_KEY') }}');
    const elements = stripe.elements();

    // Create card element
    const cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#424770',
                '::placeholder': {
                    color: '#aab7c4',
                },
            },
        },
    });

    cardElement.mount('#card-element');

    // Handle form submission
    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        // Disable submit button and show spinner
        submitButton.disabled = true;
        buttonText.classList.add('hidden');
        spinner.classList.remove('hidden');

        try {
            // Create payment method
            const {error, paymentMethod} = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    name: '{{ $student->name }}',
                    email: '{{ $student->email }}',
                },
            });

            if (error) {
                // Show error to customer
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
                
                // Re-enable submit button
                submitButton.disabled = false;
                buttonText.classList.remove('hidden');
                spinner.classList.add('hidden');
            } else {
                // Set payment method ID and submit form
                document.getElementById('payment-method-id').value = paymentMethod.id;
                form.submit();
            }
        } catch (err) {
            console.error('Error creating payment method:', err);
            
            // Re-enable submit button
            submitButton.disabled = false;
            buttonText.classList.remove('hidden');
            spinner.classList.add('hidden');
            
            // Show error
            const errorElement = document.getElementById('card-errors');
            errorElement.textContent = 'An unexpected error occurred. Please try again.';
        }
    });

    // Listen for changes in the card element
    cardElement.on('change', ({error}) => {
        const errorElement = document.getElementById('card-errors');
        if (error) {
            errorElement.textContent = error.message;
        } else {
            errorElement.textContent = '';
        }
    });
});
</script>
</x-app-layout>
