<!-- resources/views/student/promotions/preview.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Class Promotion Preview') }}
            </h2>
            <a href="{{ route('student.promotions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Promotion Summary Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Promotion Summary</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">From Class:</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $fromClass->name }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">To Class:</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $toClass->name }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">Current Academic Year:</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $currentAcademicYear->name }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">New Academic Year:</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $newAcademicYear->name }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Important:</strong> You are about to promote students from <strong>{{ $fromClass->name }}</strong> to <strong>{{ $toClass->name }}</strong> 
                                    for the academic year <strong>{{ $newAcademicYear->name }}</strong>. This action will mark current enrollments as completed 
                                    and create new enrollment records. This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Selection Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('student.promotions.promote') }}" method="POST">
                        @csrf
                        <input type="hidden" name="from_class_id" value="{{ $fromClass->id }}">
                        <input type="hidden" name="to_class_id" value="{{ $toClass->id }}">
                        <input type="hidden" name="current_academic_year_id" value="{{ $currentAcademicYear->id }}">
                        <input type="hidden" name="new_academic_year_id" value="{{ $newAcademicYear->id }}">
                        
                        <div class="mb-4 flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">Select Students to Promote</h3>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" id="select-all" class="form-checkbox h-5 w-5 text-indigo-600 transition duration-150 ease-in-out">
                                    <span class="ml-2 text-sm text-gray-700">Select All</span>
                                </label>
                            </div>
                        </div>
                        
                        @if($students->isEmpty())
                            <div class="bg-gray-50 p-4 rounded-md text-center">
                                <p class="text-gray-700">No eligible students found in {{ $fromClass->name }} for the current academic year.</p>
                            </div>
                        @else
                            <div class="mb-6">
                                <div class="flex items-center space-x-2 mb-4">
                                    <label for="promotion_date" class="text-sm font-medium text-gray-700">Promotion Date:</label>
                                    <input type="date" name="promotion_date" id="promotion_date" required value="{{ date('Y-m-d') }}" 
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">
                                </div>
                                
                                <div class="flex items-center space-x-2 mb-4">
                                    <input type="checkbox" name="is_graduation" id="is_graduation" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="is_graduation" class="text-sm font-medium text-gray-700">Is this a graduating class? (Students will be marked as graduated)</label>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Select
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Student
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Admission #
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Gender
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Boarding Status
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Academic Performance
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($students as $student)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" 
                                                        class="student-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        @if($student->photo)
                                                            <div class="flex-shrink-0 h-10 w-10">
                                                                <img class="h-10 w-10 rounded-full object-cover" 
                                                                    src="{{ asset('storage/' . $student->photo) }}" 
                                                                    alt="{{ $student->first_name }}">
                                                            </div>
                                                        @else
                                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                </svg>
                                                            </div>
                                                        @endif
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $student->first_name }} {{ $student->last_name }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $student->date_of_birth ? $student->date_of_birth->age . ' years' : 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $student->admission_number }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ ucfirst($student->gender) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $student->is_boarder ? 'Boarder' : 'Day Scholar' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @php
                                                        // You can integrate with your grading system here
                                                        $grades = ['Excellent', 'Good', 'Average', 'Below Average'];
                                                        $grade = $grades[array_rand($grades)];
                                                        
                                                        $gradeColors = [
                                                            'Excellent' => 'bg-green-100 text-green-800',
                                                            'Good' => 'bg-blue-100 text-blue-800',
                                                            'Average' => 'bg-yellow-100 text-yellow-800',
                                                            'Below Average' => 'bg-red-100 text-red-800'
                                                        ];
                                                    @endphp
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $gradeColors[$grade] }}">
                                                        {{ $grade }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <a href="{{ route('student.promotions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                                    Cancel
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Promote Selected Students
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Select all functionality
        const selectAllCheckbox = document.getElementById('select-all');
        const studentCheckboxes = document.querySelectorAll('.student-checkbox');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                studentCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
            
            // Update "select all" checkbox when individual checkboxes change
            studentCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(studentCheckboxes).every(c => c.checked);
                    const someChecked = Array.from(studentCheckboxes).some(c => c.checked);
                    
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = someChecked && !allChecked;
                });
            });
        }
    </script>
    @endpush
</x-app-layout>