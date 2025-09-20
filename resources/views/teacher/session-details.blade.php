<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Session Details
            </h2>
            <a href="{{ route('teacher.sessions') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                Back to Sessions
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">


                    <!-- Session Overview -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <!-- Session Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Session Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Session Name:</span>
                                    <p class="text-gray-900">{{ $session->session_name ?? 'Chess Session' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Type:</span>
                                    <p class="text-gray-900">{{ ucfirst($session->session_type) }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Duration:</span>
                                    <p class="text-gray-900">{{ $session->duration }} minutes</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Status:</span>
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$session->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </div>
                                @if($session->scheduled_at)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Scheduled:</span>
                                        <p class="text-gray-900">{{ $session->scheduled_at->format('F d, Y \a\t g:i A') }}</p>
                                    </div>
                                @endif
                                @if($session->completed_at)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Completed:</span>
                                        <p class="text-gray-900">{{ $session->completed_at->format('F d, Y \a\t g:i A') }}</p>
                                    </div>
                                @endif
                                @if($session->meeting_link)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Meeting Link:</span>
                                        <p class="text-gray-900">
                                            <a href="{{ $session->meeting_link }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                                {{ $session->meeting_link }}
                                            </a>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Student Information -->
                        <div class="bg-green-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Student Information</h3>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 bg-green-200 rounded-full flex items-center justify-center">
                                        <span class="text-lg font-medium text-green-800">
                                            {{ substr($session->student->name ?? 'Student', 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium text-gray-900">{{ $session->student->name ?? 'Student' }}</h4>
                                        {{-- <p class="text-sm text-gray-500">{{ $session->student->email ?? 'N/A' }}</p> --}}
                                    </div>
                                </div>
                                @if($session->student->studentProfile)
                                    @if($session->student->studentProfile->session_type_preference === 'adult' && $session->student->studentProfile->chess_rating)
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Chess Rating:</span>
                                            <p class="text-gray-900">{{ $session->student->studentProfile->chess_rating }}</p>
                                        </div>
                                    @elseif($session->student->studentProfile->level)
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Chess Level:</span>
                                            <p class="text-gray-900">{{ ucfirst($session->student->studentProfile->level) }}</p>
                                        </div>
                                    @endif
                                    @if($session->student->studentProfile->age && $session->student->studentProfile->session_type_preference !== 'adult')
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Age:</span>
                                            <p class="text-gray-900">{{ $session->student->studentProfile->age }} years old</p>
                                        </div>
                                    @endif
                                    @if($session->student->studentProfile->learning_goals)
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Learning Goals:</span>
                                            <p class="text-gray-900">{{ $session->student->studentProfile->learning_goals }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    {{-- @if($session->payment)
                    <div class="bg-green-50 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Amount:</span>
                                <p class="text-lg font-semibold text-green-600">£{{ $session->payment->amount }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Payment Status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $session->payment->status === 'succeeded' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($session->payment->status) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Payment Date:</span>
                                <p class="text-gray-900">{{ $session->payment->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif --}}

                    <!-- Session Notes -->
                    <div class="bg-purple-50 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Session Notes</h3>
                        @if($session->notes)
                            <div class="prose max-w-none">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $session->notes }}</p>
                            </div>
                        @else
                            <p class="text-gray-500 italic">No notes available for this session.</p>
                        @endif
                        
                        <!-- Add/Edit Notes Form -->
                        <div class="mt-4">
                            <form method="POST" action="{{ route('teacher.sessions.update-notes', $session) }}">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Update Notes:</label>
                                    <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Add notes about this session...">{{ $session->notes }}</textarea>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        Update Notes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Homework Section -->
                    @if($session->homework->count() > 0)
                        <div class="bg-blue-50 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigned Homework</h3>
                            @foreach($session->homework as $homework)
                                <div class="bg-white rounded-md p-4 border border-blue-200 mb-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $homework->title }}</h4>
                                            <p class="text-sm text-gray-600">{{ $homework->description }}</p>
                                        </div>
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $homework->status_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $homework->status)) }}
                                        </span>
                                    </div>
                                    
                                    @if($homework->instructions)
                                        <div class="mb-3">
                                            <p class="text-sm font-medium text-gray-700">Instructions:</p>
                                            <p class="text-sm text-gray-600">{{ $homework->instructions }}</p>
                                        </div>
                                    @endif
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-3">
                                        <div>
                                            <p><span class="font-medium">Assigned:</span> {{ $homework->created_at->format('M d, Y g:i A') }}</p>
                                            @if($homework->completed_at)
                                                <p><span class="font-medium">Completed:</span> {{ $homework->completed_at->format('M d, Y g:i A') }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            @if($homework->submitted_at)
                                                <p><span class="font-medium">Submitted:</span> {{ $homework->submitted_at->format('M d, Y g:i A') }}</p>
                                            @endif
                                            @if($homework->feedback_submitted_at)
                                                <p><span class="font-medium">Feedback Submitted:</span> {{ $homework->feedback_submitted_at->format('M d, Y g:i A') }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Student Feedback -->
                                    @if($homework->student_feedback)
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                            <div class="flex items-start">
                                                <svg class="h-5 w-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-2.126-.306l-3.68 1.226A1 1 0 016 20V9a8 8 0 118 8z"></path>
                                                </svg>
                                                <div class="flex-1">
                                                    <h5 class="font-medium text-yellow-800 mb-1">Student's Feedback & Notes:</h5>
                                                    <p class="text-yellow-700 whitespace-pre-line">{{ $homework->student_feedback }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Session Actions -->
                    <div class="flex flex-wrap gap-4">
                        @if($session->status === 'pending')
                            <form method="POST" action="{{ route('teacher.sessions.confirm', $session) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" onclick="return confirm('Are you sure you want to confirm this session?')">>
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Confirm Session
                                </button>
                            </form>
                        @endif
                        
                        @if($session->status === 'confirmed')
                            <form method="POST" action="{{ route('teacher.sessions.complete', $session) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" onclick="return confirm('Are you sure you want to mark this session as completed?')">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Mark as Completed
                                </button>
                            </form>
                        @endif
                        
                        @if(in_array($session->status, ['confirmed', 'completed']))
                            <a href="{{ route('teacher.sessions.assign-homework', $session) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                Assign Homework
                            </a>
                        @endif

                        {{-- Cancel button for unpaid sessions --}}
                        @if(in_array($session->status, ['booked', 'pending']) && !$session->is_paid && !$session->payment_id)
                            <form method="POST" action="{{ route('teacher.sessions.cancel', $session) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to cancel this session?\n\nThis action will:\n• Cancel the session\n• Notify the student\n• Free up the time slot\n\nThis cannot be undone.')">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel Session
                                </button>
                            </form>
                        @endif
                        
                
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
