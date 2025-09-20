<?php
// This file contains the session time selection view where teachers select from student's preferred time slots

?>
<x-guest-layout>
    <div class="py-2">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden rounded-lg p-3">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h1 class="text-2xl font-semibold text-gray-800">Select Session Time</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Please select a time slot from the student's suggested availability.
                    </p>
                </div>
                
                {{-- <div class="px-4 py-5 sm:px-6"> --}}
                    <!-- Session Details -->
                    <div class="mb-6">
                        <h2 class="text-lg font-medium text-gray-700 mb-2">Session Details</h2>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Session Type:</span> {{ ucfirst($session->session_type) }}<br>
                                <span class="font-medium">Duration:</span> {{ $session->duration }} minutes<br>
                                <span class="font-medium">Session Name:</span> {{ $session->session_name }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Student Information -->
                    <div class="mb-6">
                        <h2 class="text-lg font-medium text-gray-700 mb-2">Student Information</h2>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Name:</span> {{ $student->name }}<br>
                                <span class="font-medium">Email:</span> {{ $student->email }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Time Selection Form -->
                    <div class="mt-6">
                        <h2 class="text-lg font-medium text-gray-700 mb-4">Student's Suggested Availability</h2>
                        
                        @if($suggestedAvailability && count($suggestedAvailability))
                            <form action="{{ $confirmUrl }}" method="POST" class="space-y-4">
                                @csrf
                                
                                @if ($errors->any())
                                    <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                                                <div class="mt-2 text-sm text-red-700">
                                                    <ul class="list-disc pl-5 space-y-1">
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="space-y-4">
                                    @foreach($suggestedAvailability as $index => $dateOption)
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <h3 class="font-medium text-gray-700 mb-3">
                                                {{ date('l, F j, Y', strtotime($dateOption['date'])) }}
                                            </h3>
                                            
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                                @foreach($dateOption['times'] as $time)
                                                    <label class="bg-white border border-gray-300 rounded-md px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-blue-50 transition-colors">
                                                        <div class="flex items-center">
                                                            <input type="radio" name="selected_time" 
                                                                value="{{ $dateOption['date'] }}|{{ $time }}" 
                                                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                                            <span class="ml-3 text-sm font-medium text-gray-700">
                                                                {{ date('g:i A', strtotime($time)) }}
                                                            </span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Meeting Link Field -->
                                <div class="mt-6">
                                    <label for="meeting_link" class="block text-sm font-medium text-gray-700 mb-2">
                                        Meeting Link <span class="text-red-500">*</span>
                                    </label>
                                    <input type="url" name="meeting_link" id="meeting_link" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="https://zoom.us/j/123456789 or https://meet.google.com/abc-def-ghi">
                                    <p class="mt-1 text-sm text-gray-500">
                                        Provide the meeting link (Zoom, Google Meet, etc.) for the student to join the session.
                                    </p>
                                </div>
                                
                                <div class="mt-6">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                                        Confirm Session Time
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            No suggested availability found. You can still accept this session and arrange the time directly with the student.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <form action="{{ $confirmUrl }}" method="POST">
                                @csrf
                                <input type="hidden" name="no_suggested_times" value="1">
                                
                                @if ($errors->any())
                                    <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                                                <div class="mt-2 text-sm text-red-700">
                                                    <ul class="list-disc pl-5 space-y-1">
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Meeting Link Field -->
                                <div class="mb-6">
                                    <label for="meeting_link_alt" class="block text-sm font-medium text-gray-700 mb-2">
                                        Meeting Link <span class="text-red-500">*</span>
                                    </label>
                                    <input type="url" name="meeting_link" id="meeting_link_alt" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="https://zoom.us/j/123456789 or https://meet.google.com/abc-def-ghi">
                                    <p class="mt-1 text-sm text-gray-500">
                                        Provide the meeting link (Zoom, Google Meet, etc.) for the student to join the session.
                                    </p>
                                </div>
                                
                                <div class="mt-6">
                                    <button type="submit" class="w-full inline-flex justify-center py-3 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Accept Session and Arrange Time Later
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                {{-- </div> --}}
            </div>
        </div>
    </div>
</x-guest-layout>
