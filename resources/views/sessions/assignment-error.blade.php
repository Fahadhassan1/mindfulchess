<x-guest-layout>
    <div class="text-center">
        <div class="flex items-center justify-center mb-6">
            <div class="bg-red-100 rounded-full p-3">
                <svg class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
            
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Assignment Error</h2>
            <p class="text-gray-600 text-center mb-8">{{ $message }}</p>
            
            <div class="flex justify-center">
                <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200">
                    Login to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
