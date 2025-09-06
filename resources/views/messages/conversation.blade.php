<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ route('messages.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $user->name }}
                    <span class="text-sm font-normal text-gray-500 ml-2">
                        ({{ ucfirst($user->getRoleNames()->first()) }})
                    </span>
                </h2>
            </div>
            <div class="text-sm text-gray-600">
                🔒 Secure messaging - All communications are monitored for safety
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Messages Container -->
                <div class="flex flex-col h-96 lg:h-[600px]">
                    <!-- Messages Display -->
                    <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">
                        @if($messages->isEmpty())
                            <div class="text-center py-8">
                                <p class="text-gray-500">No messages yet. Start the conversation!</p>
                            </div>
                        @else
                            @foreach($messages as $message)
                                <div class="flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-xs lg:max-w-md">
                                        <div class="flex items-end space-x-2 {{ $message->sender_id == auth()->id() ? 'flex-row-reverse space-x-reverse' : '' }}">
                                            <div class="flex-shrink-0">
                                                <div class="h-8 w-8 rounded-full {{ $message->sender_id == auth()->id() ? 'bg-indigo-500' : 'bg-gray-300' }} flex items-center justify-center">
                                                    <span class="text-xs font-medium {{ $message->sender_id == auth()->id() ? 'text-white' : 'text-gray-700' }}">
                                                        {{ strtoupper(substr($message->sender->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex flex-col">
                                                <div class="px-4 py-2 rounded-lg {{ $message->sender_id == auth()->id() ? 'bg-indigo-500 text-white' : 'bg-white text-gray-900 border border-gray-200' }}">
                                                    @if($message->is_flagged && $message->sender_id == auth()->id())
                                                        <div class="text-xs {{ $message->sender_id == auth()->id() ? 'text-yellow-200' : 'text-orange-600' }} mb-1">
                                                            ⚠️ Message under review
                                                        </div>
                                                    @endif
                                                    
                                                    <p class="text-sm whitespace-pre-wrap">{{ $message->content }}</p>
                                                </div>
                                                
                                                <p class="text-xs text-gray-500 mt-1 {{ $message->sender_id == auth()->id() ? 'text-right' : 'text-left' }}">
                                                    {{ $message->created_at->format('M j, g:i A') }}
                                                    @if($message->sender_id == auth()->id() && $message->is_read)
                                                        <span class="text-green-500">✓</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <!-- Message Input Form -->
                    <div class="border-t border-gray-200 p-4 bg-white">
                        <form id="message-form" action="{{ route('messages.store') }}" method="POST" class="flex space-x-4">
                            @csrf
                            <input type="hidden" name="recipient_id" value="{{ $user->id }}">
                            
                            <div class="flex-1">
                                <textarea 
                                    name="content" 
                                    id="message-input"
                                    rows="2" 
                                    placeholder="Type your message here..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                                    required
                                    maxlength="2000"></textarea>
                                
                                <div class="mt-1 text-xs text-gray-500">
                                    <span id="char-count">0</span>/2000 characters
                                    <span class="ml-2">• Messages are automatically filtered for sensitive information</span>
                                </div>
                            </div>
                            
                            <div class="flex-shrink-0">
                                <button 
                                    type="submit" 
                                    id="send-button"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    
                                    <span id="send-text">Send</span>
                                    <div id="send-spinner" class="hidden ml-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div id="success-message" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div id="error-message" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
            {{ session('error') }}
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageContainer = document.getElementById('messages-container');
            const messageForm = document.getElementById('message-form');
            const messageInput = document.getElementById('message-input');
            const sendButton = document.getElementById('send-button');
            const sendText = document.getElementById('send-text');
            const sendSpinner = document.getElementById('send-spinner');
            const charCount = document.getElementById('char-count');

            // Scroll to bottom of messages
            function scrollToBottom() {
                messageContainer.scrollTop = messageContainer.scrollHeight;
            }
            scrollToBottom();

            // Character counter
            messageInput.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });

            // Submit form with Enter (but not Shift+Enter for new lines)
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    messageForm.dispatchEvent(new Event('submit'));
                }
            });

            // Form submission
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const content = messageInput.value.trim();
                if (!content) return;

                // Disable form
                sendButton.disabled = true;
                sendText.classList.add('hidden');
                sendSpinner.classList.remove('hidden');

                // Send via fetch
                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Add message to container
                        addMessageToContainer(data.message, data.is_flagged);
                        messageInput.value = '';
                        charCount.textContent = '0';
                        scrollToBottom();
                        
                        if (data.is_flagged) {
                            showNotification('Message sent but flagged for review due to sensitive content.', 'warning');
                        }
                    } else {
                        showNotification(data.message || 'Failed to send message', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Failed to send message. Please try again.', 'error');
                })
                .finally(() => {
                    sendButton.disabled = false;
                    sendText.classList.remove('hidden');
                    sendSpinner.classList.add('hidden');
                });
            });

            function addMessageToContainer(message, isFlagged) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'flex justify-end';
                messageDiv.innerHTML = `
                    <div class="max-w-xs lg:max-w-md">
                        <div class="flex items-end space-x-2 flex-row-reverse space-x-reverse">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                    <span class="text-xs font-medium text-white">
                                        ${message.sender.name.charAt(0).toUpperCase()}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <div class="px-4 py-2 rounded-lg bg-indigo-500 text-white">
                                    ${isFlagged ? '<div class="text-xs text-yellow-200 mb-1">⚠️ Message under review</div>' : ''}
                                    <p class="text-sm whitespace-pre-wrap">${message.content}</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 text-right">
                                    Just now
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                messageContainer.appendChild(messageDiv);
            }

            function showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 px-4 py-3 rounded z-50 ${
                    type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
                    type === 'warning' ? 'bg-yellow-100 border border-yellow-400 text-yellow-700' :
                    'bg-green-100 border border-green-400 text-green-700'
                }`;
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 5000);
            }

            // Auto-hide success/error messages
            const successMessage = document.getElementById('success-message');
            const errorMessage = document.getElementById('error-message');
            
            if (successMessage) {
                setTimeout(() => successMessage.remove(), 5000);
            }
            if (errorMessage) {
                setTimeout(() => errorMessage.remove(), 5000);
            }
        });
    </script>
</x-app-layout>
