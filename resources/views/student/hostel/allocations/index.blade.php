<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Hostel Allocations') }}
        </h2>
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

            <!-- Students Pending Allocation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Students Pending Allocation</h3>
                        <a href="{{ route('student.hostel.bulk-allocate') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Bulk Allocate
                        </a>
                    </div>
                    
                    @if($pendingStudents->isEmpty())
                        <div class="text-gray-500 text-center py-4">No students are currently pending allocation.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admission #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pendingStudents as $student)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($student->photo)
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->first_name }}">
                                                    </div>
                                                @else
                                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $student->first_name }} {{ $student->last_name }}
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
                                            {{ $student->class->name ?? 'Not Assigned' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('student.hostel.allocations.create', $student) }}" class="text-indigo-600 hover:text-indigo-900">
                                                Allocate Bed
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $pendingStudents->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Current Allocations -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Current Allocations</h3>
                    
                    @if($currentAllocations->isEmpty())
                        <div class="text-gray-500 text-center py-4">No current allocations found.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">House</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bed</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocation Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($currentAllocations as $allocation)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($allocation->student->photo)
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $allocation->student->photo) }}" alt="{{ $allocation->student->first_name }}">
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
                                                        {{ $allocation->student->first_name }} {{ $allocation->student->last_name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $allocation->student->admission_number }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $allocation->bed->room->house->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $allocation->bed->room->room_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $allocation->bed->bed_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $allocation->allocation_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('student.hostel.allocations.history', $allocation->student) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                History
                                            </a>
                                            <a href="{{ route('student.hostel.allocations.end', $allocation->student) }}" class="text-red-600 hover:text-red-900">
                                                End Allocation
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $currentAllocations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>