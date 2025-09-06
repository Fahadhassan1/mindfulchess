<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.messages.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Message Details
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Message Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Message Information</h3>
                            <p class="text-sm text-gray-500">ID: {{ $message->id }}</p>
                        </div>
                        
                        @if($message->is_flagged)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                ⚠️ Flagged for Review
                            </span>
                        @endif
                    </div>

                    <!-- Participants -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Sender</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="font-medium">{{ $message->sender->name }}</p>
                                <p class="text-sm text-gray-600">{{ $message->sender->email }}</p>
                                <p class="text-sm text-gray-600">Role: {{ ucfirst($message->sender->getRoleNames()->first()) }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Recipient</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="font-medium">{{ $message->recipient->name }}</p>
                                <p class="text-sm text-gray-600">{{ $message->recipient->email }}</p>
                                <p class="text-sm text-gray-600">Role: {{ ucfirst($message->recipient->getRoleNames()->first()) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Current Message Content</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="whitespace-pre-wrap">{{ $message->content }}</p>
                        </div>
                    </div>

                    @if($message->original_content && $message->original_content !== $message->content)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Original Content (Before Filtering)</h4>
                            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                                <p class="whitespace-pre-wrap text-red-900">{{ $message->original_content }}</p>
                            </div>
                        </div>
                    @endif

                    @if($message->is_flagged && $message->flagged_reasons)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Flagged Reasons</h4>
                            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($message->flagged_reasons as $reason)
                                        <li class="text-yellow-900">{{ $reason }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Message Metadata -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Sent</h4>
                            <p class="text-sm">{{ $message->created_at->format('F j, Y \a\t g:i A') }}</p>
                            <p class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</p>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Status</h4>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $message->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $message->status === 'hidden' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $message->status === 'deleted' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst($message->status) }}
                            </span>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Read Status</h4>
                            <p class="text-sm">
                                @if($message->is_read)
                                    <span class="text-green-600">✓ Read</span>
                                    @if($message->read_at)
                                        <br><span class="text-xs text-gray-500">{{ $message->read_at->format('M j, g:i A') }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-500">Unread</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($message->moderated_at)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Moderation Information</h4>
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <p class="text-sm"><strong>Moderated by:</strong> {{ $message->moderator->name ?? 'Unknown' }}</p>
                                <p class="text-sm"><strong>Date:</strong> {{ $message->moderated_at->format('F j, Y \a\t g:i A') }}</p>
                                @if($message->moderation_notes)
                                    <p class="text-sm mt-2"><strong>Notes:</strong></p>
                                    <p class="text-sm text-gray-700">{{ $message->moderation_notes }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Moderation Actions -->
                    @if($message->is_flagged && !$message->moderated_at)
                        <div class="border-t pt-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-4">Moderation Actions</h4>
                            <form action="{{ route('admin.messages.moderate', $message) }}" method="POST" id="moderation-form">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Action</label>
                                        <div class="mt-2 space-y-2">
                                            <label class="flex items-center">
                                                <input type="radio" name="action" value="approve" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                                <span class="ml-2 text-sm text-gray-700">Approve (remove flag and allow message)</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="action" value="hide" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                                <span class="ml-2 text-sm text-gray-700">Hide (keep message but hide from users)</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="action" value="delete" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                                <span class="ml-2 text-sm text-gray-700">Delete (mark as deleted)</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">Moderation Notes</label>
                                        <textarea 
                                            name="notes" 
                                            id="notes" 
                                            rows="3" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            placeholder="Optional notes about this moderation decision..."></textarea>
                                    </div>

                                    <div class="flex space-x-4">
                                        <button 
                                            type="submit" 
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            Apply Moderation
                                        </button>
                                        <a 
                                            href="{{ route('admin.messages.index') }}" 
                                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-400">
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Conversation Context -->
            @if($conversationMessages->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Conversation Context</h3>
                        <div class="space-y-4">
                            @foreach($conversationMessages as $contextMessage)
                                <div class="border-l-4 {{ $contextMessage->is_flagged ? 'border-yellow-400' : 'border-gray-200' }} pl-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium">
                                                {{ $contextMessage->sender->name }} 
                                                <span class="text-gray-500">→</span> 
                                                {{ $contextMessage->recipient->name }}
                                            </p>
                                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($contextMessage->content, 150) }}</p>
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="text-xs text-gray-500">{{ $contextMessage->created_at->format('M j, g:i A') }}</p>
                                            @if($contextMessage->is_flagged)
                                                <span class="text-xs text-yellow-600">⚠️ Flagged</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            <a 
                                href="{{ route('admin.messages.conversation', [$message->sender, $message->recipient]) }}" 
                                class="text-indigo-600 hover:text-indigo-900 text-sm">
                                View Full Conversation →
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.getElementById('moderation-form').addEventListener('submit', function(e) {
            const selectedAction = document.querySelector('input[name="action"]:checked');
            if (!selectedAction) {
                e.preventDefault();
                alert('Please select a moderation action.');
                return;
            }

            const action = selectedAction.value;
            if (!confirm(`Are you sure you want to ${action} this message?`)) {
                e.preventDefault();
            }
        });
    </script>
</x-app-layout>
