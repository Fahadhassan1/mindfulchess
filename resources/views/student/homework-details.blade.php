<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">Homework Details</h1>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('student.sessions') }}" class="text-blue-500 hover:text-blue-700">
                    <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Sessions
                </a>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-purple-50 border-b border-purple-200 px-6 py-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-purple-900">{{ $homework->title }}</h2>
                            <p class="text-purple-700 mt-1">Assigned by {{ $homework->teacher->name }}</p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            @if($homework->status == 'completed') bg-green-100 text-green-800 
                            @elseif($homework->status == 'in_progress') bg-yellow-100 text-yellow-800 
                            @else bg-purple-100 text-purple-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $homework->status)) }}
                        </span>
                    </div>
                </div>

                <div class="p-6">


                    <!-- Session Information -->
                    <div class="bg-gray-50 rounded-md p-4 mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3">Related Session</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="mb-2"><span class="font-medium">Session:</span> {{ $homework->session->session_name }}</p>
                                <p class="mb-2"><span class="font-medium">Type:</span> {{ ucfirst($homework->session->session_type) }}</p>
                            </div>
                            <div>
                                @if($homework->session->scheduled_at)
                                    <p class="mb-2"><span class="font-medium">Date:</span> {{ $homework->session->scheduled_at->format('M d, Y') }}</p>
                                    <p class="mb-2"><span class="font-medium">Time:</span> {{ $homework->session->scheduled_at->format('g:i A') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Homework Details -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3">Assignment Details</h3>
                        <div class="bg-purple-50 rounded-md p-4 border border-purple-200">
                            <div class="mb-4">
                                <h4 class="font-medium text-purple-900 mb-2">Description:</h4>
                                <p class="text-purple-800">{{ $homework->description }}</p>
                            </div>

                            @if($homework->instructions)
                                <div class="mb-4">
                                    <h4 class="font-medium text-purple-900 mb-2">Instructions:</h4>
                                    <p class="text-purple-800">{{ $homework->instructions }}</p>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-purple-700">
                                <div>
                                    <p><span class="font-medium">Assigned:</span> {{ $homework->created_at->format('M d, Y g:i A') }}</p>
                                    <p><span class="font-medium">Teacher:</span> {{ $homework->teacher->name }}</p>
                                </div>
                                <div>
                                    <p><span class="font-medium">Status:</span> {{ ucfirst(str_replace('_', ' ', $homework->status)) }}</p>
                                    @if($homework->updated_at != $homework->created_at)
                                        <p><span class="font-medium">Last Updated:</span> {{ $homework->updated_at->format('M d, Y g:i A') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachment Download -->
                    @if($homework->attachment_path)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Attachment</h3>
                            <div class="bg-gray-50 rounded-md p-4 border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="h-8 w-8 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Homework Attachment</p>
                                            <p class="text-sm text-gray-500">Click to download the file</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('student.homework.download', $homework) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Status Update -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3">Update Status</h3>
                        <form method="POST" action="{{ route('student.homework.update-status', $homework) }}" class="bg-gray-50 rounded-md p-4">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Homework Status</label>
                                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                    <option value="assigned" {{ $homework->status == 'assigned' ? 'selected' : '' }}>Not Started</option>
                                    <option value="in_progress" {{ $homework->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $homework->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Update Status
                            </button>
                        </form>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <a href="{{ route('student.sessions.show', $homework->session) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            ← View Session Details
                        </a>
                        
                    
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
