<x-guest-layout>
    <div class="text-center">
        <div class="flex items-center justify-center mb-6">
            <div class="bg-green-100 rounded-full p-3">
                <svg class="h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
            
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Session Assignment Successful!</h2>
            <p class="text-gray-600 text-center mb-6">
                You have been successfully assigned as the teacher for this chess session.
            </p>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-700 mb-3">Session Details</h3>
                <p class="text-sm text-gray-600 mb-4">
                    <span class="font-medium">Session Type:</span> {{ ucfirst($session->session_type) }}<br>
                    <span class="font-medium">Duration:</span> {{ $session->duration }} minutes<br>
                    <span class="font-medium">Session Name:</span> {{ $session->session_name }}
                    @if($session->scheduled_at)
                    <br><span class="font-medium">Scheduled Time:</span> {{ $session->scheduled_at->format('l, F j, Y \a\t g:i A') }}
                    @endif
                </p>
                
                <h3 class="font-semibold text-gray-700 mb-2">Student Information</h3>
                <p class="text-sm text-gray-600">
                    <span class="font-medium">Name:</span> {{ $student->name }}<br>
                    <span class="font-medium">Email:</span> {{ $student->email }}
                </p>
            </div>
            
            <div class="flex justify-center space-x-4 mt-6">
                <a href="{{ route('login') }}" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200">
                    Login to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
