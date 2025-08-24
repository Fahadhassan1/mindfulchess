<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payment Management') }}
            </h2>
            <a href="{{ route('admin.payments.export', request()->query()) }}" class="px-4 py-2 bg-primary-800 hover:bg-primary-700 text-white font-bold rounded">
                Export Payments
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Total Payments</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['total_payments'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Total Revenue</h3>
                    <p class="text-2xl font-bold text-emerald-600">£{{ number_format($stats['total_revenue'], 2) }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">This Month</h3>
                    <p class="text-2xl font-bold text-green-600">£{{ number_format($stats['this_month_revenue'], 2) }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Average Payment</h3>
                    <p class="text-2xl font-bold text-purple-600">£{{ number_format($stats['average_payment'], 2) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   placeholder="Customer name, email, or payment ID" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Statuses</option>
                                <option value="succeeded" {{ request('status') == 'succeeded' ? 'selected' : '' }}>Succeeded</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-primary-800 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                            <a href="{{ route('admin.payments.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ms-2">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payments as $payment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $payment->payment_id }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $payment->customer_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $payment->customer_email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                        
                                        <div class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            £{{ number_format($payment->amount, 2) }}
                                        </div>
                                        @if($payment->coupon_code)
                                            <div class="text-sm text-green-600">
                                                Coupon: {{ $payment->coupon_code }}
                                            </div>
                                        @endif
                                     </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ ucfirst($payment->payment_method_type) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($payment->status == 'succeeded') bg-green-100 text-green-800
                                            @elseif($payment->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($payment->status == 'failed') bg-red-100 text-red-800
                                            @elseif($payment->status == 'refunded') bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($payment->paid_at)
                                            <div class="text-sm text-gray-900">
                                                {{ $payment->paid_at->format('M d, Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $payment->paid_at->format('h:i A') }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">Not paid</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="text-green-600 hover:text-green-900 mr-3">View</a>
                                        <a href="{{ route('admin.payments.invoice', $payment) }}" class="text-green-600 hover:text-green-900 mr-3" target="_blank">Invoice</a>
                                        
                                        {{-- @if($payment->status == 'succeeded')
                                            <button onclick="openRefundModal({{ $payment->id }}, {{ $payment->amount }})" 
                                                    class="text-red-600 hover:text-red-900">
                                                Refund
                                            </button>
                                        @endif --}}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No payments found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $payments->appends(request()->query())->links() }}
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
                        <p class="text-sm text-gray-500 mt-1">Maximum refundable amount: $<span id="max_amount"></span></p>
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
