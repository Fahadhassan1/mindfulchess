<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Teacher Profile') }}
            </h2>
            <div>
            <a href="{{ route('admin.teachers.show', $teacher) }}" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mr-2">
                View Profile
            </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Left Column -->
                            <div class="space-y-6">
                                <!-- Profile Image -->
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-3" for="profile_image">
                                        Profile Image
                                    </label>
                                    
                                    @if($profile && $profile->profile_image)
                                        <div class="mb-4">
                                            <img src="{{ asset('storage/profile_images/' . $profile->profile_image) }}" 
                                                alt="Current profile image" class="h-32 w-32 object-cover rounded-full border-4 border-gray-200 shadow-md">
                                        </div>
                                    @else
                                        <div class="mb-4">
                                            <div class="h-32 w-32 bg-gray-200 rounded-full flex items-center justify-center border-4 border-gray-300">
                                                <svg class="h-16 w-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    @endif

                                    <input 
                                        type="file" 
                                        name="profile_image" 
                                        id="profile_image" 
                                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 p-2"
                                        accept="image/*"
                                    >
                                    <p class="text-gray-500 text-xs mt-2">Upload a new image (optional)</p>
                                    
                                    @error('profile_image')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Teaching Type -->
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-3" for="teaching_type">
                                        Teaching Type
                                    </label>
                                    <select 
                                        name="teaching_type" 
                                        id="teaching_type" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white text-gray-700"
                                    >
                                        <option value="">Select a teaching type</option>
                                        <option value="adult" {{ old('teaching_type', $profile->teaching_type ?? '') == 'adult' ? 'selected' : '' }}>Adult</option>
                                        <option value="kids" {{ old('teaching_type', $profile->teaching_type ?? '') == 'kids' ? 'selected' : '' }}>Kids</option>
                                        <option value="all" {{ old('teaching_type', $profile->teaching_type ?? '') == 'all' ? 'selected' : '' }}>Child & Adult</option>
                                    </select>
                                    @error('teaching_type')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Stripe Account ID -->
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-3" for="stripe_account_id">
                                        Stripe Account ID
                                    </label>
                                    <input 
                                        type="text" 
                                        name="stripe_account_id" 
                                        id="stripe_account_id" 
                                        value="{{ old('stripe_account_id', $profile->stripe_account_id ?? '') }}" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-gray-700"
                                        placeholder="acct_1234567890"
                                    >
                                    @error('stripe_account_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <!-- Bio -->
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-3" for="bio">
                                        Bio
                                    </label>
                                    <textarea 
                                        name="bio" 
                                        id="bio" 
                                        rows="8"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-gray-700 resize-none"
                                        placeholder="Tell us about yourself..."
                                    >{{ old('bio', $profile->bio ?? '') }}</textarea>
                                    @error('bio')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Status Checkboxes -->
                                <div class="space-y-4">
                                    <!-- Active Status -->
                                    <div class="bg-gray-50 p-4 rounded-lg border">
                                        <div class="flex items-start space-x-3">
                                            <input 
                                                type="checkbox" 
                                                name="is_active" 
                                                id="is_active" 
                                                class="w-5 h-5 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 focus:ring-2 mt-1"
                                                {{ old('is_active', $profile->is_active ?? true) ? 'checked' : '' }}
                                                value="1"
                                            >
                                            <div>
                                                <label class="text-gray-700 text-sm font-bold" for="is_active">
                                                    Active (Receive Notifications)
                                                </label>
                                                <p class="text-gray-500 text-xs mt-1">When deactivated, the teacher will not receive notifications</p>
                                            </div>
                                        </div>
                                        @error('is_active')
                                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- High Level Teacher Status -->
                                    <div class="bg-gray-50 p-4 rounded-lg border">
                                        <div class="flex items-start space-x-3">
                                            <input 
                                                type="checkbox" 
                                                name="high_level_teacher" 
                                                id="high_level_teacher" 
                                                class="w-5 h-5 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 focus:ring-2 mt-1"
                                                {{ old('high_level_teacher', $profile->high_level_teacher ?? false) ? 'checked' : '' }}
                                                value="1"
                                            >
                                            <div>
                                                <label class="text-gray-700 text-sm font-bold" for="high_level_teacher">
                                                    High Level Teacher (Lesson costs £50 per hour)
                                                </label>
                                                <p class="text-gray-500 text-xs mt-1">Mark this teacher as a high level teacher</p>
                                            </div>
                                        </div>
                                        @error('high_level_teacher')
                                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                            <div class="flex space-x-3">
                                <a href="{{ route('admin.teachers.show', $teacher) }}" 
                                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-200">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition duration-200">
                                    Update Teacher Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
