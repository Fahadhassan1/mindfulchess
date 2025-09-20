<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Assign Homework
            </h2>
            <a href="{{ route('teacher.sessions.show', $session) }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                Back to Session
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Session Information -->
                    <div class="bg-green-50 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Session Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Session:</span>
                                <p class="text-gray-900">{{ $session->session_name ?? 'Chess Session' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Student:</span>
                                <p class="text-gray-900">{{ $session->student->name ?? 'Student' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Date:</span>
                                <p class="text-gray-900">{{ $session->scheduled_at ? $session->scheduled_at->format('M d, Y \a\t g:i A') : 'Not scheduled' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Duration:</span>
                                <p class="text-gray-900">{{ $session->duration }} minutes</p>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Homework -->
                    @if($session->homework->isNotEmpty())
                        <div class="bg-yellow-50 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Previously Assigned Homework</h3>
                            <div class="space-y-3">
                                @foreach($session->homework as $existingHomework)
                                    <div class="border border-yellow-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-medium text-gray-900">{{ $existingHomework->title }}</h4>
                                                <p class="text-sm text-gray-500 mt-1">{{ $existingHomework->description }}</p>
                                                @if($existingHomework->due_date)
                                                    <p class="text-xs text-gray-400 mt-1">Due: {{ $existingHomework->due_date->format('M d, Y') }}</p>
                                                @endif
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $existingHomework->status_color }}">
                                                {{ ucfirst($existingHomework->status) }}
                                            </span>
                                          

                                        </div>
                                        <!-- Student Feedback -->
                                        @if($existingHomework->student_feedback)
                                            <div class="bg-primary-50 border border-yellow-200 rounded-md p-3 mt-4">
                                                <div class="flex items-start">
                                                    <svg class="h-5 w-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-2.126-.306l-3.68 1.226A1 1 0 016 20V9a8 8 0 118 8z"></path>
                                                </svg>
                                                    <div>
                                                        <p class="text-sm text-yellow-800"><span class="font-medium">Student Feedback:</span> {{ $existingHomework->student_feedback }}</p>
                                                        @if($existingHomework->feedback_submitted_at)
                                                            <p class="text-xs text-yellow-600 mt-1">Submitted on {{ $existingHomework->feedback_submitted_at->format('M d, Y \a\t g:i A') }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Homework Assignment Form -->
                    <form method="POST" action="{{ route('teacher.sessions.store-homework', $session) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Homework Title *</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('title') border-red-500 @enderror"
                                   placeholder="e.g., Chess Tactics Practice">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                            <textarea name="description" id="description" rows="4" required 
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                                      placeholder="Describe what the student needs to work on...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="instructions" class="block text-sm font-medium text-gray-700">Detailed Instructions</label>
                            <textarea name="instructions" id="instructions" rows="6" 
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('instructions') border-red-500 @enderror"
                                      placeholder="Provide step-by-step instructions for the homework...">{{ old('instructions') }}</textarea>
                            @error('instructions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('due_date') border-red-500 @enderror">
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="attachment" class="block text-sm font-medium text-gray-700">Attachment (Optional)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="attachment" class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-green-500">
                                            <span>Upload a file</span>
                                            <input id="attachment" name="attachment" type="file" class="sr-only" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, PDF, DOC up to 10MB</p>
                                </div>
                            </div>
                            @error('attachment')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('teacher.sessions.show', $session) }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Assign Homework & Send Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
