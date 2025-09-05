<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Student Profile') }}
            </h2>
            <div>
                <a href="{{ route('admin.students.show', $student) }}" class="bg-primary-800 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                    View Profile
                </a>


            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
         

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <!-- Profile Image -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="profile_image">
                                        Profile Image
                                    </label>
                                    
                                    @if($profile && $profile->profile_image)
                                        <div class="mb-3">
                                            <img src="{{ asset('storage/profile_images/' . $profile->profile_image) }}" 
                                                alt="Current profile image" class="h-40 w-40 object-cover rounded">
                                        </div>
                                    @endif

                                    <input 
                                        type="file" 
                                        name="profile_image" 
                                        id="profile_image" 
                                        class="block w-full text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                                    >
                                    <p class="text-gray-500 text-xs mt-1">Upload a new image (optional)</p>
                                    
                                    @error('profile_image')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Age -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="age">
                                        Age
                                    </label>
                                    <input 
                                        type="number" 
                                        name="age" 
                                        id="age" 
                                        value="{{ old('age', $profile->age ?? '') }}" 
                                        min="1"
                                        max="100"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >
                                    @error('age')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Level -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="level">
                                        Chess Level
                                    </label>
                                    <select 
                                        name="level" 
                                        id="level" 
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >
                                        <option value="">Select a level</option>
                                        <option value="beginner" {{ old('level', $profile->level ?? '') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="intermediate" {{ old('level', $profile->level ?? '') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="advanced" {{ old('level', $profile->level ?? '') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                    </select>
                                    @error('level')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- School -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="school">
                                        School
                                    </label>
                                    <input 
                                        type="text" 
                                        name="school" 
                                        id="school" 
                                        value="{{ old('school', $profile->school ?? '') }}" 
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >
                                    @error('school')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <!-- Parent Name -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="parent_name">
                                        Parent/Guardian Name
                                    </label>
                                    <input 
                                        type="text" 
                                        name="parent_name" 
                                        id="parent_name" 
                                        value="{{ old('parent_name', $profile->parent_name ?? '') }}" 
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >
                                    @error('parent_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Parent Email -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="parent_email">
                                        Parent/Guardian Email
                                    </label>
                                    <input 
                                        type="email" 
                                        name="parent_email" 
                                        id="parent_email" 
                                        value="{{ old('parent_email', $profile->parent_email ?? '') }}" 
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >
                                    @error('parent_email')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Parent Phone -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="parent_phone">
                                        Parent/Guardian Phone
                                    </label>
                                    <input 
                                        type="text" 
                                        name="parent_phone" 
                                        id="parent_phone" 
                                        value="{{ old('parent_phone', $profile->parent_phone ?? '') }}" 
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >
                                    @error('parent_phone')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Learning Goals -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="learning_goals">
                                        Learning Goals
                                    </label>
                                    <textarea 
                                        name="learning_goals" 
                                        id="learning_goals" 
                                        rows="6"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >{{ old('learning_goals', $profile->learning_goals ?? '') }}</textarea>
                                    @error('learning_goals')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="bg-primary-800 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                                Update Student Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
