<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">My Payment Methods</h1>
            <a href="{{ route('student.payments') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                Back to Payment History
            </a>
        </div>
    </x-slot>
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif
                    
                    @if($paymentMethods->isEmpty())
                        <div class="bg-white shadow-md rounded-lg p-8 text-center">
                            <div class="mb-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 text-lg mb-4">You don't have any saved payment methods yet.</p>
                            <a href="{{ route('student.booking.calendar') }}" class="inline-block px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200">
                                Book a Session to Add Payment Method
                            </a>
                        </div>
                    @else
                        <div class="mb-6">
                            <p class="text-gray-600">Manage your saved payment methods. Your default payment method will be used automatically for future bookings.</p>
                        </div>
                        
                        <div class="grid gap-4">
                            @foreach($paymentMethods as $paymentMethod)
                                <div class="border border-gray-200 rounded-lg p-6 {{ $paymentMethod->is_default ? 'border-green-500 bg-green-50' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <!-- Card Icon -->
                                            <div class="flex-shrink-0">
                                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                </svg>
                                            </div>
                                            
                                            <!-- Card Details -->
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <h3 class="font-medium text-gray-900">
                                                        {{ ucfirst($paymentMethod->payment_method_type) }} Payment
                                                    </h3>
                                                    @if($paymentMethod->is_default)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Default
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                @if(isset($paymentMethod->stripe_data['payment_method']['card']))
                                                    @php
                                                        $card = $paymentMethod->stripe_data['payment_method']['card'];
                                                    @endphp
                                                    <p class="text-sm text-gray-500">
                                                        **** **** **** {{ $card['last4'] ?? 'xxxx' }} • 
                                                        {{ strtoupper($card['brand'] ?? 'card') }} • 
                                                        Expires {{ $card['exp_month'] ?? 'xx' }}/{{ $card['exp_year'] ?? 'xx' }}
                                                    </p>
                                                @else
                                                    <p class="text-sm text-gray-500">
                                                        Payment method from {{ $paymentMethod->created_at->format('M d, Y') }}
                                                    </p>
                                                @endif
                                                
                                                <p class="text-xs text-gray-400 mt-1">
                                                    Added: {{ $paymentMethod->created_at->format('F d, Y \a\t g:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="flex items-center space-x-3">
                                            @if(!$paymentMethod->is_default)
                                                <form method="POST" action="{{ route('student.payment-methods.set-default', $paymentMethod) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        Set as Default
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <span class="text-sm text-gray-500">
                                                Used {{ $paymentMethods->where('payment_method_id', $paymentMethod->payment_method_id)->count() }} 
                                                {{ $paymentMethods->where('payment_method_id', $paymentMethod->payment_method_id)->count() === 1 ? 'time' : 'times' }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($paymentMethod->is_default)
                                        <div class="mt-3 text-sm text-green-700">
                                            <p>✓ This payment method will be used automatically for future bookings</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="flex-shrink-0 h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">About Payment Methods</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Your default payment method will be charged automatically for new session bookings</li>
                                            <li>If a payment fails, you'll be prompted to enter a new payment method</li>
                                            <li>All payment methods are securely stored with Stripe and encrypted</li>
                                            <li>You can change your default payment method at any time</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
