<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">My Payment Methods</h1>
            <div class="flex gap-2">
                <a href="{{ route('student.payment-methods.update') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-200">
                     Update Payment Method
                </a>
            </div>
        </div>
    </x-slot>
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    
                    @if(!$paymentMethod)
                        <div class="bg-white shadow-md rounded-lg p-8 text-center">
                            <div class="mb-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 text-lg mb-4">You don't have any saved payment method yet.</p>
                            <a href="{{ route('student.booking.calendar') }}" class="inline-block px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200">
                                Book a Session to Add Payment Method
                            </a>
                        </div>
                    @else
                        <div class="mb-6">
                            <p class="text-gray-600">Your default payment method is shown below. This will be used automatically for all session bookings.</p>
                        </div>
                        
                        <div class="grid gap-4">
                            <div class="border border-green-500 bg-green-50 rounded-lg p-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <!-- Card Icon -->
                                            <div class="flex-shrink-0">
                                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                </svg>
                                            </div>
                                            
                                            <!-- Card Details -->
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <h3 class="font-medium text-gray-900">
                                                        {{ ucfirst($paymentMethod->type) }} Payment Method
                                                    </h3>
                                                    @php
                                                        // Always show as default attached since it's the only one
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Default Attached
                                                    </span>
                                                </div>
                                                
                                                @if($paymentMethod->card)
                                                    <p class="text-sm text-gray-500">
                                                        **** **** **** {{ $paymentMethod->card->last4 }} • 
                                                        {{ strtoupper($paymentMethod->card->brand) }} • 
                                                        Expires {{ str_pad($paymentMethod->card->exp_month, 2, '0', STR_PAD_LEFT) }}/{{ $paymentMethod->card->exp_year }}
                                                    </p>
                                                @else
                                                    <p class="text-sm text-gray-500">
                                                        Payment method attached to your account
                                                    </p>
                                                @endif
                                                
                                                <p class="text-xs text-gray-400 mt-1">
                                                    Added: {{ $paymentMethod->created_at->format('F d, Y \a\t g:i A') }}
                                                </p>
                                            </div>
                                        </div>
                            
                            <div class="mt-3 text-sm text-green-700">
                                <p>✓ This payment method is automatically used for all session bookings</p>
                            </div>
                        </div>
                        
                       
                    @endif
                </div>
                 <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="flex-shrink-0 h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">About Your Payment Method</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>This payment method will be charged automatically for new session bookings</li>
                                            <li>If a payment fails, you'll receive an email notification to update your payment method</li>
                                            <li>Your payment information is securely stored with Stripe and encrypted</li>
                                            <li>You can update your payment method anytime using the button above</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
            </div>
        </div>
    </div>
</x-app-layout>
