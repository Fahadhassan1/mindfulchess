<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Management') }}
            </h2>
            <div>
                <a href="{{ route('admin.students.export', request()->query()) }}" 
                   class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export to CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __("Manage Students") }}</h3>
                    
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.students.index') }}" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Search Input -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                                <input type="text" 
                                       name="search" 
                                       id="search"
                                       value="{{ request('search') }}" 
                                       placeholder="Name, email, school, parent name, or teacher..."
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-800 focus:ring-primary-800 sm:text-sm">
                            </div>
                            
                            <!-- Level Filter -->
                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
                                <select name="level" 
                                        id="level"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-800 focus:ring-primary-800 sm:text-sm">
                                    <option value="">All Levels</option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                            {{ ucfirst($level) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Teacher Filter -->
                            <div>
                                <label for="teacher" class="block text-sm font-medium text-gray-700">Assigned Teacher</label>
                                <select name="teacher" 
                                        id="teacher"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-800 focus:ring-primary-800 sm:text-sm">
                                    <option value="">All Teachers</option>
                                    <option value="unassigned" {{ request('teacher') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ request('teacher') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Filter Buttons -->
                            <div class="flex items-end space-x-2">
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-primary-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Filter
                                </button>
                                <a href="{{ route('admin.students.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Teacher</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($students as $student)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $student->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $student->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $student->studentProfile->age ?? 'Not specified' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($student->studentProfile && $student->studentProfile->level)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($student->studentProfile->level == 'beginner') bg-green-100 text-green-800
                                            @elseif($student->studentProfile->level == 'intermediate') bg-primary-100 text-primary-800
                                            @else bg-purple-100 text-purple-800 @endif">
                                                {{ ucfirst($student->studentProfile->level) }}
                                            </span>
                                        @else
                                            Not specified
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($student->studentProfile && $student->studentProfile->teacher)
                                            <div class="flex items-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 mr-2">
                                                    {{ $student->studentProfile->teacher->name }}
                                                </span>
                                                <button onclick="openReassignModal({{ $student->id }}, '{{ $student->name }}', {{ $student->studentProfile->teacher_id ?? 'null' }})" 
                                                        class="text-primary-600 hover:text-primary-900 text-xs">
                                                    Change
                                                </button>
                                            </div>
                                        @else
                                            <div class="flex items-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 mr-2">
                                                    Unassigned
                                                </span>
                                                <button onclick="openReassignModal({{ $student->id }}, '{{ $student->name }}', null)" 
                                                        class="text-primary-600 hover:text-primary-900 text-xs">
                                                    Assign
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.students.edit', $student) }}" class="text-primary-600 hover:text-primary-900">Edit</a>
                                    </td>
                                </tr>
                                @endforeach
                                
                                @if($students->isEmpty())
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No students found matching your criteria
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $students->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teacher Reassignment Modal -->
    <div id="reassignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 1000;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">
                        Reassign Teacher
                    </h3>
                    <button onclick="closeReassignModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="reassignForm" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="modalStudentName" class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                        <p id="modalStudentName" class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md"></p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-2">Select Teacher</label>
                        <select name="teacher_id" id="teacher_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-800 focus:ring-primary-800 sm:text-sm">
                            <option value="">No Teacher (Unassigned)</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeReassignModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-primary-800 text-white text-sm font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            Reassign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openReassignModal(studentId, studentName, currentTeacherId) {
            document.getElementById('modalTitle').textContent = 'Reassign Teacher for ' + studentName;
            document.getElementById('modalStudentName').textContent = studentName;
            document.getElementById('reassignForm').action = `/admin/students/${studentId}/reassign-teacher`;
            
            // Reset and set the teacher selection
            const teacherSelect = document.getElementById('teacher_id');
            teacherSelect.value = currentTeacherId || '';
            
            document.getElementById('reassignModal').classList.remove('hidden');
        }

        function closeReassignModal() {
            document.getElementById('reassignModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('reassignModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReassignModal();
            }
        });
    </script>
</x-app-layout>
