<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Availability') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
  

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Add Availability Slot') }}
                            </h2>
                            
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Set up your teaching availability schedule.") }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('teacher.availability.store') }}" class="mt-6 space-y-6">
                            @csrf

                            <!-- Day of Week -->
                            <div>
                                <x-input-label for="day_of_week" :value="__('Day of Week')" />
                                <select id="day_of_week" name="day_of_week" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="monday" {{ old('day_of_week') == 'monday' ? 'selected' : '' }}>Monday</option>
                                    <option value="tuesday" {{ old('day_of_week') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="wednesday" {{ old('day_of_week') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="thursday" {{ old('day_of_week') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                    <option value="friday" {{ old('day_of_week') == 'friday' ? 'selected' : '' }}>Friday</option>
                                    <option value="saturday" {{ old('day_of_week') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                    <option value="sunday" {{ old('day_of_week') == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('day_of_week')" />
                            </div>

                            <!-- Start Time -->
                            <div>
                                <x-input-label for="start_time" :value="__('Start Time')" />
                                <input type="time" id="start_time" name="start_time" value="{{ old('start_time') }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
                            </div>

                            <!-- End Time -->
                            <div>
                                <x-input-label for="end_time" :value="__('End Time')" />
                                <input type="time" id="end_time" name="end_time" value="{{ old('end_time') }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <x-input-error class="mt-2" :messages="$errors->get('end_time')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Add Availability') }}</x-primary-button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-7xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 mb-6">
                                {{ __('Your Current Availability') }}
                            </h2>
                        </header>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                <div class="border rounded-md p-4 bg-gray-50">
                                    <h3 class="font-semibold text-gray-700 mb-3 capitalize">{{ $day }}</h3>
                                    
                                    @if($groupedAvailability[$day]->count() > 0)
                                        <ul class="space-y-2">
                                            @foreach($groupedAvailability[$day] as $slot)
                                                <li class="flex justify-between items-center bg-white p-2 rounded border">
                                                    <span>
                                                        {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - 
                                                        {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                                    </span>
                                                    <div>
                                                        <form method="POST" action="{{ route('teacher.availability.destroy', $slot) }}" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                                    onclick="return confirm('Are you sure you want to delete this availability slot?')">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-gray-500">No availability set for this day</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
