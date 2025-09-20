<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Session Management') }}
            </h2>
            <a href="{{ route('admin.sessions.export', request()->query()) }}" class="px-4 py-2 bg-primary-800 hover:bg-primary-700 text-white font-bold rounded">
                Export Sessions
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Total Sessions</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total_sessions'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Pending</h3>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_sessions'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Booked</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['confirmed_sessions'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Completed</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['completed_sessions'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Revenue</h3>
                    <p class="text-2xl font-bold text-emerald-600">£{{ number_format($stats['total_revenue'], 2) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.sessions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   placeholder="Student or teacher name/email" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>Booked</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label for="teacher_id" class="block text-sm font-medium text-gray-700">Teacher</label>
                            <select name="teacher_id" id="teacher_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Teachers</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-primary-800 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                            <a href="{{ route('admin.sessions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ms-2">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sessions Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($sessions as $session)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            Session #{{ $session->id }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $session->duration }} min
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($session->student)
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $session->student->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $session->student->email }}
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-500">
                                                No student assigned
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($session->teacher)
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $session->teacher->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $session->teacher->email }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">Not assigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                        @if($session->scheduled_at)
                                            {{ $session->scheduled_at->format('M d, Y') }}
                                        @else
                                            Not scheduled
                                        @endif
                                        </div>
                                        
                                        <div class="text-sm text-gray-500 flex items-center">
                                        @if($session->scheduled_at)
                                            {{ $session->scheduled_at->format('h:i A') }}
                                            @if($session->meeting_link)
                                                <svg class="ml-2 h-4 w-4 text-blue-500" title="Meeting link available" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                                </svg>
                                            @endif
                                        @else
                                            --:--
                                        @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            @if($session->payment && $session->payment->amount)
                                                £{{ number_format($session->payment->amount, 2) }}
                                            @else
                                                £{{ number_format($session->amount ?? 0, 2) }}
                                            @endif
                                        </div>
                                        @if($session->payment)
                                            <div class="text-sm text-green-600">
                                                Paid
                                            </div>
                                        @else
                                            <div class="text-sm text-red-600">
                                                Not paid
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($session->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($session->status == 'booked') bg-blue-100 text-blue-800
                                            @elseif($session->status == 'completed') bg-green-100 text-green-800
                                            @elseif($session->status == 'cancelled') bg-red-100 text-red-800
                                            @endif">
                                            @if($session->status == 'booked')
                                                Booked
                                            @else
                                                {{ ucfirst($session->status) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.sessions.show', $session) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                        
                                        {{-- @if($session->status != 'completed' && $session->status != 'cancelled')
                                            <button onclick="openStatusModal({{ $session->id }}, '{{ $session->status }}')" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                Update Status
                                            </button>
                                        @endif --}}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No sessions found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $sessions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">Update Session Status</h3>
                <form id="statusForm" method="POST" class="mt-4">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="status_select" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status_select" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="pending">Pending</option>
                            <option value="booked">Booked</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700">Reason (Optional)</label>
                        <textarea name="reason" id="reason" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Enter reason for status change..."></textarea>
                    </div>
                    <div class="flex justify-between">
                        <button type="button" onclick="closeStatusModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openStatusModal(sessionId, currentStatus) {
            document.getElementById('statusForm').action = `/admin/sessions/${sessionId}/status`;
            document.getElementById('status_select').value = currentStatus;
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
