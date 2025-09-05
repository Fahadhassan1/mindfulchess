<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">Chess Session Details</h1>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('student.sessions') }}" class="text-blue-500 hover:text-blue-700">
                    <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to All Sessions
                </a>
            </div>
    
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-6">
        
        <div class="p-6">
            <!-- Session Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Session Information</h3>
                    <div class="bg-gray-50 rounded-md p-4">
                        <p class="mb-2"><span class="font-medium">Name:</span> {{ $session->session_name }}</p>
                        <p class="mb-2"><span class="font-medium">Type:</span> {{ ucfirst($session->session_type) }}</p>
                        <p class="mb-2"><span class="font-medium">Duration:</span> {{ $session->duration }} minutes</p>
                        <p class="mb-2">
                            <span class="font-medium">Status:</span>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($session->status == 'completed') bg-green-100 text-green-800 
                                @elseif($session->status == 'booked') bg-blue-100 text-blue-800 
                                @elseif($session->status == 'canceled') bg-red-100 text-red-800 
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($session->status) }}
                            </span>
                        </p>
                        <p class="mb-2"><span class="font-medium">Booked On:</span> {{ $session->created_at->format('F d, Y') }}</p>
                        @if($session->scheduled_at)
                            <p class="mb-2"><span class="font-medium">Scheduled For:</span> {{ $session->scheduled_at->format('F d, Y g:i A') }}</p>
                        @endif
                        @if($session->completed_at)
                            <p class="mb-2"><span class="font-medium">Completed On:</span> {{ $session->completed_at->format('F d, Y g:i A') }}</p>
                        @endif
                        @if($session->notes)
                            <p class="mt-4"><span class="font-medium">Notes:</span></p>
                            <p class="mt-1 text-gray-600">{{ $session->notes }}</p>
                        @endif
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Teacher Information</h3>
                    @if($session->teacher)
                        <div class="bg-gray-50 rounded-md p-4">
                            <div class="flex items-start">
                                @if($session->teacher->teacherProfile && $session->teacher->teacherProfile->profile_image)
                                    <img src="{{ asset('storage/profile_images/' . $session->teacher->teacherProfile->profile_image) }}" 
                                         alt="{{ $session->teacher->name }}" 
                                         class="w-16 h-16 rounded-full object-cover mr-4">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center mr-4">
                                        <span class="text-gray-600 text-xl">{{ substr($session->teacher->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $session->teacher->name }}</p>
                                    {{-- <p class="text-gray-600">{{ $session->teacher->email }}</p> --}}
                                    @if($session->teacher->teacherProfile)
                                        @if($session->teacher->teacherProfile->experience_years)
                                            <p class="text-sm text-gray-500 mt-1">{{ $session->teacher->teacherProfile->experience_years }} years of experience</p>
                                        @endif
                                        @if($session->teacher->teacherProfile->specialties)
                                            <p class="text-sm text-gray-500">Specialties: {{ implode(', ', (array) $session->teacher->teacherProfile->specialties) }}</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 rounded-md p-4 border border-yellow-200">
                            <p class="text-yellow-700">No teacher has been assigned to this session yet.</p>
                            <p class="text-sm text-yellow-600 mt-2">Our team is working to match you with the best teacher for your needs.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Payment Info -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Payment Information</h3>
                @if($session->payment)
                    <div class="bg-gray-50 rounded-md p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="mb-2"><span class="font-medium">Amount:</span> £{{ number_format($session->payment->amount, 2) }}</p>
                                <p class="mb-2"><span class="font-medium">Payment ID:</span> {{ $session->payment->payment_id }}</p>
                                <p class="mb-2"><span class="font-medium">Payment Date:</span> {{ $session->payment->paid_at->format('F d, Y') }}</p>
                            </div>
                            <div>
                                @if($session->payment->coupon_code)
                                    <p class="mb-2"><span class="font-medium">Coupon Code:</span> {{ $session->payment->coupon_code }}</p>
                                    <p class="mb-2"><span class="font-medium">Discount:</span> {{ $session->payment->discount_percentage }}%</p>
                                    <p class="mb-2"><span class="font-medium">Original Price:</span> £{{ number_format($session->payment->original_amount, 2) }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <a href="{{ route('student.payments.invoice', $session->payment) }}" 
                               class="inline-flex items-center px-4 py-2 bg-primary-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                View Invoice
                            </a>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 rounded-md p-4 border border-red-200">
                        <p class="text-red-700">No payment information found for this session.</p>
                    </div>
                @endif
            </div>
            
            <!-- Homework Section -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Homework Assignments</h3>
                @if($session->homework && $session->homework->count() > 0)
                    <div class="space-y-4">
                        @foreach($session->homework as $homework)
                            <div class="bg-purple-50 rounded-md p-4 border border-purple-200">
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="font-semibold text-purple-900">{{ $homework->title }}</h4>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($homework->status == 'completed') bg-green-100 text-green-800 
                                        @elseif($homework->status == 'in_progress') bg-yellow-100 text-yellow-800 
                                        @else bg-purple-100 text-purple-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $homework->status)) }}
                                    </span>
                                </div>
                                
                                <p class="text-purple-800 mb-3">{{ $homework->description }}</p>
                                
                                @if($homework->instructions)
                                    <div class="mb-3">
                                        <p class="font-medium text-purple-900 mb-1">Instructions:</p>
                                        <p class="text-purple-700">{{ $homework->instructions }}</p>
                                    </div>
                                @endif
                                
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-purple-600">
                                        <p>Assigned: {{ $homework->created_at->format('M d, Y') }}</p>
                                        <p>Teacher: {{ $homework->teacher->name }}</p>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        @if($homework->attachment_path)
                                            <a href="{{ route('student.homework.download', $homework) }}" 
                                               class="inline-flex items-center px-3 py-1 border border-purple-300 rounded text-sm font-medium text-purple-700 bg-white hover:bg-purple-50 transition-colors duration-200">
                                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Download
                                            </a>
                                        @endif
                                        
                                        <a href="{{ route('student.homework.show', $homework) }}" 
                                           class="inline-flex items-center px-3 py-1 border border-purple-300 rounded text-sm font-medium text-purple-700 bg-purple-100 hover:bg-purple-200 transition-colors duration-200">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-gray-50 rounded-md p-4 border border-gray-200">
                        <p class="text-gray-600">No homework has been assigned for this session yet.</p>
                    </div>
                @endif
            </div>
        </div>
            </div>
        </div>
    </div>
</x-app-layout>
