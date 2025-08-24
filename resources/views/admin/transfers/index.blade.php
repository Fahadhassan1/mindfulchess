<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Transfer Management') }}
            </h2>
            <div class="flex space-x-2">
                @if($stats['pending_transfers'] > 0)
                    <form method="POST" action="{{ route('admin.transfers.process-pending') }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to process all pending transfers?')" 
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Process Pending ({{ $stats['pending_transfers'] }})
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.transfers.export', request()->query()) }}" class="px-4 py-2 bg-primary-800 hover:bg-primary-700 text-white font-bold rounded">
                    Export Transfers
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Total Transfers</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['total_transfers'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Total Transferred</h3>
                    <p class="text-2xl font-bold text-emerald-600">£{{ number_format($stats['total_transferred'], 2) }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Pending</h3>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_transfers'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-700">Fees Collected</h3>
                    <p class="text-2xl font-bold text-purple-600">£{{ number_format($stats['total_fees_collected'], 2) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.transfers.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   placeholder="Teacher name or transfer ID" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
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
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-primary-800 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                            <a href="{{ route('admin.transfers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ms-2">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Transfers Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fees</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($transfers as $transfer)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            #{{ $transfer->id }}
                                        </div>
                                        @if($transfer->stripe_transfer_id)
                                            <div class="text-xs text-gray-500 font-mono">
                                                {{ substr($transfer->stripe_transfer_id, 0, 20) }}...
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $transfer->teacher->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $transfer->teacher->email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($transfer->session)
                                            <div class="text-sm text-gray-900">
                                                Session #{{ $transfer->session->id }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $transfer->session->student->name }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">No session</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            £{{ number_format($transfer->amount, 2) }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Total: £{{ number_format($transfer->total_session_amount * 100, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            £{{ number_format($transfer->application_fee, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($transfer->status == 'completed') bg-green-100 text-green-800
                                            @elseif($transfer->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($transfer->status == 'failed') bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($transfer->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($transfer->transferred_at)
                                            <div class="text-sm text-gray-900">
                                                {{ $transfer->transferred_at->format('M d, Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $transfer->transferred_at->format('h:i A') }}
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-900">
                                                {{ $transfer->created_at->format('M d, Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Created
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.transfers.show', $transfer) }}" class="text-green-600 hover:text-green-900 mr-3">View</a>
                                        <a href="{{ route('admin.transfers.invoice', $transfer) }}" class="text-green-600 hover:text-green-900 mr-3" target="_blank">Invoice</a>
                                        
                                        @if($transfer->status == 'failed')
                                            <form method="POST" action="{{ route('admin.transfers.retry', $transfer) }}" class="inline">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Are you sure you want to retry this transfer?')" 
                                                        class="text-blue-600 hover:text-blue-900">
                                                    Retry
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        No transfers found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $transfers->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
