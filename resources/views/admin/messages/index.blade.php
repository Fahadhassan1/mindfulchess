<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Message Moderation') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.messages.export') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    Export Flagged
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">💬</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Messages</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">⚠️</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Flagged Messages</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['flagged']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">⏳</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pending Review</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['pending_review']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">🔒</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Hidden Messages</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['hidden']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.messages.index') }}" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-64">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}"
                                placeholder="Search messages, users..."
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <select name="flagged" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Messages</option>
                                <option value="true" {{ request('flagged') === 'true' ? 'selected' : '' }}>Flagged Only</option>
                            </select>
                        </div>

                        <div>
                            <select name="status" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="all">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="hidden" {{ request('status') === 'hidden' ? 'selected' : '' }}>Hidden</option>
                                <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                            </select>
                        </div>

                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                            Filter
                        </button>

                        @if(request()->hasAny(['search', 'flagged', 'status']))
                            <a href="{{ route('admin.messages.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-400">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Messages Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($messages->count() > 0)
                        <!-- Bulk Actions -->
                        <div class="mb-4 flex items-center space-x-4">
                            <input type="checkbox" id="select-all" class="rounded">
                            <label for="select-all" class="text-sm text-gray-600">Select All</label>
                            
                            <div class="hidden" id="bulk-actions">
                                <select id="bulk-action" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                                    <option value="">Bulk Actions</option>
                                    <option value="approve">Approve Selected</option>
                                    <option value="hide">Hide Selected</option>
                                    <option value="delete">Delete Selected</option>
                                </select>
                                <button id="apply-bulk" class="ml-2 px-3 py-1 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                                    Apply
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <input type="checkbox" id="header-select-all" class="rounded">
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participants</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($messages as $message)
                                        <tr class="hover:bg-gray-50 {{ $message->is_flagged ? 'bg-yellow-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="checkbox" name="message_ids[]" value="{{ $message->id }}" class="message-checkbox rounded">
                                            </td>
                                            
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    @if($message->is_flagged)
                                                        <div class="flex items-center mb-1">
                                                            <span class="text-yellow-500 mr-2">⚠️</span>
                                                            <span class="text-xs text-yellow-700 bg-yellow-100 px-2 py-1 rounded">
                                                                Flagged: {{ implode(', ', $message->flagged_reasons ?? []) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    
                                                    <p class="truncate max-w-md">{{ Str::limit($message->content, 100) }}</p>
                                                    
                                                    @if($message->original_content && $message->original_content !== $message->content)
                                                        <details class="mt-2">
                                                            <summary class="text-xs text-gray-500 cursor-pointer">Original content (before filtering)</summary>
                                                            <p class="text-xs text-gray-600 mt-1 bg-gray-100 p-2 rounded">
                                                                {{ Str::limit($message->original_content, 150) }}
                                                            </p>
                                                        </details>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm">
                                                    <div class="text-gray-900">
                                                        <strong>From:</strong> {{ $message->sender->name }}
                                                        <span class="text-gray-500">({{ $message->sender->getRoleNames()->first() }})</span>
                                                    </div>
                                                    <div class="text-gray-900">
                                                        <strong>To:</strong> {{ $message->recipient->name }}
                                                        <span class="text-gray-500">({{ $message->recipient->getRoleNames()->first() }})</span>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                    {{ $message->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $message->status === 'hidden' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $message->status === 'deleted' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                    {{ ucfirst($message->status) }}
                                                </span>
                                                
                                                @if($message->is_flagged && !$message->moderated_at)
                                                    <span class="block mt-1 text-xs text-orange-600">Needs Review</span>
                                                @endif
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $message->created_at->format('M j, Y g:i A') }}
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                                <a href="{{ route('admin.messages.show', $message) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">View</a>
                                                
                                                @if($message->is_flagged && !$message->moderated_at)
                                                    <button onclick="quickModerate({{ $message->id }}, 'approve')" 
                                                            class="text-green-600 hover:text-green-900">Approve</button>
                                                    <button onclick="quickModerate({{ $message->id }}, 'hide')" 
                                                            class="text-red-600 hover:text-red-900">Hide</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $messages->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500">No messages found matching your criteria.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Quick moderation
        function quickModerate(messageId, action) {
            if (!confirm(`Are you sure you want to ${action} this message?`)) {
                return;
            }

            fetch(`/admin/messages/${messageId}/moderate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    notes: `Quick ${action} via admin panel`
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to moderate message'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to moderate message');
            });
        }

        // Bulk actions functionality
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const headerSelectAll = document.getElementById('header-select-all');
            const messageCheckboxes = document.querySelectorAll('.message-checkbox');
            const bulkActions = document.getElementById('bulk-actions');
            const bulkActionSelect = document.getElementById('bulk-action');
            const applyBulkButton = document.getElementById('apply-bulk');

            function updateSelectAllState() {
                const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
                const allChecked = checkedBoxes.length === messageCheckboxes.length && messageCheckboxes.length > 0;
                const someChecked = checkedBoxes.length > 0;

                selectAll.checked = allChecked;
                headerSelectAll.checked = allChecked;
                selectAll.indeterminate = someChecked && !allChecked;
                headerSelectAll.indeterminate = someChecked && !allChecked;

                if (someChecked) {
                    bulkActions.classList.remove('hidden');
                } else {
                    bulkActions.classList.add('hidden');
                }
            }

            function toggleAll(checked) {
                messageCheckboxes.forEach(checkbox => {
                    checkbox.checked = checked;
                });
                updateSelectAllState();
            }

            selectAll.addEventListener('change', (e) => toggleAll(e.target.checked));
            headerSelectAll.addEventListener('change', (e) => toggleAll(e.target.checked));

            messageCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectAllState);
            });

            applyBulkButton.addEventListener('click', function() {
                const action = bulkActionSelect.value;
                const checkedIds = Array.from(document.querySelectorAll('.message-checkbox:checked'))
                    .map(cb => cb.value);

                if (!action) {
                    alert('Please select an action');
                    return;
                }

                if (checkedIds.length === 0) {
                    alert('Please select at least one message');
                    return;
                }

                if (!confirm(`Are you sure you want to ${action} ${checkedIds.length} message(s)?`)) {
                    return;
                }

                fetch('/admin/messages/bulk-moderate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message_ids: checkedIds,
                        action: action,
                        notes: `Bulk ${action} via admin panel`
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to process bulk action'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to process bulk action');
                });
            });

            updateSelectAllState();
        });
    </script>
</x-app-layout>
