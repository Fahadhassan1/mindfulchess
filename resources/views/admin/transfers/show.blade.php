<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Transfer Details') }} - #{{ $transfer->id }}
            </h2>
            <a href="{{ route('admin.transfers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Transfers
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Transfer Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Transfer Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Transfer ID</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">#{{ $transfer->id }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($transfer->status == 'completed') bg-green-100 text-green-800
                                    @elseif($transfer->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($transfer->status == 'failed') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($transfer->status) }}
                                </span>
                            </div>
                            
                            @if($transfer->stripe_transfer_id)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Stripe Transfer ID</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $transfer->stripe_transfer_id }}</p>
                            </div>
                            @endif
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Transfer Amount</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">£{{ number_format($transfer->amount, 2) }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Application Fee</label>
                                <p class="mt-1 text-sm text-gray-900">£{{ number_format($transfer->application_fee, 2) }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Session Amount</label>
                                <p class="mt-1 text-sm text-gray-900">£{{ number_format($transfer->total_session_amount * 100, 2) }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Created At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $transfer->created_at->format('l, F j, Y \a\t g:i A') }}</p>
                            </div>
                            
                            @if($transfer->transferred_at)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Transferred At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $transfer->transferred_at->format('l, F j, Y \a\t g:i A') }}</p>
                            </div>
                            @endif
                            
                            @if($transfer->notes)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $transfer->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Teacher Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Teacher Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $transfer->teacher->name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $transfer->teacher->email }}</p>
                            </div>
                            
                            @if($transfer->teacher->teacherProfile)
                                @if($transfer->teacher->teacherProfile->teaching_type)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Teaching Type</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst($transfer->teacher->teacherProfile->teaching_type) }}</p>
                                </div>
                                @endif
                                
                                @if($transfer->teacher->teacherProfile->stripe_account_id)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stripe Account ID</label>
                                    <p class="mt-1 text-sm text-gray-900 font-mono">{{ $transfer->teacher->teacherProfile->stripe_account_id }}</p>
                                </div>
                                @endif
                            @endif
                            
                            <div class="mt-4">
                                <a href="{{ route('admin.teachers.show', $transfer->teacher) }}" class="text-blue-600 hover:text-blue-900">
                                    View Teacher Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Associated Session -->
                @if($transfer->session)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Associated Session</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Session ID</label>
                                <p class="mt-1 text-sm text-gray-900">#{{ $transfer->session->id }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Student</label>
                                @if($transfer->session && $transfer->session->student)
                                    <p class="mt-1 text-sm text-gray-900">{{ $transfer->session->student->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $transfer->session->student->email }}</p>
                                @else
                                    <p class="mt-1 text-sm text-gray-500">Student information not available</p>
                                @endif
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Scheduled Time</label>
                                <p class="text-sm font-medium text-gray-500">Session Date</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    @if($transfer->session && $transfer->session->scheduled_at)
                                        {{ $transfer->session->scheduled_at->format('l, F j, Y \a\t g:i A') }}
                                    @else
                                        Not scheduled
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Duration</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    @if($transfer->session)
                                        {{ $transfer->session->duration }} minutes
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Session Status</label>
                                @if($transfer->session)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($transfer->session->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($transfer->session->status == 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($transfer->session->status == 'completed') bg-green-100 text-green-800
                                        @elseif($transfer->session->status == 'cancelled') bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($transfer->session->status) }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Unknown
                                    </span>
                                @endif
                            </div>
                            
                            <div class="mt-4">
                                <a href="{{ route('admin.sessions.show', $transfer->session) }}" class="text-blue-600 hover:text-blue-900">
                                    View Session Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Payment Information -->
                @if($transfer->session && $transfer->session->payment)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment ID</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $transfer->session->payment->payment_id }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Amount</label>
                                <p class="mt-1 text-sm text-gray-900">£{{ number_format($transfer->session->payment->amount, 2) }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                                @if($transfer->session && $transfer->session->payment)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($transfer->session->payment->status == 'succeeded') bg-green-100 text-green-800
                                        @elseif($transfer->session->payment->status == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($transfer->session->payment->status) }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Unknown
                                    </span>
                                @endif
                            </div>
                            
                            <div class="mt-4">
                                <a href="{{ route('admin.payments.show', $transfer->session->payment) }}" class="text-blue-600 hover:text-blue-900">
                                    View Payment Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                        
                        <div class="space-y-4">
                            <a href="{{ route('admin.transfers.invoice', $transfer) }}" target="_blank" 
                               class="bg-primary-800 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                                View Invoice
                            </a>
                            
                            @if($transfer->status == 'failed')
                            <form method="POST" action="{{ route('admin.transfers.retry', $transfer) }}" class="inline ml-4">
                                @csrf
                                <button type="submit" onclick="return confirm('Are you sure you want to retry this transfer?')" 
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Retry Transfer
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
