<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">Book Session for {{ $student->name }}</h1>
            <a href="{{ route('teacher.students') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                Back to Students
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Student Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Student Information</h3>
                    <div class="flex items-center space-x-4">
                        @if($student->studentProfile && $student->studentProfile->profile_image)
                            <img src="{{ asset('storage/profile_images/' . $student->studentProfile->profile_image) }}" 
                                alt="{{ $student->name }}" class="w-16 h-16 object-cover rounded-full">
                        @else
                            <div class="w-16 h-16 bg-gray-200 flex items-center justify-center rounded-full">
                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <h4 class="text-xl font-bold">{{ $student->name }}</h4>
                            <p class="text-gray-600">{{ $student->email }}</p>
                            @if($student->studentProfile)
                                @if($student->studentProfile->session_type_preference === 'adult' && $student->studentProfile->chess_rating)
                                    <p class="text-sm text-gray-500">Rating: {{ $student->studentProfile->chess_rating }}</p>
                                @elseif($student->studentProfile->level)
                                    <p class="text-sm text-gray-500">Level: {{ ucfirst($student->studentProfile->level) }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form id="booking-form">
                        @csrf
                        
                        <!-- Session Configuration -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Session Details</h3>
                            
                            @if($usesPremiumPricing)
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            <span class="font-medium">Premium Rate Applied:</span> This student has completed 10+ sessions with you, and premium pricing is now in effect.
                                        </p>
                                        <div class="mt-2 text-xs text-yellow-600">
                                            <p><strong>Premium Rates:</strong> 30min: £{{ number_format($sessionPrices['30']['price'], 2) }} | 45min: £{{ number_format($sessionPrices['45']['price'], 2) }} | 60min: £{{ number_format($sessionPrices['60']['price'], 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- @else
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <span class="font-medium">Standard Rates:</span> Standard pricing applies for this student.
                                        </p>
                                        <div class="mt-2 text-xs text-blue-600">
                                            <p><strong>Standard Rates:</strong> 30min: £{{ number_format($sessionPrices['30']['price'], 2) }} | 45min: £{{ number_format($sessionPrices['45']['price'], 2) }} | 60min: £{{ number_format($sessionPrices['60']['price'], 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            @endif
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration</label>
                                    <select name="duration" id="duration" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="30">30 minutes - £{{ number_format($sessionPrices['30']['price'], 2) }}</option>
                                        <option value="45">45 minutes - £{{ number_format($sessionPrices['45']['price'], 2) }}</option>
                                        <option value="60" selected>60 minutes - £{{ number_format($sessionPrices['60']['price'], 2) }}</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Session Type (automatically determined based on stored preference) -->
                            <input type="hidden" name="session_type" id="session_type" value="{{ $student->studentProfile->session_type_preference  }}">
                            <div class="mt-2 p-3 bg-blue-50 rounded-md">
                                <p class="text-sm text-blue-700">
                                    <strong>Session Type:</strong> 
                                    <span id="session_type_display">{{ $student->studentProfile->session_type_preference  }}</span>
                                    (based on student's preference)
                                </p>
                            </div>
                            
                            <!-- Meeting Link Field -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Meeting Link</label>
                                <input type="url" name="meeting_link" id="meeting_link" 
                                       placeholder="https://meet.google.com/abc-defg-hij or https://teams.microsoft.com/..."
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-sm text-gray-500">
                                    Provide your Google Meet, Microsoft Teams, or other video conferencing link for this session.
                                </p>
                            </div>
                        </div>

                        <!-- Calendar -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Select Date & Time</h3>
                            
                            @if($availability->isEmpty())
                                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded">
                                    <p>You have no available time slots in the next 30 days. Please set up your availability first.</p>
                                    <a href="{{ route('teacher.availability') }}" class="mt-2 inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Set Availability
                                    </a>
                                </div>
                            @else
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- Calendar Grid -->
                                    <div>
                                        <h4 class="font-medium mb-3">Available Dates</h4>
                                        <div class="grid grid-cols-7 gap-1 text-center text-sm">
                                            <!-- Calendar headers -->
                                            <div class="font-medium p-2 text-gray-500">Sun</div>
                                            <div class="font-medium p-2 text-gray-500">Mon</div>
                                            <div class="font-medium p-2 text-gray-500">Tue</div>
                                            <div class="font-medium p-2 text-gray-500">Wed</div>
                                            <div class="font-medium p-2 text-gray-500">Thu</div>
                                            <div class="font-medium p-2 text-gray-500">Fri</div>
                                            <div class="font-medium p-2 text-gray-500">Sat</div>
                                            
                                            @php
                                                $today = \Carbon\Carbon::now();
                                                $endDate = $today->copy()->addDays(30);
                                                
                                                // Start from today and show next 30 days
                                                $currentDate = $today->copy();
                                                
                                                // Add empty cells to align the first date with correct day of week
                                                $firstDayOfWeek = $currentDate->dayOfWeek; // 0=Sunday, 1=Monday, etc.
                                                for ($i = 0; $i < $firstDayOfWeek; $i++) {
                                                    echo '<div class="p-2"></div>';
                                                }
                                            @endphp
                                            
                                            @for($date = \Carbon\Carbon::now(); $date->lte($endDate); $date->addDay())
                                                @php
                                                    $dateString = $date->format('Y-m-d');
                                                    $hasAvailability = $availability->has($dateString);
                                                @endphp
                                                <div class="p-1">
                                                    <button type="button" 
                                                            class="w-full p-2 rounded date-btn {{ $hasAvailability ? 'bg-blue-100 hover:bg-blue-200 text-blue-800 cursor-pointer' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}"
                                                            data-date="{{ $dateString }}"
                                                            {{ $hasAvailability ? '' : 'disabled' }}>
                                                        {{ $date->format('j') }}
                                                    </button>
                                                </div>
                                                
                                                @if($date->dayOfWeek === 6 && !$date->eq($endDate)) <!-- Saturday, end of week, but not the last date -->
                                                    </div><div class="grid grid-cols-7 gap-1 text-center text-sm">
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    
                                    <!-- Time Slots -->
                                    <div>
                                        <h4 class="font-medium mb-3">Available Times</h4>
                                        <div id="time-slots" class="space-y-2 max-h-80 overflow-y-auto border border-gray-200 rounded-lg p-3">
                                            <p class="text-gray-500 text-sm">Please select a date first</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Payment Method Info -->
                        @if($hasStoredPaymentMethod)
                            <div class="mb-6">
                                <div class="bg-green-50 border border-green-200 p-4 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-green-800 font-medium">We'll use {{ $student->name }}'s saved payment method from previous bookings.</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-6">
                                <div class="bg-blue-50 border border-blue-200 p-4 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-blue-800">You'll be prompted to enter payment details for {{ $student->name }} after selecting the session time.</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Selected Session Summary -->
                        <div id="session-summary" class="mb-6 hidden">
                            <div class="bg-gray-50 border border-gray-200 p-4 rounded">
                                <h4 class="font-medium mb-2">Session Summary</h4>
                                <div class="text-sm space-y-1">
                                    <p><span class="font-medium">Student:</span> {{ $student->name }}</p>
                                    <p><span class="font-medium">Date:</span> <span id="selected-date-display"></span></p>
                                    <p><span class="font-medium">Time:</span> <span id="selected-time-display"></span></p>
                                    <p><span class="font-medium">Duration:</span> <span id="selected-duration-display"></span></p>
                                    <p><span class="font-medium">Price:</span> <span id="selected-price-display"></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Book Button -->
                        <div class="flex justify-end">
                            <button type="submit" id="book-session-btn" class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                                <span id="book-btn-text">Book Session for {{ $student->name }}</span>
                                <div id="book-btn-spinner" class="hidden inline-block ml-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden availability data -->
    <script type="application/json" id="availability-data">
        @json($availability)
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let availabilityData = JSON.parse(document.getElementById('availability-data').textContent);
            const form = document.getElementById('booking-form');
            const bookBtn = document.getElementById('book-session-btn');
            const bookBtnText = document.getElementById('book-btn-text');
            const bookBtnSpinner = document.getElementById('book-btn-spinner');
            const sessionSummary = document.getElementById('session-summary');
            const durationSelect = document.getElementById('duration');
            
            let selectedDate = null;
            let selectedTime = null;

            // Session pricing
            const sessionPrices = @json($sessionPrices);

            // Duration change handler - reload availability when duration changes
            durationSelect.addEventListener('change', function() {
                const duration = this.value;
                
                // Reset selections
                selectedDate = null;
                selectedTime = null;
                updateSessionSummary();
                
                // Reset visual selections
                document.querySelectorAll('.date-btn').forEach(btn => {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    if (!btn.disabled) {
                        btn.classList.add('bg-blue-100', 'text-blue-800');
                    }
                });
                
                // Clear time slots
                document.getElementById('time-slots').innerHTML = '<p class="text-gray-500 text-sm">Please select a date first</p>';
                
                // Fetch new availability for selected duration
                fetch('{{ route("teacher.booking.availability", ["student" => $student->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ duration: parseInt(duration) })
                })
                .then(response => response.json())
                .then(data => {
                    availabilityData = data.availability;
                    updateCalendarDates();
                })
                .catch(error => {
                    console.error('Error fetching availability:', error);
                });
            });

            // Function to update calendar dates based on new availability data
            function updateCalendarDates() {
                document.querySelectorAll('.date-btn').forEach(btn => {
                    const date = btn.dataset.date;
                    const hasAvailability = availabilityData.hasOwnProperty(date) && availabilityData[date].length > 0;
                    
                    if (hasAvailability) {
                        btn.disabled = false;
                        btn.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                        btn.classList.add('bg-blue-100', 'hover:bg-blue-200', 'text-blue-800', 'cursor-pointer');
                    } else {
                        btn.disabled = true;
                        btn.classList.remove('bg-blue-100', 'hover:bg-blue-200', 'text-blue-800', 'cursor-pointer', 'bg-blue-600', 'text-white');
                        btn.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                    }
                });
            }

            // Date selection
            document.querySelectorAll('.date-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (this.disabled) return;

                    // Remove previous selection
                    document.querySelectorAll('.date-btn').forEach(b => b.classList.remove('bg-blue-600', 'text-white'));
                    
                    // Add selection to current
                    this.classList.add('bg-blue-600', 'text-white');
                    this.classList.remove('bg-blue-100', 'text-blue-800');
                    
                    selectedDate = this.dataset.date;
                    selectedTime = null;
                    
                    // Update time slots
                    updateTimeSlots(selectedDate);
                    updateSessionSummary();
                });
            });

            function updateTimeSlots(date) {
                const timeSlots = document.getElementById('time-slots');
                const slots = availabilityData[date] || [];
                
                if (slots.length === 0) {
                    timeSlots.innerHTML = '<p class="text-gray-500 text-sm">No available times for this date</p>';
                    return;
                }

                const slotsHtml = slots.map(slot => {
                    // Calculate the day of week from the date string to ensure accuracy
                    const slotDate = new Date(slot.date + 'T00:00:00');
                    const dayOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][slotDate.getDay()];
                    
                    return `
                        <button type="button" 
                                class="time-slot-btn w-full p-3 text-left border border-gray-300 rounded hover:bg-blue-50 hover:border-blue-300 transition duration-200"
                                data-time="${slot.start_time.substring(0, 5)}">
                            <div class="font-medium">${slot.formatted_start} - ${slot.formatted_end}</div>
                            <div class="text-sm text-gray-500">${dayOfWeek} • ${slot.duration} minutes</div>
                        </button>
                    `;
                }).join('');

                timeSlots.innerHTML = slotsHtml;

                // Add click handlers to time slots
                document.querySelectorAll('.time-slot-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        // Remove previous selection
                        document.querySelectorAll('.time-slot-btn').forEach(b => {
                            b.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                            b.classList.add('border-gray-300');
                        });
                        
                        // Add selection to current
                        this.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
                        this.classList.remove('border-gray-300');
                        
                        selectedTime = this.dataset.time;
                        updateSessionSummary();
                    });
                });
            }

            function updateSessionSummary() {
                if (selectedDate && selectedTime) {
                    const duration = document.getElementById('duration').value;
                    const sessionType = document.getElementById('session_type').value;
                    const priceInfo = sessionPrices[duration];

                    document.getElementById('selected-date-display').textContent = new Date(selectedDate).toLocaleDateString();
                    document.getElementById('selected-time-display').textContent = selectedTime;
                    document.getElementById('selected-duration-display').textContent = priceInfo.name;
                    document.getElementById('selected-price-display').textContent = `£${priceInfo.price.toFixed(2)}`;

                    sessionSummary.classList.remove('hidden');
                    bookBtn.disabled = false;
                } else {
                    sessionSummary.classList.add('hidden');
                    bookBtn.disabled = true;
                }
            }

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!selectedDate || !selectedTime) {
                    alert('Please select a date and time for the session.');
                    return;
                }

                // Show loading state
                bookBtn.disabled = true;
                bookBtnText.textContent = 'Processing...';
                bookBtnSpinner.classList.remove('hidden');

                const formData = new FormData(form);
                formData.append('selected_date', selectedDate);
                formData.append('selected_time', selectedTime);

                fetch('{{ route("teacher.booking.process", ["student" => $student->id]) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect_url;
                    } else if (data.conflicting_session) {
                        alert(data.error + ' The calendar will refresh to show current availability.');
                        location.reload(); // Refresh the page to show updated availability
                    } else if (data.payment_failed || data.needs_payment) {
                        alert(data.message);
                        window.location.href = data.redirect_url;
                    } else {
                        alert(data.message || data.error || 'An error occurred. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                })
                .finally(() => {
                    // Reset button state
                    bookBtn.disabled = false;
                    bookBtnText.textContent = 'Book Session for {{ $student->name }}';
                    bookBtnSpinner.classList.add('hidden');
                });
            });
        });
    </script>

    <style>
        .date-btn:disabled {
            cursor: not-allowed !important;
        }
        
        .time-slot-btn:hover:not(.bg-blue-600) {
            background-color: #eff6ff;
            border-color: #93c5fd;
        }
        
        /* Custom scrollbar styling for time slots */
        #time-slots {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }
        
        #time-slots::-webkit-scrollbar {
            width: 6px;
        }
        
        #time-slots::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        #time-slots::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        #time-slots::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        #time-slots {
            max-height: 500px !important;
        }
    </style>
</x-app-layout>
