<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Bulk Bed Allocation') }}
            </h2>
            <a href="{{ route('student.hostel.allocations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Back to Allocations
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Step Indicator -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="progress-bar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: 33%"></div>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <div>Step 1: Select House & Criteria</div>
                    <div>Step 2: Match Students</div>
                    <div>Step 3: Confirm Allocations</div>
                </div>
            </div>
            
            <!-- Step 1: House Selection and Criteria -->
            <div id="step1" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Select House and Allocation Criteria</h3>
                    
                    <form id="step1-form" class="space-y-6">
                        <!-- House Selection -->
                        <div>
                            <x-label for="house_id" :value="__('House')" />
                            <select id="house_id" name="house_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="">Select House</option>
                                @foreach($houses as $house)
                                    <option value="{{ $house->id }}">{{ $house->name }} ({{ $house->code }}) - {{ ucfirst($house->gender) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Academic Year -->
                        <div>
                            <x-label for="academic_year" :value="__('Academic Year')" />
                            <select id="academic_year" name="academic_year" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year }}">{{ $year }}-{{ $year + 1 }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Allocation Period -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label for="start_date" :value="__('Start Date')" />
                                <x-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $defaultStartDate)" required />
                            </div>
                            
                            <div>
                                <x-label for="end_date" :value="__('End Date')" />
                                <x-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $defaultEndDate)" required />
                            </div>
                        </div>
                        
                        <!-- Student Criteria -->
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Student Selection Criteria</h4>
                            
                            <div class="space-y-4 bg-gray-50 p-4 rounded-md">
                                <div>
                                    <x-label for="year_of_study" :value="__('Year of Study')" />
                                    <select id="year_of_study" name="year_of_study" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">All Years</option>
                                        <option value="1">First Year</option>
                                        <option value="2">Second Year</option>
                                        <option value="3">Third Year</option>
                                        <option value="4">Fourth Year</option>
                                        <option value="5">Fifth Year</option>
                                        <option value="6">Sixth Year</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <x-label for="program" :value="__('Program/Course')" />
                                    <select id="program" name="program" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">All Programs</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program }}">{{ $program }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <x-label for="additional_criteria" :value="__('Additional Criteria (Optional)')" />
                                    <textarea id="additional_criteria" name="additional_criteria" rows="2" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" placeholder="Any special criteria or notes"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="button" id="next-to-step2" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Next Step
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Step 2: Match Students to Beds -->
            <div id="step2" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 hidden">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Match Students to Beds</h3>
                    
                    <div class="mb-4">
                        <div class="flex justify-between items-center bg-gray-50 p-4 rounded-md mb-4">
                            <div>
                                <span class="text-gray-700 font-medium">Selected House:</span>
                                <span id="selected-house" class="ml-2"></span>
                            </div>
                            <div>
                                <span class="text-gray-700 font-medium">Available Beds:</span>
                                <span id="available-beds-count" class="ml-2"></span>
                            </div>
                            <div>
                                <span class="text-gray-700 font-medium">Eligible Students:</span>
                                <span id="eligible-students-count" class="ml-2"></span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Available Beds -->
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Available Beds</h4>
                                <div class="bg-gray-50 p-3 rounded-md max-h-96 overflow-y-auto" id="available-beds-container">
                                    <div class="flex justify-center items-center h-32 text-gray-400">
                                        Please complete Step 1 first to see available beds
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Eligible Students -->
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Eligible Students</h4>
                                <div class="bg-gray-50 p-3 rounded-md max-h-96 overflow-y-auto" id="eligible-students-container">
                                    <div class="flex justify-center items-center h-32 text-gray-400">
                                        Please complete Step 1 first to see eligible students
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h4 class="font-medium text-gray-700 mb-2">Matching Options</h4>
                        <div class="space-y-4 bg-gray-50 p-4 rounded-md">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-label for="allocation-method" :value="__('Allocation Method')" />
                                    <select id="allocation-method" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="manual">Manual Matching</option>
                                        <option value="auto-random">Auto - Random Assignment</option>
                                        <option value="auto-priority">Auto - By Priority</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <x-label for="priority-field" :value="__('Priority By (for Auto)')" />
                                    <select id="priority-field" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="year">Year of Study (Higher First)</option>
                                        <option value="gpa">Academic Performance</option>
                                        <option value="distance">Distance from Home</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-end">
                                    <button type="button" id="apply-matching" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        Apply Matching
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h4 class="font-medium text-gray-700 mb-2">Current Matches</h4>
                        <div id="matches-container" class="bg-gray-50 p-4 rounded-md min-h-40">
                            <div class="text-center text-gray-400 py-8">
                                No matches created yet
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-6">
                        <button type="button" id="back-to-step1" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Previous Step
                        </button>
                        <button type="button" id="next-to-step3" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Next Step
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Step 3: Confirm and Process Allocations -->
            <div id="step3" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 hidden">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Confirm and Process Allocations</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-md mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Selected House</div>
                                <div id="confirm-house" class="font-medium"></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Allocation Period</div>
                                <div id="confirm-period" class="font-medium"></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Total Allocations</div>
                                <div id="confirm-total" class="font-medium"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-700 mb-2">Allocation Summary</h4>
                        <div id="allocation-summary" class="max-h-96 overflow-y-auto bg-white border border-gray-200 rounded-md">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bed</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="allocation-tbody" class="bg-white divide-y divide-gray-200">
                                    <!-- Allocation rows will be inserted here by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-700 mb-2">Additional Options</h4>
                        <div class="bg-gray-50 p-4 rounded-md space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-label for="payment_status" :value="__('Default Payment Status')" />
                                    <select id="payment_status" name="payment_status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="pending">Pending</option>
                                        <option value="partial">Partial</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <x-label for="allocation_status" :value="__('Default Allocation Status')" />
                                    <select id="allocation_status" name="allocation_status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="pending">Pending</option>
                                        <option value="active">Active</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <x-label for="bulk_notes" :value="__('Notes (Optional)')" />
                                <textarea id="bulk_notes" name="bulk_notes" rows="2" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full"></textarea>
                            </div>
                            
                            <div class="flex items-center">
                                <input id="send_notification" name="send_notification" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="send_notification" class="ml-2 block text-sm text-gray-900">Send email notification to students</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form for submitting the final data -->
                    <form id="bulk-allocation-form" method="POST" action="{{ route('student.hostel.allocations.process-bulk') }}">
                        @csrf
                        <input type="hidden" id="allocation_data" name="allocation_data" value="">
                        <input type="hidden" id="form_payment_status" name="payment_status" value="pending">
                        <input type="hidden" id="form_allocation_status" name="allocation_status" value="pending">
                        <input type="hidden" id="form_bulk_notes" name="bulk_notes" value="">
                        <input type="hidden" id="form_send_notification" name="send_notification" value="0">
                        
                        <!-- Navigation Buttons -->
                        <div class="flex justify-between mt-6">
                            <button type="button" id="back-to-step2" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Previous Step
                            </button>
                            <button type="submit" id="process-allocations" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Process Allocations
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Step navigation elements
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const step3 = document.getElementById('step3');
            const progressBar = document.getElementById('progress-bar');
            
            // Navigation buttons
            const nextToStep2Btn = document.getElementById('next-to-step2');
            const backToStep1Btn = document.getElementById('back-to-step1');
            const nextToStep3Btn = document.getElementById('next-to-step3');
            const backToStep2Btn = document.getElementById('back-to-step2');
            
            // Form elements from step 1
            const houseIdSelect = document.getElementById('house_id');
            const academicYearSelect = document.getElementById('academic_year');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            // Step 1 to Step 2
            nextToStep2Btn.addEventListener('click', function() {
                // Validate step 1 inputs
                if (!houseIdSelect.value || !academicYearSelect.value || !startDateInput.value || !endDateInput.value) {
                    alert('Please fill in all required fields before proceeding.');
                    return;
                }
                
                // Update house name in step 2
                const selectedHouseText = houseIdSelect.options[houseIdSelect.selectedIndex].text;
                document.getElementById('selected-house').textContent = selectedHouseText;
                
                // Show step 2, hide step 1
                step1.classList.add('hidden');
                step2.classList.remove('hidden');
                progressBar.style.width = '66%';
                
                // Here you would typically load the available beds and eligible students via AJAX
                // For demonstration, we're just showing placeholder content
                fetchAvailableBedsAndStudents();
            });
            
            // Step 2 back to Step 1
            backToStep1Btn.addEventListener('click', function() {
                step2.classList.add('hidden');
                step1.classList.remove('hidden');
                progressBar.style.width = '33%';
            });
            
            // Step 2 to Step 3
            nextToStep3Btn.addEventListener('click', function() {
                // Validate we have some matches
                const matchesContainer = document.getElementById('matches-container');
                if (matchesContainer.querySelector('.text-gray-400')) {
                    alert('Please create at least one match before proceeding.');
                    return;
                }
                
                // Update confirmation info
                document.getElementById('confirm-house').textContent = document.getElementById('selected-house').textContent;
                document.getElementById('confirm-period').textContent = `${startDateInput.value} to ${endDateInput.value}`;
                
                // Count total allocations and populate summary table
                const allocationRows = document.querySelectorAll('#matches-container .allocation-match');
                document.getElementById('confirm-total').textContent = allocationRows.length;
                
                // Populate the allocation summary table
                const allocationTbody = document.getElementById('allocation-tbody');
                allocationTbody.innerHTML = ''; // Clear existing rows
                
                allocationRows.forEach(row => {
                    const studentName = row.getAttribute('data-student-name');
                    const studentId = row.getAttribute('data-student-id');
                    const roomNumber = row.getAttribute('data-room-number');
                    const bedNumber = row.getAttribute('data-bed-number');
                    const bedId = row.getAttribute('data-bed-id');
                    const studentDataId = row.getAttribute('data-student-data-id');
                    
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${studentName}</div>
                            <div class="text-xs text-gray-500">${studentId}</div>
                            <input type="hidden" name="student_id[]" value="${studentDataId}">
                            <input type="hidden" name="bed_id[]" value="${bedId}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${roomNumber}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${bedNumber}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ready</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-allocation" data-row-id="${studentDataId}-${bedId}">Remove</button>
                        </td>
                    `;
                    allocationTbody.appendChild(tr);
                });
                
                // Add event listeners to remove buttons in the summary table
                document.querySelectorAll('.remove-allocation').forEach(button => {
                    button.addEventListener('click', function() {
                        const rowId = this.getAttribute('data-row-id');
                        // Remove from summary table
                        this.closest('tr').remove();
                        // Update count
                        const remainingRows = document.querySelectorAll('#allocation-tbody tr').length;
                        document.getElementById('confirm-total').textContent = remainingRows;
                    });
                });
                
                // Show step 3, hide step 2
                step2.classList.add('hidden');
                step3.classList.remove('hidden');
                progressBar.style.width = '100%';
            });
            
            // Step 3 back to Step 2
            backToStep2Btn.addEventListener('click', function() {
                step3.classList.add('hidden');
                step2.classList.remove('hidden');
                progressBar.style.width = '66%';
            });
            
            // Update hidden form fields before submission
            document.getElementById('bulk-allocation-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Collect all allocation data from the table
                const allocations = [];
                document.querySelectorAll('#allocation-tbody tr').forEach(row => {
                    const studentId = row.querySelector('input[name="student_id[]"]').value;
                    const bedId = row.querySelector('input[name="bed_id[]"]').value;
                    allocations.push({
                        student_id: studentId,
                        bed_id: bedId
                    });
                });
                
                if (allocations.length === 0) {
                    alert('No allocations to process. Please go back and create some matches first.');
                    return;
                }
                
                // Set allocation data in hidden field as JSON
                document.getElementById('allocation_data').value = JSON.stringify(allocations);
                
                // Set other form values
                document.getElementById('form_payment_status').value = document.getElementById('payment_status').value;
                document.getElementById('form_allocation_status').value = document.getElementById('allocation_status').value;
                document.getElementById('form_bulk_notes').value = document.getElementById('bulk_notes').value;
                document.getElementById('form_send_notification').value = document.getElementById('send_notification').checked ? '1' : '0';
                
                // Submit the form
                this.submit();
            });
        });
    </script>
</x-app-layout>