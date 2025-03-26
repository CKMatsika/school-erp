<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Accommodation Details') }}: {{ $student->name }}
            </h2>
            <a href="{{ route('student.hostel.students.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Back to Students
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Student Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Student Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <dl>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                    <dt class="text-sm font-medium text-gray-500">Student Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->name }}</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Student ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->student_id }}</dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->email }}</dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div>
                            <dl>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                    <dt class="text-sm font-medium text-gray-500">Program</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->program }}</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Year of Study</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->year_of_study }}</dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->phone ?: 'Not provided' }}</dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div>
                            <dl>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                    <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ ucfirst($student->gender) }}</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : 'Not provided' }}</dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Nationality</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->nationality ?: 'Not provided' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Current Accommodation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Current Accommodation</h3>
                        @if(!$currentAllocation)
                            <a href="{{ route('student.hostel.allocations.create', ['student_id' => $student->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Allocate Bed
                            </a>
                        @endif
                    </div>
                    
                    @if(!$currentAllocation)
                        <div class="bg-gray-50 p-4 rounded-md text-center">
                            <p class="text-gray-500">This student is not currently allocated to a bed.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dl>
                                    <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                        <dt class="text-sm font-medium text-gray-500">House</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $currentAllocation->bed->room->house->name }} ({{ $currentAllocation->bed->room->house->code }})</dd>
                                    </div>
                                    <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Room</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            <a href="{{ route('student.hostel.rooms.show', $currentAllocation->bed->room->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $currentAllocation->bed->room->room_number }}
                                            </a>
                                        </dd>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Bed</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            <a href="{{ route('student.hostel.beds.show', $currentAllocation->bed->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $currentAllocation->bed->bed_number }} ({{ ucfirst(str_replace('_', ' ', $currentAllocation->bed->bed_type)) }})
                                            </a>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div>
                                <dl>
                                    <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                        <dt class="text-sm font-medium text-gray-500">Period</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $currentAllocation->start_date->format('M d, Y') }} - {{ $currentAllocation->end_date->format('M d, Y') }}</dd>
                                    </div>
                                    <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1 sm:mt-0 sm:col-span-2">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($currentAllocation->status === 'active') bg-green-100 text-green-800 
                                                @elseif($currentAllocation->status === 'pending') bg-yellow-100 text-yellow-800 
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($currentAllocation->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Payment</dt>
                                        <dd class="mt-1 sm:mt-0 sm:col-span-2">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($currentAllocation->payment_status === 'paid') bg-green-100 text-green-800 
                                                @elseif($currentAllocation->payment_status === 'partial') bg-blue-100 text-blue-800 
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($currentAllocation->payment_status) }}
                                            </span>
                                            
                                            @if($currentAllocation->payment_status !== 'paid')
                                                <a href="{{ route('student.hostel.payments.create', ['allocation_id' => $currentAllocation->id]) }}" class="ml-2 text-sm text-indigo-600 hover:text-indigo-900">
                                                    Record Payment
                                                </a>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                Allocated on {{ $currentAllocation->created_at->format('M d, Y') }}
                            </div>
                            <div>
                                <a href="{{ route('student.hostel.allocations.show', $currentAllocation->id) }}" class="inline-flex items-center px-3 py-1 bg-indigo-100 border border-transparent rounded-md font-medium text-sm text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    View Full Details
                                </a>
                                
                                <form method="POST" action="{{ route('student.hostel.allocations.terminate', $currentAllocation->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to terminate this allocation? The student will be removed from this bed.');">
                                    @csrf
                                    <button type="submit" class="ml-2 inline-flex items-center px-3 py-1 bg-red-100 border border-transparent rounded-md font-medium text-sm text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Terminate Allocation
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Allocation History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Allocation History</h3>
                    
                    @if($allocationHistory->isEmpty())
                        <div class="text-gray-500 text-center py-4">No previous allocation history found for this student.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Year</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">House/Room</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($allocationHistory as $allocation)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $allocation->academic_year }}-{{ $allocation->academic_year + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $allocation->bed->room->house->name }}</div>
                                            <div class="text-xs text-gray-500">Room {{ $allocation->bed->room->room_number }}, Bed {{ $allocation->bed->bed_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $allocation->start_date->format('M d, Y') }} - {{ $allocation->end_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($allocation->status === 'active') bg-green-100 text-green-800 
                                                @elseif($allocation->status === 'pending') bg-yellow-100 text-yellow-800 
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($allocation->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($allocation->payment_status === 'paid') bg-green-100 text-green-800 
                                                @elseif($allocation->payment_status === 'partial') bg-blue-100 text-blue-800 
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($allocation->payment_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('student.hostel.allocations.show', $allocation->id) }}" class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Maintenance Issues -->
            <div class="mt-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 bg-white border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-700">Maintenance Issues Reported</h3>
                        <a href="{{ route('student.hostel.maintenance.create') }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:border-indigo-300 focus:ring ring-indigo-300 active:bg-indigo-200 transition ease-in-out duration-150">
                            Report Issue
                        </a>
                    </div>
                    <div class="p-6">
                        @if($maintenanceIssues->isEmpty())
                            <div class="text-gray-500 text-center py-4">No maintenance issues reported by this student.</div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported On</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($maintenanceIssues as $issue)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $issue->title }}</div>
                                                <div class="text-xs text-gray-500">{{ ucfirst($issue->category) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $issue->house->name }}{{ $issue->room ? ', Room '.$issue->room->room_number : '' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($issue->status === 'open') bg-red-100 text-red-800
                                                    @elseif($issue->status === 'in_progress') bg-yellow-100 text-yellow-800
                                                    @else bg-green-100 text-green-800 @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $issue->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('student.hostel.maintenance.show', $issue->id) }}" class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>