@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Chess Sessions</h1>
    </div>
    
    @if($sessions->isEmpty())
        <div class="bg-white shadow-md rounded-lg p-8 text-center">
            <p class="text-gray-500 text-lg">No sessions found.</p>
            
            @if(auth()->check() && auth()->user()->roles && auth()->user()->roles->pluck('name')->contains('student'))
                <a href="{{ route('checkout') }}" class="mt-4 inline-block px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200">
                    Book a New Session
                </a>
            @endif
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
                            Student
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Teacher
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->student_id && $session->student)
                                    <div class="text-sm font-medium text-gray-900">{{ $session->student->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $session->student->email ?? 'N/A' }}</div>
                                @else
                                    <span class="text-sm text-gray-500">Not assigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->teacher_id && $session->teacher)
                                    <div class="text-sm font-medium text-gray-900">{{ $session->teacher->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $session->teacher->email ?? 'N/A' }}</div>
                                @else
                                    <span class="text-sm text-gray-500">Not assigned</span>
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
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if(auth()->check() && auth()->user()->roles && auth()->user()->roles->pluck('name')->contains('teacher') && !$session->teacher_id)
                                    <a href="{{ route('sessions.assign', ['session' => $session->id, 'teacher' => auth()->id()]) }}" class="text-blue-600 hover:text-blue-900">
                                        Accept
                                    </a>
                                @endif
                                
                                @if(auth()->check() && auth()->user()->roles && auth()->user()->roles->pluck('name')->contains('admin'))
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900 ml-2">Edit</a>
                                @endif
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
@endsection
