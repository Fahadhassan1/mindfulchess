<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Messages') }}
            </h2>
            <div class="text-sm text-gray-600">
                Secure communication with your {{ auth()->user()->hasRole('teacher') ? 'students' : 'teacher' }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($conversations->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.013 8.013 0 01-7-4c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No conversations yet</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(auth()->user()->hasRole('student'))
                                    Your teacher will be able to start a conversation with you.
                                @else
                                    Start a conversation with one of your assigned students.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($conversations as $otherUserId => $lastMessage)
                                @php
                                    $otherUser = $lastMessage->sender_id == auth()->id() ? $lastMessage->recipient : $lastMessage->sender;
                                    $unreadCount = $unreadCounts[$otherUserId] ?? 0;
                                @endphp
                                
                                <a href="{{ route('messages.conversation', $otherUser) }}" 
                                   class="block hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                                                <span class="text-sm font-medium text-white">
                                                    {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="ml-4 flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $otherUser->name }}
                                                    <span class="text-xs text-gray-500 ml-2">
                                                        ({{ ucfirst($otherUser->getRoleNames()->first()) }})
                                                    </span>
                                                </p>
                                                
                                                @if($unreadCount > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        {{ $unreadCount }} new
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="flex items-center justify-between mt-1">
                                                <p class="text-sm text-gray-500 truncate">
                                                    @if($lastMessage->is_flagged && $lastMessage->sender_id == auth()->id())
                                                        <span class="text-orange-600">⚠️ Message under review:</span>
                                                    @endif
                                                    
                                                    @if($lastMessage->sender_id == auth()->id())
                                                        You: 
                                                    @endif
                                                    
                                                    {{ Str::limit($lastMessage->content, 50) }}
                                                </p>
                                                
                                                <p class="text-xs text-gray-400">
                                                    {{ $lastMessage->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="ml-2 flex-shrink-0">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
