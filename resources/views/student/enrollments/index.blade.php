<!-- resources/views/student/enrollments/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Enrollments') }}
            </h2>
            <a href="{{ route('student.enrollments.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Enrollment
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Academic Year Selector -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Select Academic Year</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($academicYears as $year)
                            <a href="{{ route('student.enrollments.showByYear', $year) }}" 
                               class="inline-flex items-center px-3 py-2 border {{ $currentYear && $currentYear->id === $year->id ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }} rounded-md text-sm font-medium">
                                {{ $year->name }}
                                @if($year->is_current)
                                    <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        Current
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Current Enrollments -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">
                            @if($currentYear)
                                Current Enrollments: {{ $currentYear->name }}
                            @else
                                No Academic Year Selected
                            @endif
                        </h3>
                        @if($currentYear)
                            <div class="flex space-x-2">
                                <a href="{{ route('student.enrollments.export', ['academic_year_id' => $currentYear->id, 'format' => 'csv']) }}" 
                                   class="text-sm text-indigo-600 hover:text-indigo-900">Export CSV</a>
                                <a href="{{ route('student.enrollments.export', ['academic_year_id' => $currentYear->id, 'format' => 'excel']) }}" 
                                   class="text-sm text-indigo-600 hover:text-indigo-900">Export Excel</a>
                                <a href="{{ route('student.enrollments.export', ['academic_year_id' => $currentYear->id, 'format' => 'pdf']) }}" 
                                   class="text-sm text-indigo-600 hover:text-indigo-900">Export PDF</a>
                            </div>
                        @endif
                    </div>
                    
                    @if($currentYear)
                        <!-- Class enrollment summary cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
                            @foreach($enrollments->groupBy('class_id') as $classId => $classEnrollments)
                                @php
                                    $class = $classEnrollments->first()->class;
                                @endphp
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900">{{ $class->name }}</h4>
                                            <div class="mt-1 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                                </svg>
                                                <span class="text-sm text-gray-500">{{ $classEnrollments->count() }} Students</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $classEnrollments->where('student.gender', 'male')->count() }} M / 
                                                {{ $classEnrollments->where('student.gender', 'female')->count() }} F
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <a href="{{ route('student.classes.show', $class) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                            View Class
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Enrollment Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admission #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollment Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($enrollments as $enrollment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($enrollment->student->photo)
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full object-cover" 
                                                             src="{{ asset('storage/' . $enrollment->student->photo) }}" 
                                                             alt="{{ $enrollment->student->first_name }}">
                                                    </div>
                                                @else
                                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="ml-4">
                                                    <a href="{{ route('student.students.show', $enrollment->student) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                        {{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}
                                                    </a>
                                                    <div class="text-xs text-gray-500">
                                                        {{ ucfirst($enrollment->student->gender) }} | 
                                                        {{ $enrollment->student->date_of_birth ? $enrollment->student->date_of_birth->age . ' years' : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $enrollment->student->admission_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $enrollment->class->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $enrollment->enrollment_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $enrollment->getStatusColorClass() }}">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                            @if($enrollment->student->is_boarder)
                                                <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Boarder
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('student.enrollments.edit', $enrollment) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <a href="{{ route('student.students.show', $enrollment->student) }}" class="text-blue-600 hover:text-blue-900">View Student</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No enrollments found for this academic year.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No academic year selected</h3>
                            <p class="mt-1 text-sm text-gray-500">Please select an academic year from above to view enrollments.</p>
                            <div class="mt-6">
                                @if($academicYears->isEmpty())
                                    <a href="{{ route('student.academic-years.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Create Academic Year
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>