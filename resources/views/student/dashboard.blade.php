<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __("Welcome to your Student Dashboard") }}</h3>
                    <p class="mb-4">Manage your profile and view your assigned teachers.</p>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-teal-50 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-teal-800">My Teachers</h4>
                            <p class="text-sm text-gray-700 mt-2">View information about your assigned teachers.</p>
                            <a href="{{ route('student.teachers') }}" class="mt-3 inline-block bg-teal-600 text-white px-4 py-2 rounded text-sm hover:bg-teal-700">View Teachers</a>
                        </div>
                        
                        <div class="bg-blue-50 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-blue-800">My Sessions</h4>
                            <p class="text-sm text-gray-700 mt-2">View your booked sessions and payments.</p>
                            <div class="mt-3 space-y-2">
                                <a href="{{ route('student.sessions') }}" class="block bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 text-center">View Sessions</a>
                                <a href="{{ route('student.booking.calendar') }}" class="block bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 text-center">Book Additional Session</a>
                            </div>
                        </div>
                        
                        <div class="bg-amber-50 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-amber-800">My Profile</h4>
                            <p class="text-sm text-gray-700 mt-2">Update your profile information and preferences.</p>
                            <a href="{{ route('student.profile') }}" class="mt-3 inline-block bg-amber-600 text-white px-4 py-2 rounded text-sm hover:bg-amber-700">Edit Profile</a>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-purple-800">Payment & Billing</h4>
                            <p class="text-sm text-gray-700 mt-2">Manage payments, methods, and view invoices.</p>
                            <div class="mt-3 space-y-2">
                                <a href="{{ route('student.payments') }}" class="block bg-purple-600 text-white px-4 py-2 rounded text-sm hover:bg-purple-700 text-center">Payment History</a>
                                {{-- <a href="{{ route('student.payment-methods') }}" class="block bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 text-center">Payment Methods</a> --}}
                            </div>
                        </div>
                        
                        <div class="bg-cyan-50 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-cyan-800">Account Settings</h4>
                            <p class="text-sm text-gray-700 mt-2">Manage your account settings and preferences.</p>
                            <a href="{{ route('profile.edit') }}" class="mt-3 inline-block bg-cyan-600 text-white px-4 py-2 rounded text-sm hover:bg-cyan-700">Account Settings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
