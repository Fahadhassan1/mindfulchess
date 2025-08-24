<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Lessons') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __("Available Lessons") }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="border border-gray-200 rounded-lg shadow p-4">
                            <h4 class="font-bold">Introduction to Chess</h4>
                            <p class="text-sm text-gray-600 mt-2">Basic rules and piece movement for beginners.</p>
                            <div class="mt-4">
                                <button class="bg-teal-600 text-white px-4 py-2 rounded text-sm hover:bg-teal-700">Start Lesson</button>
                            </div>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg shadow p-4">
                            <h4 class="font-bold">Opening Strategies</h4>
                            <p class="text-sm text-gray-600 mt-2">Common opening moves and their strategic purposes.</p>
                            <div class="mt-4">
                                <button class="bg-teal-600 text-white px-4 py-2 rounded text-sm hover:bg-teal-700">Start Lesson</button>
                            </div>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg shadow p-4">
                            <h4 class="font-bold">Endgame Techniques</h4>
                            <p class="text-sm text-gray-600 mt-2">Strategies for winning in endgame scenarios.</p>
                            <div class="mt-4">
                                <button class="bg-teal-600 text-white px-4 py-2 rounded text-sm hover:bg-teal-700">Start Lesson</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
