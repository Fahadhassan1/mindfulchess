<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payment Details') }} - {{ $payment->payment_id }}
            </h2>
            <a href="{{ route('admin.payments.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Payments
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Payment Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment ID</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $payment->payment_id }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($payment->status == 'succeeded') bg-green-100 text-green-800
                                    @elseif($payment->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($payment->status == 'failed') bg-red-100 text-red-800
                                    @elseif($payment->status == 'refunded') bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">£{{ number_format($payment->amount, 2) }} {{ strtoupper($payment->currency) }}</p>
                                @if($payment->original_amount && $payment->original_amount != $payment->amount)
                                    <p class="text-sm text-gray-500">
                                        Original: £{{ number_format($payment->original_amount, 2) }}
                                        @if($payment->coupon_code)
                                            ({{ $payment->discount_percentage }}% off with {{ $payment->coupon_code }})
                                        @endif
                                    </p>
                                @endif
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <p class="mt-1 text-sm text-gray-900">{{ ucfirst($payment->payment_method_type) }}</p>
                                @if($payment->payment_method_id)
                                    <p class="text-sm text-gray-500">ID: {{ $payment->payment_method_id }}</p>
                                @endif
                            </div>
                            
                            @if($payment->paid_at)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Paid At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->paid_at->format('l, F j, Y \a\t g:i A') }}</p>
                            </div>
                            @endif
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Created At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->created_at->format('l, F j, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Customer ID</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $payment->customer_id }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->customer_name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->customer_email }}</p>
                            </div>
                            
                            @if($payment->user)
                            <div class="mt-4">
                                <a href="{{ route('admin.students.show', $payment->user) }}" class="text-blue-600 hover:text-blue-900">
                                    View Customer Profile
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Associated Session -->
                @if($payment->session)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Associated Session</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Session ID</label>
                                <p class="mt-1 text-sm text-gray-900">#{{ $payment->session->id }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Student</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->session->student->name }}</p>
                            </div>
                            
                            @if($payment->session->teacher)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teacher</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->session->teacher->name }}</p>
                            </div>
                            @endif
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Scheduled Time</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->session->scheduled_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Session Status</label>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($payment->session->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($payment->session->status == 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($payment->session->status == 'completed') bg-green-100 text-green-800
                                    @elseif($payment->session->status == 'cancelled') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($payment->session->status) }}
                                </span>
                            </div>
                            
                            <div class="mt-4">
                                <a href="{{ route('admin.sessions.show', $payment->session) }}" class="text-blue-600 hover:text-blue-900">
                                    View Session Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Stripe Data -->
                @if($payment->stripe_data)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Stripe Data</h3>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <pre class="text-xs text-gray-700 overflow-x-auto">{{ json_encode($payment->stripe_data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                        
                        <div class="space-y-4">
                            <a href="{{ route('admin.payments.invoice', $payment) }}" target="_blank" 
                               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-block">
                                View Invoice
                            </a>
                            
                            {{-- @if($payment->status == 'succeeded')
                            <button onclick="openRefundModal({{ $payment->id }}, {{ $payment->amount }})" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-4">
                                Process Refund
                            </button>
                            @endif --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refund Modal -->
    <div id="refundModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">Process Refund</h3>
                <form id="refundForm" method="POST" class="mt-4">
                    @csrf
                    <div class="mb-4 text-left">
                        <label for="refund_amount" class="block text-sm font-medium text-gray-700">Refund Amount</label>
                        <input type="number" name="refund_amount" id="refund_amount" step="0.01" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <p class="text-sm text-gray-500 mt-1">Maximum refundable amount: £<span id="max_amount"></span></p>
                    </div>
                    <div class="mb-4 text-left">
                        <label for="refund_reason" class="block text-sm font-medium text-gray-700">Reason</label>
                        <textarea name="reason" id="refund_reason" rows="3" required
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                                  placeholder="Enter reason for refund..."></textarea>
                    </div>
                    <div class="flex justify-between">
                        <button type="button" onclick="closeRefundModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancel
                        </button>
                        {{-- <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Process Refund
                        </button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRefundModal(paymentId, maxAmount) {
            document.getElementById('refundForm').action = `/admin/payments/${paymentId}/refund`;
            document.getElementById('refund_amount').max = maxAmount;
            document.getElementById('refund_amount').value = maxAmount;
            document.getElementById('max_amount').textContent = maxAmount.toFixed(2);
            document.getElementById('refundModal').classList.remove('hidden');
        }

        function closeRefundModal() {
            document.getElementById('refundModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
