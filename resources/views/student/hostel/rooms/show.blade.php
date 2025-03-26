{{-- resources/views/student/hostel/rooms/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Room Details') }}: {{ $room->room_number }}
            </h2>
            <div>
                <a href="{{ route('student.hostel.rooms.edit', $room->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    Edit Room
                </a>
                <a href="{{ route('student.hostel.rooms.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to Rooms
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Room Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Room Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dl>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                    <dt class="text-sm font-medium text-gray-500">Room Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $room->room_number }}</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">House</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $room->house->name }} ({{ $room->house->code }})</dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Floor</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $room->floor }}</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Room Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ ucfirst($room->room_type) }}</dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div>
                            <dl>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                    <dt class="text-sm font-medium text-gray-500">Capacity</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $room->capacity }} beds</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Available Beds</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $room->available_beds }} beds</dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($room->status === 'available') bg-green-100 text-green-800 
                                            @elseif($room->status === 'full') bg-red-100 text-red-800 
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ ucfirst($room->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $room->description ?: 'No description provided' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Beds in Room -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Beds in Room</h3>
                        <a href="{{ route('student.hostel.beds.create', ['room_id' => $room->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Add Bed
                        </a>
                    </div>
                    
                    @if($beds->isEmpty())
                        <div class="text-gray-500 text-center py-4">No beds have been added to this room yet.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bed Number</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bed Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupant</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($beds as $bed)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $bed->bed_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ ucfirst(str_replace('_', ' ', $bed->bed_type)) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($bed->status === 'available') bg-green-100 text-green-800 
                                                @elseif($bed->status === 'occupied') bg-red-100 text-red-800 
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($bed->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($bed->status === 'occupied' && $bed->student)
                                                {{ $bed->student->name }} ({{ $bed->student->student_id }})
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('student.hostel.beds.show', $bed->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">View</a>
                                            <a href="{{ route('student.hostel.beds.edit', $bed->id) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">Edit</a>
                                            <form class="inline" action="{{ route('student.hostel.beds.destroy', $bed->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this bed?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
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
</x-app-layout>