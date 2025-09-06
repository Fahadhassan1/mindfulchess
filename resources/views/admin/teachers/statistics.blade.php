<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Teacher Statistics Overview') }}
            </h2>
            <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Teachers
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 grid gap-6 md:grid-cols-4">
                <!-- Summary Cards -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Total Teachers</h3>
                        <p class="text-3xl font-bold mt-2">{{ count($teacherStats) }}</p>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Active Teachers</h3>
                        <p class="text-3xl font-bold mt-2">{{ collect($teacherStats)->where('is_active', true)->count() }}</p>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Total Sessions</h3>
                        <p class="text-3xl font-bold mt-2">{{ collect($teacherStats)->sum('total_sessions') }}</p>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Total Students</h3>
                        <p class="text-3xl font-bold mt-2">{{ collect($teacherStats)->sum('total_students') }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Student Session Milestone Stats -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium mb-4">{{ __("Student Session Milestones") }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @php
                            $totalStudents = collect($teacherStats)->sum('total_students');
                            $tenPlusStudents = collect($teacherStats)->sum('ten_plus_sessions');
                            $twentyPlusStudents = collect($teacherStats)->sum('twenty_plus_sessions');
                            $fiftyPlusStudents = collect($teacherStats)->sum('fifty_plus_sessions');
                            
                            $tenPlusPercentage = $totalStudents > 0 ? round(($tenPlusStudents / $totalStudents) * 100, 1) : 0;
                            $twentyPlusPercentage = $totalStudents > 0 ? round(($twentyPlusStudents / $totalStudents) * 100, 1) : 0;
                            $fiftyPlusPercentage = $totalStudents > 0 ? round(($fiftyPlusStudents / $totalStudents) * 100, 1) : 0;
                        @endphp
                        
                        <!-- 10+ Sessions -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-medium text-gray-800">Students with 10+ Sessions</h4>
                            <div class="mt-2 flex items-baseline">
                                <span class="text-2xl font-semibold text-blue-600">{{ $tenPlusPercentage }}%</span>
                                <span class="ml-2 text-sm text-gray-600">of students ({{ $tenPlusStudents }} students)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $tenPlusPercentage }}%"></div>
                            </div>
                        </div>
                        
                        <!-- 20+ Sessions -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-medium text-gray-800">Students with 20+ Sessions</h4>
                            <div class="mt-2 flex items-baseline">
                                <span class="text-2xl font-semibold text-blue-600">{{ $twentyPlusPercentage }}%</span>
                                <span class="ml-2 text-sm text-gray-600">of students ({{ $twentyPlusStudents }} students)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $twentyPlusPercentage }}%"></div>
                            </div>
                        </div>
                        
                        <!-- 50+ Sessions -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-medium text-gray-800">Students with 50+ Sessions</h4>
                            <div class="mt-2 flex items-baseline">
                                <span class="text-2xl font-semibold text-blue-600">{{ $fiftyPlusPercentage }}%</span>
                                <span class="ml-2 text-sm text-gray-600">of students ({{ $fiftyPlusStudents }} students)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $fiftyPlusPercentage }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __("Teacher Performance Statistics") }}</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sessions</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Students</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recurring Students</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">10+ Sessions</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Retention Rate</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($teacherStats as $teacher)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <a href="{{ route('admin.teachers.statistics.show', $teacher['id']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-900">{{ $teacher['name'] }}</a>
                                                    <div class="text-sm text-gray-500">{{ $teacher['email'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $teacher['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $teacher['is_active'] ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $teacher['total_sessions'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $teacher['total_students'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $teacher['recurring_students'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $teacher['ten_plus_sessions'] ?? 0 }}
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1 mt-1">
                                                <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $teacher['ten_plus_percentage'] ?? 0 }}%"></div>
                                            </div>
                                            <span class="text-xs">{{ $teacher['ten_plus_percentage'] ?? 0 }}%</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $teacher['recurring_percentage'] }}%"></div>
                                            </div>
                                            <span class="text-xs">{{ $teacher['recurring_percentage'] }}%</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('admin.teachers.statistics.show', $teacher['id']) }}" class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                View Details
                                            </a>
                                        </td>
                            
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No teachers found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
