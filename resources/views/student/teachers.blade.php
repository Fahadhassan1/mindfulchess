<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Teachers') }}
            </h2>
            @if($teacher)
                <a href="{{ route('student.booking.calendar') }}" class="bg-primary-800 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                    Book Session
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($teacher)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Your Assigned Teacher</h3>
                            
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col md:flex-row gap-6">
                                <div class="md:w-1/4">
                                    @if($teacher->teacherProfile && $teacher->teacherProfile->profile_image)
                                        <img src="{{ asset('storage/profile_images/' . $teacher->teacherProfile->profile_image) }}" 
                                            alt="{{ $teacher->name }}" class="w-full h-auto object-cover rounded-lg">
                                    @else
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center rounded-lg">
                                            <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="md:w-3/4">
                                    <h4 class="text-xl font-bold">{{ $teacher->name }}</h4>
                                    {{-- <p class="text-gray-600">{{ $teacher->email }}</p> --}}
                                    
                                    @if($teacher->teacherProfile)
                                        <div class="mt-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                                
                                                @if($teacher->teacherProfile->teaching_type)
                                                <div>
                                                    <p class="text-gray-500 text-sm">Teaching Focus</p>
                                                    <p class="font-medium">{{ ucfirst($teacher->teacherProfile->teaching_type) }}</p>
                                                </div>
                                                @endif
                                                
                                                @if($teacher->teacherProfile->experience_years)
                                                <div>
                                                    <p class="text-gray-500 text-sm">Years of Experience</p>
                                                    <p class="font-medium">{{ $teacher->teacherProfile->experience_years }}</p>
                                                </div>
                                                @endif
                                                
                                                @if($teacher->teacherProfile->specialties)
                                                <div>
                                                    <p class="text-gray-500 text-sm">Specialties</p>
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        @foreach($teacher->teacherProfile->specialties as $specialty)
                                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">{{ $specialty }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            
                                            @if($teacher->teacherProfile->bio)
                                            <div class="mt-4">
                                                <p class="text-gray-500 text-sm">Bio</p>
                                                <p class="mt-1">{{ $teacher->teacherProfile->bio }}</p>
                                            </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Teacher Availability -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Teacher Availability</h3>
                            
                            @if($teacher->availability && $teacher->availability->count() > 0)
                                @php
                                    $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
                                    $availabilityByDay = $teacher->availability->groupBy('day_of_week');
                                @endphp
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach($days as $dayKey => $dayName)
                                        <div class="border rounded-lg p-4 {{ isset($availabilityByDay[$dayKey]) ? 'bg-white' : 'bg-gray-50' }}">
                                            <h4 class="font-semibold text-lg mb-2">{{ $dayName }}</h4>
                                            
                                            @if(isset($availabilityByDay[$dayKey]))
                                                <ul class="space-y-1">
                                                @foreach($availabilityByDay[$dayKey] as $slot)
                                                    @if($slot->is_available)
                                                        <li class="text-sm">
                                                            <span class="text-green-600">●</span> 
                                                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $slot->start_time)->format('g:i A') }} - 
                                                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $slot->end_time)->format('g:i A') }}
                                                        </li>
                                                    @endif
                                                @endforeach
                                                </ul>
                                            @else
                                                <p class="text-sm text-gray-500">No availability</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded">
                                    <p>Your teacher hasn't set their availability yet. Please check back later.</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded">
                            <p>You don't have an assigned teacher yet. Please contact the administrator for more information.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
