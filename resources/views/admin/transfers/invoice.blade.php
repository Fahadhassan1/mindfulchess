<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Admin Transfer Invoice - Transfer #{{ $transfer->id }}
            </h2>
            <div class="text-right">
                <span class="text-lg font-semibold text-red-600">£{{ number_format($transfer->amount, 2) }}</span>
                <div class="text-sm text-gray-500">Administrative View</div>
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
            <div class="bg-red-600 text-white px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">Admin Transfer Invoice</h1>
                        <p class="text-red-100">Transfer ID: {{ $transfer->id }}</p>
                        <span class="inline-block bg-red-800 px-3 py-1 rounded-full text-sm mt-2">Administrative Copy</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold">£{{ number_format($transfer->amount, 2) }}</p>
                        <p class="text-red-100">{{ $transfer->transferred_at ? $transfer->transferred_at->format('d M Y') : 'Pending' }}</p>
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
                            <p class="text-sm text-red-600">Teacher ID: {{ $transfer->teacher->id }}</p>
                            @if($transfer->teacher->teacherProfile && $transfer->teacher->teacherProfile->stripe_account_id)
                                <p class="text-sm">Stripe Account: {{ $transfer->teacher->teacherProfile->stripe_account_id }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Administrative Information -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-red-900 mb-4">Administrative Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-red-600">Transfer ID</p>
                            <p class="font-medium">{{ $transfer->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-red-600">Status</p>
                            <p class="font-medium">{{ ucfirst($transfer->status) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-red-600">Created</p>
                            <p class="font-medium">{{ $transfer->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        @if($transfer->stripe_transfer_id)
                        <div>
                            <p class="text-sm text-red-600">Stripe Transfer ID</p>
                            <p class="font-medium text-xs">{{ $transfer->stripe_transfer_id }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-sm text-red-600">Updated</p>
                            <p class="font-medium">{{ $transfer->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-red-600">Teacher ID</p>
                            <p class="font-medium">{{ $transfer->teacher_id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Session Details -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Session Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Session ID</p>
                            <p class="font-medium">{{ $transfer->session->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Session Name</p>
                            <p class="font-medium">{{ $transfer->session->session_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Student</p>
                            <p class="font-medium">{{ $transfer->session->student->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Student ID</p>
                            <p class="font-medium">{{ $transfer->session->student->id }}</p>
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

                <!-- Payment Information -->
                @if($transfer->session && $transfer->session->payment)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-blue-900 mb-4">Related Payment Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-blue-600">Payment ID</p>
                            <p class="font-medium">{{ $transfer->session->payment->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600">Payment Amount</p>
                            <p class="font-medium">£{{ number_format($transfer->session->payment->amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600">Payment Status</p>
                            <p class="font-medium">{{ ucfirst($transfer->session->payment->status) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600">Payment Date</p>
                            <p class="font-medium">{{ $transfer->session->payment->paid_at ? $transfer->session->payment->paid_at->format('d M Y, H:i') : 'Not paid' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Payment Breakdown -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Breakdown</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Session Amount</span>
                            <span class="font-medium">£{{ number_format(($transfer->amount + $transfer->application_fee), 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Application Fee ({{ $transfer->application_fee > 0 ? round(($transfer->application_fee / ($transfer->amount + $transfer->application_fee)) * 100, 1) : 0 }}%)</span>
                            <span class="text-red-600">-£{{ number_format($transfer->application_fee, 2) }}</span>
                        </div>
                        <hr class="border-gray-200">
                        <div class="flex justify-between text-lg">
                            <span class="font-semibold text-gray-900">Teacher Earnings</span>
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
                                This transfer was not successful. Admin action may be required.
                            </p>
                            @if($transfer->failure_reason)
                                <p class="text-xs text-red-600 mt-1">
                                    Reason: {{ $transfer->failure_reason }}
                                </p>
                            @endif
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
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                        <div>
                            <h3 class="text-sm font-medium text-blue-800">Banking Information</h3>
                            <p class="text-sm text-blue-600 mt-1">
                                Funds will appear in teacher's bank account within 3-5 business days.
                            </p>
                            <p class="text-xs text-blue-600 mt-1">
                                The exact timing depends on the teacher's bank processing schedule.
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
                    <a href="{{ route('admin.transfers.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Transfers
                    </a>
                    
                    <div class="space-x-2">
                        <a href="{{ route('admin.transfers.show', $transfer) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-eye mr-2"></i>
                            View Details
                        </a>
                        
                        <button onclick="window.print()" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-200">
                            <i class="fas fa-print mr-2"></i>
                            Print Invoice
                        </button>
                    </div>
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
    
    .bg-red-600 {
        background-color: #dc2626 !important;
        -webkit-print-color-adjust: exact;
    }
}
</style>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
