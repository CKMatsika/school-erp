<!-- resources/views/student/enrollments/by_year.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Enrollments') }}: {{ $academicYear->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('student.enrollments.create', ['academic_year_id' => $academicYear->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Enrollment
                </a>
                <a href="{{ route('student.enrollments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>
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

            <!-- Enrollment Stats -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Academic Year Details</h3>
                            <div class="mt-2 text-sm text-gray-600">
                                <p><strong>Name:</strong> {{ $academicYear->name }}</p>
                                <p><strong>Period:</strong> {{ $academicYear->start_date->format('M d, Y') }} - {{ $academicYear->end_date->format('M d, Y') }}</p>
                                <p><strong>Status:</strong> 
                                    @if($academicYear->is_current)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Current
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Enrollment Summary</h3>
                            <div class="mt-2">
                                <div class="flex items-center">
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Total Students:</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ $enrollments->count() }}</p>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Classes:</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ $enrollments->groupBy('class_id')->count() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Gender Distribution</h3>
                            <div class="mt-2">
                                <div class="flex items-center">
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Male:</p>
                                        <p class="text-2xl font-bold text-blue-600">{{ $enrollments->where('student.gender', 'male')->count() }}</p>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Female:</p>
                                        <p class="text-2xl font-bold text-pink-600">{{ $enrollments->where('student.gender', 'female')->count() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Boarding Status</h3>
                            <div class="mt-2">
                                <div class="flex items-center">
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Boarders:</p>
                                        <p class="text-2xl font-bold text-indigo-600">{{ $enrollments->filter(function($e) { return $e->student->is_boarder; })->count() }}</p>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600">Day Students:</p>
                                        <p class="text-2xl font-bold text-green-600">{{ $enrollments->filter(function($e) { return !$e->student->is_boarder; })->count() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('student.enrollments.showByYear', $academicYear) }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                    placeholder="Name, Admission #" 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                                <select name="class_id" id="class_id" 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">All Classes</option>
                                    @foreach($enrollments->groupBy('class_id') as $classId => $classEnrollments)
                                        <option value="{{ $classId }}" {{ request('class_id') == $classId ? 'selected' : '' }}>
                                            {{ $classEnrollments->first()->class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">All Statuses</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="withdrawn" {{ request('status') == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                    <option value="transferred" {{ request('status') == 'transferred' ? 'selected' : '' }}>Transferred</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enrollment Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">
                            Enrolled Students
                        </h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('student.enrollments.export', ['academic_year_id' => $academicYear->id, 'format' => 'csv']) }}" 
                                class="text-sm text-indigo-600 hover:text-indigo-900">Export CSV</a>
                            <a href="{{ route('student.enrollments.export', ['academic_year_id' => $academicYear->id, 'format' => 'excel']) }}" 
                                class="text-sm text-indigo-600 hover:text-indigo-900">Export Excel</a>
                            <a href="{{ route('student.enrollments.export', ['academic_year_id' => $academicYear->id, 'format' => 'pdf']) }}" 
                                class="text-sm text-indigo-600 hover:text-indigo-900">Export PDF</a>
                        </div>
                    </div>
                    
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
                </div>
            </div>
        </div>
    </div>
</x-app-layout>