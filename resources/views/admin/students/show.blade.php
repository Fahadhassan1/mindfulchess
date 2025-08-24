<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Profile') }}: {{ $student->name }}
            </h2>
            <div>
                <a href="{{ route('admin.students.edit', $student) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2">
                    Edit Profile
                </a>
                <a href="{{ route('admin.students.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Back to Students
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Profile Image and Basic Info -->
                        <div class="md:col-span-1">
                            <div class="mb-6 text-center">
                                @if($student->studentProfile && $student->studentProfile->profile_image)
                                    <img src="{{ asset('storage/profile_images/' . $student->studentProfile->profile_image) }}" 
                                        alt="{{ $student->name }}'s profile image" 
                                        class="h-48 w-48 object-cover rounded-full mx-auto mb-4">
                                @else
                                    <div class="h-48 w-48 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-4">
                                        <span class="text-gray-500 text-4xl">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <h3 class="text-xl font-semibold">{{ $student->name }}</h3>
                                <p class="text-gray-600">{{ $student->email }}</p>
                                
                                @if($student->studentProfile && $student->studentProfile->level)
                                    <div class="mt-2">
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                        @if($student->studentProfile->level == 'beginner') bg-green-100 text-green-800
                                        @elseif($student->studentProfile->level == 'intermediate') bg-blue-100 text-blue-800
                                        @else bg-purple-100 text-purple-800 @endif">
                                            {{ ucfirst($student->studentProfile->level) }} Level
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Student Details -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-medium mb-4 border-b pb-2">Student Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Age</p>
                                    <p class="font-medium">{{ $student->studentProfile->age ?? 'Not specified' }}</p>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-600">School</p>
                                    <p class="font-medium">{{ $student->studentProfile->school ?? 'Not specified' }}</p>
                                </div>

                                <div class="md:col-span-2 mt-4">
                                    <p class="text-sm text-gray-600">Learning Goals</p>
                                    <p class="font-medium">
                                        {{ $student->studentProfile->learning_goals ?? 'No learning goals specified' }}
                                    </p>
                                </div>
                            </div>

                            <h3 class="text-lg font-medium mt-8 mb-4 border-b pb-2">Parent/Guardian Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Parent/Guardian Name</p>
                                    <p class="font-medium">{{ $student->studentProfile->parent_name ?? 'Not specified' }}</p>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-600">Parent/Guardian Email</p>
                                    <p class="font-medium">{{ $student->studentProfile->parent_email ?? 'Not specified' }}</p>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-600">Parent/Guardian Phone</p>
                                    <p class="font-medium">{{ $student->studentProfile->parent_phone ?? 'Not specified' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
