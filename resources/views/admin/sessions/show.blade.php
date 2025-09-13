<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Session Details') }} - #{{ $session->id }}
            </h2>
            <a href="{{ route('admin.sessions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Sessions
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Session Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Session Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($session->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($session->status == 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($session->status == 'completed') bg-green-100 text-green-800
                                    @elseif($session->status == 'cancelled') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Scheduled Time</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $session->scheduled_at->format('l, F j, Y \a\t g:i A') }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Duration</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $session->duration }} minutes</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    @if($session->payment)
                                        £{{ number_format($session->payment->amount, 2) }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            
                            @if($session->notes)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Session Notes</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $session->notes }}</p>
                            </div>
                            @endif
                            
                            @if($session->admin_notes)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Admin Notes</label>
                                <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $session->admin_notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Participants -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Participants</h3>
                        
                        <div class="space-y-6">
                            <!-- Student Info -->
                            <div>
                                <h4 class="font-medium text-gray-900">Student</h4>
                                <div class="mt-2 space-y-2">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Name:</span> {{ $session->student->name }}
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Email:</span> {{ $session->student->email }}
                                    </p>
                                    @if($session->student->studentProfile && $session->student->studentProfile->level)
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Level:</span> {{ ucfirst($session->student->studentProfile->level) }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Teacher Info -->
                            <div>
                                <h4 class="font-medium text-gray-900">Teacher</h4>
                                @if($session->teacher)
                                <div class="mt-2 space-y-2">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Name:</span> {{ $session->teacher->name }}
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Email:</span> {{ $session->teacher->email }}
                                    </p>
                                    @if($session->teacher->teacherProfile && $session->teacher->teacherProfile->teaching_type)
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Teaching Type:</span> {{ ucfirst($session->teacher->teacherProfile->teaching_type) }}
                                    </p>
                                    @endif
                                </div>
                                @else
                                <p class="text-sm text-gray-500 mt-2">No teacher assigned</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                @if($session->payment)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment ID</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $session->payment->payment_id }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount Paid</label>
                                <p class="mt-1 text-sm text-gray-900">£{{ number_format($session->payment->amount, 2) }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($session->payment->status == 'succeeded') bg-green-100 text-green-800
                                    @elseif($session->payment->status == 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($session->payment->status) }}
                                </span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <p class="mt-1 text-sm text-gray-900">{{ ucfirst($session->payment->payment_method_type) }}</p>
                            </div>
                            
                            @if($session->payment->paid_at)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Paid At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $session->payment->paid_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                            @endif
                            
                            <div class="mt-4">
                                <a href="{{ route('admin.payments.show', $session->payment) }}" class="text-blue-600 hover:text-blue-900">
                                    View Full Payment Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                        <p class="text-sm text-gray-500">No payment associated with this session.</p>
                    </div>
                </div>
                @endif

                <!-- Homework -->
                @if($session->homework->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Homework Assigned</h3>
                        
                        <div class="space-y-4">
                            @foreach($session->homework as $homework)
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900">{{ $homework->title }}</h4>
                                @if($homework->description)
                                <p class="text-sm text-gray-600 mt-1">{{ $homework->description }}</p>
                                @endif
                                @if($homework->due_date)
                                <p class="text-sm text-gray-500 mt-1">Due: {{ $homework->due_date->format('M d, Y') }}</p>
                                @endif
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full mt-2
                                    @if($homework->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($homework->status == 'completed') bg-green-100 text-green-800
                                    @elseif($homework->status == 'submitted') bg-blue-100 text-blue-800
                                    @endif">
                                    {{ ucfirst($homework->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
