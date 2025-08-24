<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Students') }}
        </h2>
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
                    <h3 class="text-lg font-medium mb-4">{{ __("Students Assigned to You") }}</h3>
                    
                    <!-- Search and Filter Form -->
                    <x-filter-form
                        :route="route('teacher.students')"
                        :showSearch="true"
                        searchLabel="Search"
                        searchPlaceholder="Name, email, school or parent name..."
                        filterName="level"
                        filterLabel="Level"
                        filterAllLabel="All Levels"
                        :filterOptions="collect($levels)->mapWithKeys(function($level) {
                            return [$level => ucfirst($level)];
                        })"
                    />
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
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
                                            @elseif($student->studentProfile->level == 'intermediate') bg-green-100 text-green-800
                                            @else bg-purple-100 text-purple-800 @endif">
                                                {{ ucfirst($student->studentProfile->level) }}
                                            </span>
                                        @else
                                            Not specified
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="view-details text-green-600 hover:text-green-900" 
                                                data-student-id="{{ $student->id }}"
                                                data-student-name="{{ $student->name }}"
                                                data-student-email="{{ $student->email }}"
                                                data-student-age="{{ $student->studentProfile->age ?? 'N/A' }}"
                                                data-student-level="{{ $student->studentProfile->level ? ucfirst($student->studentProfile->level) : 'N/A' }}"
                                                data-student-school="{{ $student->studentProfile->school ?? 'N/A' }}"
                                                data-student-parent-name="{{ $student->studentProfile->parent_name ?? 'N/A' }}"
                                                data-student-parent-email="{{ $student->studentProfile->parent_email ?? 'N/A' }}"
                                                data-student-parent-phone="{{ $student->studentProfile->parent_phone ?? 'N/A' }}"
                                                data-student-goals="{{ $student->studentProfile->learning_goals ?? 'No goals specified.' }}">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                
                                @if($students->isEmpty())
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No students assigned to you yet
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

    <!-- Student Details Modal -->
    <div id="studentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"></h3>
                <div class="mt-2 px-7 py-3">
                    <div class="text-left space-y-3">
                        <p><strong>Email:</strong> <span id="modal-email"></span></p>
                        <p><strong>Age:</strong> <span id="modal-age"></span></p>
                        <p><strong>Level:</strong> <span id="modal-level"></span></p>
                        <p><strong>School:</strong> <span id="modal-school"></span></p>
                        <p><strong>Parent Name:</strong> <span id="modal-parent-name"></span></p>
                        <p><strong>Parent Email:</strong> <span id="modal-parent-email"></span></p>
                        <p><strong>Parent Phone:</strong> <span id="modal-parent-phone"></span></p>
                        <div>
                            <strong>Learning Goals:</strong> 
                            <p class="mt-1 text-sm text-gray-600" id="modal-goals"></p>
                        </div>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="closeModal" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('studentModal');
            const viewButtons = document.querySelectorAll('.view-details');
            const closeButton = document.getElementById('closeModal');
            
            // Open modal with student details
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('modal-title').textContent = this.dataset.studentName;
                    document.getElementById('modal-email').textContent = this.dataset.studentEmail;
                    document.getElementById('modal-age').textContent = this.dataset.studentAge;
                    document.getElementById('modal-level').textContent = this.dataset.studentLevel;
                    document.getElementById('modal-school').textContent = this.dataset.studentSchool;
                    document.getElementById('modal-parent-name').textContent = this.dataset.studentParentName;
                    document.getElementById('modal-parent-email').textContent = this.dataset.studentParentEmail;
                    document.getElementById('modal-parent-phone').textContent = this.dataset.studentParentPhone;
                    document.getElementById('modal-goals').textContent = this.dataset.studentGoals;
                    
                    modal.classList.remove('hidden');
                });
            });
            
            // Close modal
            closeButton.addEventListener('click', function() {
                modal.classList.add('hidden');
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>
