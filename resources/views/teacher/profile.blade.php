<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Teacher Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">


            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Profile Information') }}
                            </h2>
                            
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Update your account's profile information and teaching details.") }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('teacher.profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
                            @csrf
                            @method('put')

                            <!-- Profile Image -->
                            <div>
                                <label for="profile_image" class="block text-sm font-medium text-gray-700">Profile Image</label>
                                
                                @if($teacher->teacherProfile && $teacher->teacherProfile->profile_image)
                                    <div class="mt-2 mb-4">
                                        <img src="{{ asset('storage/profile_images/' . $teacher->teacherProfile->profile_image) }}" 
                                             alt="{{ $teacher->name }}" 
                                             class="w-32 h-32 rounded-full object-cover">
                                    </div>
                                @endif
                                
                                <input type="file" name="profile_image" id="profile_image" class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100">
                                
                                @error('profile_image')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>



                            <!-- Teaching Type -->
                            <div>
                                <x-input-label for="teaching_type" :value="__('Teaching Type')" />
                                <select id="teaching_type" name="teaching_type" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a teaching type</option>
                                    <option value="adult" {{ old('teaching_type', $teacher->teacherProfile->teaching_type ?? '') == 'adult' ? 'selected' : '' }}>Adult</option>
                                    <option value="kids" {{ old('teaching_type', $teacher->teacherProfile->teaching_type ?? '') == 'kids' ? 'selected' : '' }}>Kids</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('teaching_type')" />
                            </div>

                            <!-- Experience Years -->
                            <div>
                                <x-input-label for="experience_years" :value="__('Years of Experience')" />
                                <x-text-input id="experience_years" name="experience_years" type="number" class="mt-1 block w-full" 
                                    :value="old('experience_years', $teacher->teacherProfile->experience_years ?? '')" />
                                <x-input-error class="mt-2" :messages="$errors->get('experience_years')" />
                            </div>
                            
                            <!-- Specialties -->
                            <div>
                                <x-input-label for="specialties" :value="__('Specialties (comma separated)')" />
                                <x-text-input id="specialties" name="specialties" type="text" class="mt-1 block w-full" 
                                    :value="old('specialties', $teacher->teacherProfile->specialties ? implode(', ', $teacher->teacherProfile->specialties) : '')" 
                                    placeholder="e.g. Opening strategies, Endgame tactics, Beginner coaching" />
                                <p class="text-sm text-gray-500 mt-1">Enter specialties separated by commas</p>
                                <x-input-error class="mt-2" :messages="$errors->get('specialties')" />
                            </div>

                            <!-- Stripe Account ID -->
                            <div>
                                <x-input-label for="stripe_account_id" :value="__('Payment Account ID')" />
                                <x-text-input id="stripe_account_id" name="stripe_account_id" type="text" class="mt-1 block w-full bg-gray-100" 
                                    :value="$teacher->teacherProfile->stripe_account_id ?? 'Not set by admin yet'" disabled readonly />
                                <p class="text-sm text-gray-500 mt-1">For receiving payments (provided by admin only)</p>
                                <input type="hidden" name="stripe_account_id" value="{{ $teacher->teacherProfile->stripe_account_id ?? '' }}">
                            </div>

                            <!-- Bio -->
                            <div>
                                <x-input-label for="bio" :value="__('Biography / Teaching Philosophy')" />
                                <textarea id="bio" name="bio" rows="6" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('bio', $teacher->teacherProfile->bio ?? '') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('bio')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header class="flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900">
                                    {{ __('Active Status') }}
                                </h2>
                                
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __("Control whether you're available to receive notifications.") }}
                                </p>
                            </div>

                            <div>
                                <form method="post" action="{{ route('teacher.profile.toggle-active') }}">
                                    @csrf
                                    @method('put')
                                    
                                    @if($teacher->teacherProfile && $teacher->teacherProfile->is_active)
                                        <x-danger-button>
                                            {{ __('Set as Inactive') }}
                                        </x-danger-button>
                                    @else
                                        <x-primary-button>
                                            {{ __('Set as Active') }}
                                        </x-primary-button>
                                    @endif
                                </form>
                            </div>
                        </header>

                        <div class="mt-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($teacher->teacherProfile && $teacher->teacherProfile->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-600">
                                        {{ $teacher->teacherProfile && $teacher->teacherProfile->is_active
                                            ? 'You are currently active and will receive notifications.'
                                            : 'You are currently inactive and will not receive notifications.'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
