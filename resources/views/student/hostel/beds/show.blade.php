{{-- resources/views/student/hostel/beds/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Bed Details') }}: {{ $bed->bed_number }}
            </h2>
            <div>
                <a href="{{ route('student.hostel.beds.edit', $bed->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    Edit Bed
                </a>
                <a href="{{ route('student.hostel.beds.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to Beds
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Bed Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Bed Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dl>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                    <dt class="text-sm font-medium text-gray-500">Bed Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $bed->bed_number }}</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $bed->description ?: 'No description provided' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Occupant Information (if occupied) -->
            @if($bed->status === 'occupied' && $bed->student)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Current Occupant</h3>
                        <a href="{{ route('student.hostel.allocations.edit', $bed->allocation->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Manage Allocation
                        </a>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-xl font-semibold text-gray-600">{{ substr($bed->student->name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="text-lg font-medium">{{ $bed->student->name }}</div>
                                <div class="text-sm text-gray-500">ID: {{ $bed->student->student_id }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Allocated since:</div>
                                <div class="font-medium">{{ $bed->allocation->start_date->format('M d, Y') }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">End date:</div>
                                <div class="font-medium">{{ $bed->allocation->end_date->format('M d, Y') }}</div>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Program/Course</div>
                                <div>{{ $bed->student->program }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Year of Study</div>
                                <div>{{ $bed->student->year_of_study }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Contact</div>
                                <div>{{ $bed->student->email }}</div>
                                <div>{{ $bed->student->phone }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Bed Allocation</h3>
                        @if($bed->status === 'available')
                        <a href="{{ route('student.hostel.allocations.create', ['bed_id' => $bed->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Allocate Bed
                        </a>
                        @endif
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-md">
                        @if($bed->status === 'available')
                            <p class="text-center text-gray-500 py-4">This bed is currently unoccupied and available for allocation.</p>
                        @elseif($bed->status === 'maintenance')
                            <p class="text-center text-yellow-600 py-4">This bed is currently under maintenance and not available for allocation.</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Allocation History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Allocation History</h3>
                    
                    @if($allocationHistory->isEmpty())
                        <div class="text-gray-500 text-center py-4">No allocation history found for this bed.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($allocationHistory as $allocation)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $allocation->student->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $allocation->student->student_id }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $allocation->start_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $allocation->end_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($allocation->is_active) bg-green-100 text-green-800 
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $allocation->is_active ? 'Active' : 'Completed' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $allocation->notes ?: '-' }}
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
</x-app-layout>sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Room</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <a href="{{ route('student.hostel.rooms.show', $bed->room->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $bed->room->room_number }}
                                        </a>
                                    </dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">House</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $bed->room->house->name }} ({{ $bed->room->house->code }})</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Bed Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ ucfirst(str_replace('_', ' ', $bed->bed_type)) }}</dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div>
                            <dl>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($bed->status === 'available') bg-green-100 text-green-800 
                                            @elseif($bed->status === 'occupied') bg-red-100 text-red-800 
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ ucfirst($bed->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Cost</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        ${{ number_format($bed->cost, 2) }} per academic year
                                    </dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $bed->updated_at->format('M d, Y H:i') }}</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3