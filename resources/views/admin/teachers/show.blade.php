<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Teacher Profile') }}: {{ $teacher->name }}
            </h2>
            <a href="{{ route('admin.teachers.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                Back to Teachers
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <div class="bg-gray-100 p-4 rounded-lg text-center">
                                @if($teacher->teacherProfile && $teacher->teacherProfile->profile_image)
                                    <img src="{{ asset('storage/profile_images/' . $teacher->teacherProfile->profile_image) }}" 
                                         alt="{{ $teacher->name }}" class="mx-auto h-48 w-48 object-cover rounded-full">
                                @else
                                    <div class="mx-auto h-48 w-48 rounded-full bg-gray-300 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                                <h3 class="text-xl font-bold mt-4">{{ $teacher->name }}</h3>
                                <p class="text-gray-500">{{ $teacher->email }}</p>
                                
                                <div class="mt-4 text-left">

                                    <p class="text-sm font-semibold mt-2">Teaching Type:</p>
                                    <p>{{ $teacher->teacherProfile && $teacher->teacherProfile->teaching_type ? ucfirst($teacher->teacherProfile->teaching_type) : 'Not specified' }}</p>
                                    
                                    <!-- Experience field has been removed -->
                                    
                                    <p class="text-sm font-semibold mt-2">Stripe Account:</p>
                                    <p class="text-xs break-all">{{ $teacher->teacherProfile->stripe_account_id ?? 'Not connected' }}</p>
                                    
                                    <p class="text-sm font-semibold mt-2">Status:</p>
                                    <div class="flex items-center">
                                        @if(!$teacher->teacherProfile)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                No Profile
                                            </span>
                                        @elseif($teacher->teacherProfile->is_active)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                        @endif
                                        <span class="ml-2 text-xs text-gray-500">
                                            {{ $teacher->teacherProfile && $teacher->teacherProfile->is_active ? 'Receiving notifications' : 'Notifications paused' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 space-y-3">
                                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded block text-center">
                                    Edit Profile
                                </a>
                                
                                <form action="{{ route('admin.teachers.toggle-active', $teacher) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="w-full bg-{{ $teacher->teacherProfile && !$teacher->teacherProfile->is_active ? 'green' : 'red' }}-500 hover:bg-{{ $teacher->teacherProfile && !$teacher->teacherProfile->is_active ? 'green' : 'red' }}-700 text-white font-bold py-2 px-4 rounded">
                                        {{ $teacher->teacherProfile && $teacher->teacherProfile->is_active ? 'Deactivate (Pause Notifications)' : 'Activate (Resume Notifications)' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="md:col-span-2">
                            <div class="bg-white rounded-lg">
                                <h3 class="text-xl font-bold mb-4 pb-2 border-b">Teacher Information</h3>
                                
                                <div class="mb-6">
                                    <h4 class="text-lg font-semibold mb-2">Bio</h4>
                                    <div class="bg-gray-50 p-4 rounded">
                                        {{ $teacher->teacherProfile->bio ?? 'No bio available.' }}
                                    </div>
                                </div>
                                
                                <!-- Specialties section has been removed -->
                                
                                <!-- Teacher Availability -->
                                <div class="mb-6">
                                    <h4 class="text-lg font-semibold mb-2">Teaching Availability</h4>
                                    <div class="bg-gray-50 p-4 rounded">
                                        @if($teacher->availability && $teacher->availability->count() > 0)
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                    <div class="border rounded-md p-3 bg-white">
                                                        <h5 class="font-semibold text-gray-700 mb-2 capitalize text-sm">{{ $day }}</h5>
                                                        
                                                        @if($groupedAvailability[$day]->count() > 0)
                                                            <ul class="space-y-1">
                                                                @foreach($groupedAvailability[$day] as $slot)
                                                                    <li class="flex justify-between items-center bg-green-50 p-2 rounded border border-green-200">
                                                                        <span class="text-sm text-green-800">
                                                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - 
                                                                            {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                                                        </span>
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                            Available
                                                                        </span>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <p class="text-xs text-gray-500">No availability</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            
                                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                    </svg>
                                                    <div>
                                                        <p class="text-sm font-medium text-blue-800">Availability Summary</p>
                                                        <p class="text-xs text-blue-600">
                                                            Total slots: {{ $teacher->availability->count() }} | 
                                                            Days available: {{ $groupedAvailability->filter(fn($slots) => $slots->count() > 0)->count() }}/7
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center py-8">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="text-gray-500 text-sm">No availability schedule set up yet.</p>
                                                <p class="text-xs text-gray-400 mt-1">Teacher needs to set their availability in their profile.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Additional sections could be added here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
