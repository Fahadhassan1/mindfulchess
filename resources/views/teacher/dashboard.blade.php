<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Teacher Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h1 class="text-2xl font-semibold mb-2">Welcome, {{ $teacher->name }}!</h1>
                        
                        <!-- Status Badge -->
                        <div class="mb-4">
                            <span class="text-sm">Status: </span>
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
                    </div>

                    <!-- Stats Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border-l-4 border-blue-500">
                            <h3 class="text-lg font-medium text-gray-900">Total Sessions</h3>
                            <p class="text-3xl font-bold mt-2 text-blue-600">{{ $totalSessions }}</p>
                        </div>
                        
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border-l-4 border-green-500">
                            <h3 class="text-lg font-medium text-gray-900">Total Students</h3>
                            <p class="text-3xl font-bold mt-2 text-green-600">{{ $totalStudents }}</p>
                        </div>
                        
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border-l-4 border-purple-500">
                            <h3 class="text-lg font-medium text-gray-900">Recurring Students</h3>
                            <p class="text-3xl font-bold mt-2 text-purple-600">{{ $recurringStudents }}</p>
                        </div>
                        
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border-l-4 border-yellow-500">
                            <h3 class="text-lg font-medium text-gray-900">Retention Rate</h3>
                            <div class="flex items-center mt-2">
                                <p class="text-3xl font-bold text-yellow-600">{{ $recurringPercentage }}%</p>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                <div class="bg-yellow-600 h-2.5 rounded-full" style="width: {{ $recurringPercentage }}%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Main Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Students Stats Card -->
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg overflow-hidden text-white">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold">Students</h3>
                                        <p class="text-3xl font-bold mt-2">{{ $assignedStudents }}</p>
                                    </div>
                                    <div>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('teacher.students') }}" class="text-white hover:text-green-100 text-sm font-medium">
                                        View All Students →
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Sessions Card -->
                        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg overflow-hidden text-white">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold">My Sessions</h3>
                                        <p class="text-sm mt-2">Manage confirmed sessions</p>
                                    </div>
                                    <div>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('teacher.sessions') }}" class="text-white hover:text-green-100 text-sm font-medium">
                                        View All Sessions →
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Card -->
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg overflow-hidden text-white">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold">My Profile</h3>
                                        <p class="text-sm mt-2">
                                            @if($teacher->teacherProfile)
                                                Your profile is set up
                                            @else
                                                Complete your profile details
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('teacher.profile') }}" class="text-white hover:text-purple-100 text-sm font-medium">
                                        View & Edit Profile →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Monthly Sessions Chart -->
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Sessions</h3>
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                            <div style="height: 300px;">
                                <canvas id="sessionsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Sessions Chart
            const ctx = document.getElementById('sessionsChart').getContext('2d');
            const sessionsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($monthlyLabels) !!},
                    datasets: [{
                        label: 'Sessions',
                        data: {!! json_encode($monthlyData) !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
