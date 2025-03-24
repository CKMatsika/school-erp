<!-- resources/views/student/promotions/preview-graduation.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Graduation Preview') }}
            </h2>
            <a href="{{ route('student.promotions.graduate') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Graduation Summary Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Graduation Summary</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Graduating Class:</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $class->name }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">Academic Year:</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $academicYear->name }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 bg-purple-50 border-l-4 border-purple-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-purple-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-purple-700">
                                    <strong>Important:</strong> You are about to mark students in <strong>{{ $class->name }}</strong> 
                                    as graduated for the academic year <strong>{{ $academicYear->name }}</strong>. This action will update student status
                                    to 'graduated' and mark current enrollments as completed. This action cannot be easily undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Selection Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('student.promotions.processGraduation') }}" method="POST">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $class->id }}">
                        <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                        
                        <div class="mb-4 flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">Select Students to Graduate</h3>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" id="select-all" class="form-checkbox h-5 w-5 text-indigo-600 transition duration-150 ease-in-out">
                                    <span class="ml-2 text-sm text-gray-700">Select All</span>
                                </label>
                            </div>
                        </div>
                        
                        @if($students->isEmpty())
                            <div class="bg-gray-50 p-4 rounded-md text-center">
                                <p class="text-gray-700">No eligible students found in {{ $class->name }} for the current academic year.</p>
                            </div>
                        @else
                            <div class="mb-6">
                                <div class="flex items-center space-x-2 mb-4">
                                    <label for="graduation_date" class="text-sm font-medium text-gray-700">Graduation Date:</label>
                                    <input type="date" name="graduation_date" id="graduation_date" required value="{{ date('Y-m-d') }}" 
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">
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
                                                Enrollment Date
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Years at School
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Performance
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
                                                                {{ ucfirst($student->gender) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $student->admission_number }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $student->enrollment_date ? $student->enrollment_date->format('M d, Y') : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @php
                                                        $yearsAtSchool = $student->enrollment_date ? $student->enrollment_date->diffInYears(now()) : 'N/A';
                                                    @endphp
                                                    {{ $yearsAtSchool }} {{ is_numeric($yearsAtSchool) ? Str::plural('year', $yearsAtSchool) : '' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @php
                                                        // Placeholder for actual student performance data
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
                                <a href="{{ route('student.promotions.graduate') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                                    Cancel
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                    </svg>
                                    Graduate Selected Students
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