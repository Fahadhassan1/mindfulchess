<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Teacher Profile') }}: {{ $teacher->name }}
            </h2>
            <div>
                <a href="{{ route('admin.teachers.show', $teacher) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2">
                    View Profile
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Back to Teachers
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



                                <!-- Teaching Type -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="teaching_type">
                                        Teaching Type
                                    </label>
                                    <select 
                                        name="teaching_type" 
                                        id="teaching_type" 
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >
                                        <option value="">Select a teaching type</option>
                                        <option value="adult" {{ old('teaching_type', $profile->teaching_type ?? '') == 'adult' ? 'selected' : '' }}>Adult</option>
                                        <option value="kids" {{ old('teaching_type', $profile->teaching_type ?? '') == 'kids' ? 'selected' : '' }}>Kids</option>
                                    </select>
                                    @error('teaching_type')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Experience Years -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="experience_years">
                                        Years of Experience
                                    </label>
                                    <input 
                                        type="number" 
                                        name="experience_years" 
                                        id="experience_years" 
                                        value="{{ old('experience_years', $profile->experience_years ?? '') }}" 
                                        min="0"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >
                                    @error('experience_years')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <!-- Stripe Account ID -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="stripe_account_id">
                                        Stripe Account ID
                                    </label>
                                    <input 
                                        type="text" 
                                        name="stripe_account_id" 
                                        id="stripe_account_id" 
                                        value="{{ old('stripe_account_id', $profile->stripe_account_id ?? '') }}" 
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >
                                    @error('stripe_account_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Specialties -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="specialties">
                                        Specialties (comma separated)
                                    </label>
                                    <input 
                                        type="text" 
                                        name="specialties" 
                                        id="specialties" 
                                        value="{{ old('specialties', $profile->specialties ? implode(', ', $profile->specialties) : '') }}" 
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        placeholder="e.g. Opening strategies, Endgame tactics, Beginner coaching"
                                    >
                                    <p class="text-gray-500 text-xs mt-1">Enter specialties separated by commas</p>
                                    @error('specialties')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Bio -->
                                <div class="mb-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="bio">
                                        Bio
                                    </label>
                                    <textarea 
                                        name="bio" 
                                        id="bio" 
                                        rows="6"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    >{{ old('bio', $profile->bio ?? '') }}</textarea>
                                    @error('bio')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Active Status -->
                                <div class="mb-6">
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            name="is_active" 
                                            id="is_active" 
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                            {{ old('is_active', $profile->is_active ?? true) ? 'checked' : '' }}
                                            value="1"
                                        >
                                        <label class="ml-2 text-gray-700 text-sm font-bold" for="is_active">
                                            Active (Receive Notifications)
                                        </label>
                                    </div>
                                    <p class="text-gray-500 text-xs mt-1">When deactivated, the teacher will not receive notifications</p>
                                    @error('is_active')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Update Teacher Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
