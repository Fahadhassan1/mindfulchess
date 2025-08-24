<x-guest-layout>
    <div class="text-center">
        <div class="flex items-center justify-center mb-6">
            <div class="bg-yellow-100 rounded-full p-3">
                <svg class="h-12 w-12 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 4a8 8 0 100 16 8 8 0 000-16z"></path>
                </svg>
            </div>
        </div>
            
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Session Already Assigned</h2>
            <p class="text-gray-600 text-center mb-6">
                This chess session has already been assigned to another teacher.
            </p>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-700 mb-2">Session Details</h3>
                <p class="text-sm text-gray-600">
                    <span class="font-medium">Session Type:</span> {{ ucfirst($session->session_type) }}<br>
                    <span class="font-medium">Duration:</span> {{ $session->duration }} minutes<br>
                    <span class="font-medium">Current Teacher:</span> {{ $teacher->name }}
                </p>
            </div>
            
            <div class="flex justify-center">
                <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200">
                    Login to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
