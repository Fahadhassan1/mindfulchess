<x-app-layout>
                <div class="bg-green-600 text-white px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">Payment Transfer</h1>
                        <p class="text-green-100">Transfer ID: {{ $transfer->id }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold">Transfer Date</p>
                        <p class="text-green-100">{{ $transfer->transferred_at ? $transfer->transferred_at->format('d M Y') : 'Pending' }}</p>ame="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Payment Invoice - Transfer #{{ $transfer->id }}
            </h2>
            <div class="text-right">
                <span class="text-lg font-semibold text-green-600">£{{ number_format($transfer->amount, 2) }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="max-w-4xl mx-auto">
        <!-- Invoice Header -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-green-600 text-white px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">Payment Invoice</h1>
                        <p class="text-green-100">Transfer ID: {{ $transfer->id }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold">£{{ number_format($transfer->amount, 2) }}</p>
                        <p class="text-green-100">{{ $transfer->transferred_at ? $transfer->transferred_at->format('d M Y') : 'Pending' }}</p>
                    </div>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Company Info -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">From:</h2>
                        <div class="text-gray-600">
                            <p class="font-medium">Mindful Chess</p>
                            <p>Chess Tutoring Platform</p>
                            <p>support@mindfulchess.com</p>
                        </div>
                    </div>

                    <!-- Teacher Info -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">To:</h2>
                        <div class="text-gray-600">
                            <p class="font-medium">{{ $transfer->teacher->name }}</p>
                            <p>{{ $transfer->teacher->email }}</p>
                            @if($transfer->teacher->teacherProfile && $transfer->teacher->teacherProfile->stripe_account_id)
                                <p class="text-sm">Stripe Account: {{ substr($transfer->teacher->teacherProfile->stripe_account_id, 0, 12) }}...</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Session Details -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Session Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Session Name</p>
                            <p class="font-medium">{{ $transfer->session->session_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Student</p>
                            <p class="font-medium">{{ $transfer->session->student->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Date & Time</p>
                            <p class="font-medium">{{ $transfer->session->session_date ? $transfer->session->session_date->format('d M Y, H:i') : 'Not scheduled' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Duration</p>
                            <p class="font-medium">{{ $transfer->session->duration }} minutes</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Breakdown -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Breakdown</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Session Amount</span>
                            <span class="font-medium">£{{ number_format($transfer->total_session_amount * 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Application Fee ({{ round(($transfer->application_fee / $transfer->total_session_amount) * 100, 1) }}%)</span>
                            <span class="text-red-600">-£{{ number_format($transfer->application_fee, 2) }}</span>
                        </div>
                        <hr class="border-gray-200">
                        <div class="flex justify-between text-lg">
                            <span class="font-semibold text-gray-900">Your Earnings</span>
                            <span class="font-bold text-green-600">£{{ number_format($transfer->amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Transfer Information -->
                @if($transfer->status === 'completed' && $transfer->transferred_at)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-6">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <div>
                            <h3 class="text-sm font-medium text-green-800">Transfer Completed</h3>
                            <p class="text-sm text-green-600 mt-1">
                                Funds transferred on {{ $transfer->transferred_at->format('d M Y \a\t H:i') }}
                            </p>
                            @if($transfer->stripe_transfer_id)
                                <p class="text-xs text-green-600 mt-1">
                                    Stripe Transfer ID: {{ $transfer->stripe_transfer_id }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                @elseif($transfer->status === 'failed')
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <div>
                            <h3 class="text-sm font-medium text-red-800">Transfer Failed</h3>
                            <p class="text-sm text-red-600 mt-1">
                                This transfer was not successful. Please contact support for assistance.
                            </p>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-yellow-500 mr-3"></i>
                        <div>
                            <h3 class="text-sm font-medium text-yellow-800">Transfer Pending</h3>
                            <p class="text-sm text-yellow-600 mt-1">
                                This transfer is being processed and will be completed soon.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Banking Information -->
                @if($transfer->status === 'completed')
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-green-500 mr-3"></i>
                        <div>
                            <h3 class="text-sm font-medium text-green-800">Banking Information</h3>
                            <p class="text-sm text-green-600 mt-1">
                                Funds will appear in your bank account within 3-5 business days.
                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                The exact timing depends on your bank's processing schedule.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Notes -->
                @if($transfer->notes)
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Notes</h2>
                    <p class="text-gray-600 bg-gray-50 p-3 rounded">{{ $transfer->notes }}</p>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('teacher.transfers') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Transfers
                    </a>
                    
                    <button onclick="window.print()" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                        <i class="fas fa-print mr-2"></i>
                        Print Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .shadow-lg {
        box-shadow: none !important;
    }
}
</style>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
