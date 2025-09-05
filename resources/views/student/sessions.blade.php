<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">My Chess Sessions</h1>
            <div class="space-x-3">
                <a href="{{ route('student.booking.calendar') }}" class="bg-primary-800 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                    Book Additional Session
                </a>
                {{-- <a href="{{ route('checkout') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-200">
                    Book New Session
                </a> --}}
            </div>
        </div>
    </x-slot>
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    
                    @if($sessions->isEmpty())
                        <div class="bg-white shadow-md rounded-lg p-8 text-center">
                            <p class="text-gray-500 text-lg">You haven't booked any sessions yet.</p>
                            <a href="{{ route('checkout') }}" class="mt-4 inline-block px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200">
                                Book Your First Session
                            </a>
                        </div>
                    @else
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Session Details
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Teacher
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($sessions as $session)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $session->session_name }}</div>
                                <div class="text-sm text-gray-500">Type: {{ ucfirst($session->session_type) }}</div>
                                <div class="text-sm text-gray-500">Duration: {{ $session->duration }} minutes</div>
                                @if($session->scheduled_at)
                                    <div class="text-sm text-gray-500">
                                        Scheduled: {{ $session->scheduled_at->format('M d, Y H:i') }}
                                    </div>
                                @endif
                                <div class="text-sm text-gray-500">
                                    Booked: {{ $session->created_at->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->teacher)
                                    <div class="text-sm font-medium text-gray-900">{{ $session->teacher->name }}</div>
                                    {{-- <div class="text-sm text-gray-500">{{ $session->teacher->email }}</div> --}}
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Not Assigned
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($session->status == 'completed') bg-green-100 text-green-800 
                                    @elseif($session->status == 'booked') bg-blue-100 text-blue-800 
                                    @elseif($session->status == 'canceled') bg-red-100 text-red-800 
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->payment)
                                    <div class="text-sm font-medium text-gray-900">£{{ number_format($session->payment->amount, 2) }}</div>
                                    <div class="text-sm text-gray-500">Paid on {{ $session->payment->paid_at->format('M d, Y') }}</div>
                                @else
                                    <span class="text-sm text-gray-500">No payment found</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2 justify-end">
                                    <a href="{{ route('student.sessions.show', $session) }}" class="text-blue-600 hover:text-blue-900">
                                        View Details
                                    </a>
                                    
                                    @if($session->homework && $session->homework->count() > 0)
                                        <a href="{{ route('student.homework.show', $session->homework->first()) }}" class="inline-flex items-center px-2 py-1 border border-purple-300 rounded text-xs font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 hover:text-purple-800 transition-colors duration-200" title="View Homework">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                            </svg>
                                            Homework
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $sessions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
