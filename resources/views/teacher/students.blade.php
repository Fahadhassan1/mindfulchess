<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Students') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
         

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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessions</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Session</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($students as $student)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $student->name }}</td>
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(isset($studentStats[$student->id]))
                                            <span class="px-2 py-1 inline-flex text-sm leading-5 font-medium rounded-full {{ $studentStats[$student->id]['session_count'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $studentStats[$student->id]['session_count'] }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-sm leading-5 font-medium rounded-full bg-gray-100 text-gray-600">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(isset($studentStats[$student->id]) && $studentStats[$student->id]['is_recurring'])
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Recurring
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                One-time
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(isset($studentStats[$student->id]) && $studentStats[$student->id]['last_session'])
                                            {{ $studentStats[$student->id]['last_session'] }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    
                                        
                                        @if(isset($studentStats[$student->id]) && $studentStats[$student->id]['session_count'] > 0)
                                            <a href="{{ route('teacher.sessions') }}?student_id={{ $student->id }}" class="text-green-600 hover:text-green-900 ml-3">
                                                Sessions
                                            </a>
                                        @endif
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
                        <!-- Session Stats Section -->
                        <div class="mb-4 pb-3 border-b">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Session Statistics</h4>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <div class="text-xs text-gray-500">Total</div>
                                    <div class="text-lg font-medium text-blue-600" id="modal-session-count">0</div>
                                </div>
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <div class="text-xs text-gray-500">Status</div>
                                    <div class="text-sm font-medium" id="modal-recurring-status">-</div>
                                </div>
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <div class="text-xs text-gray-500">Last Session</div>
                                    <div class="text-sm font-medium text-gray-600" id="modal-last-session">-</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Student Details Section -->
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
                <div class="items-center px-4 py-3 flex space-x-3">
                    <button id="closeModal" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md flex-grow shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Close
                    </button>
                    <a id="messageStudentBtn" href="#" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md flex-grow shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Message
                    </a>
                    <a id="viewSessionsBtn" href="#" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md flex-grow shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                        View Sessions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('studentModal');
            const viewButtons = document.querySelectorAll('.view-details');
            const closeButton = document.getElementById('closeModal');
            const viewSessionsBtn = document.getElementById('viewSessionsBtn');
            const studentStats = @json($studentStats);
            
            // Open modal with student details
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const studentId = this.dataset.studentId;
                    
                    document.getElementById('modal-title').textContent = this.dataset.studentName;
                    document.getElementById('modal-email').textContent = this.dataset.studentEmail;
                    document.getElementById('modal-age').textContent = this.dataset.studentAge;
                    document.getElementById('modal-level').textContent = this.dataset.studentLevel;
                    document.getElementById('modal-school').textContent = this.dataset.studentSchool;
                    document.getElementById('modal-parent-name').textContent = this.dataset.studentParentName;
                    document.getElementById('modal-parent-email').textContent = this.dataset.studentParentEmail;
                    document.getElementById('modal-parent-phone').textContent = this.dataset.studentParentPhone;
                    document.getElementById('modal-goals').textContent = this.dataset.studentGoals;
                    
                    // Add session statistics
                    const sessionCount = studentStats[studentId] ? studentStats[studentId].session_count : 0;
                    document.getElementById('modal-session-count').textContent = sessionCount;
                    
                    if (sessionCount > 0) {
                        const isRecurring = studentStats[studentId].is_recurring;
                        document.getElementById('modal-recurring-status').textContent = isRecurring ? 'Recurring' : 'One-time';
                        document.getElementById('modal-recurring-status').className = isRecurring ? 
                            'text-sm font-medium text-green-600' : 'text-sm font-medium text-yellow-600';
                        document.getElementById('modal-last-session').textContent = studentStats[studentId].last_session;
                        
                        // Enable sessions button
                        viewSessionsBtn.href = "{{ route('teacher.sessions') }}?student_id=" + studentId;
                        viewSessionsBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    } else {
                        document.getElementById('modal-recurring-status').textContent = 'No sessions';
                        document.getElementById('modal-recurring-status').className = 'text-sm font-medium text-gray-500';
                        document.getElementById('modal-last-session').textContent = '-';
                        
                        // Disable sessions button
                        viewSessionsBtn.href = "#";
                        viewSessionsBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                    
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
