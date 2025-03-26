{{-- resources/views/student/hostel/rooms/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Rooms') }}
            </h2>
            <a href="{{ route('student.hostel.rooms.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                Add New Room
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('student.hostel.rooms.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="house" class="block text-sm font-medium text-gray-700 mb-1">Filter by House</label>
                                <select id="house" name="house" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                    <option value="">All Houses</option>
                                    @foreach($houses as $house)
                                        <option value="{{ $house->id }}" {{ request('house') == $house->id ? 'selected' : '' }}>
                                            {{ $house->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="floor" class="block text-sm font-medium text-gray-700 mb-1">Filter by Floor</label>
                                <select id="floor" name="floor" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                    <option value="">All Floors</option>
                                    @foreach($floors as $floor)
                                        <option value="{{ $floor }}" {{ request('floor') == $floor ? 'selected' : '' }}>
                                            {{ $floor }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                                <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                    <option value="">All Status</option>
                                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="full" {{ request('status') == 'full' ? 'selected' : '' }}>Full</option>
                                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Room List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Room List</h3>
                    
                    @if($rooms->isEmpty())
                        <div class="text-gray-500 text-center py-4">No rooms match the selected filters.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Number</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">House</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Floor</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Beds</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($rooms as $room)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $room->room_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $room->house->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $room->house->code }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $room->floor }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $room->capacity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $room->available_beds }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($room->status === 'available') bg-green-100 text-green-800 
                                                @elseif($room->status === 'full') bg-red-100 text-red-800 
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($room->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('student.hostel.rooms.show', $room->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">View</a>
                                            <a href="{{ route('student.hostel.rooms.edit', $room->id) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">Edit</a>
                                            <form class="inline" action="{{ route('student.hostel.rooms.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this room?');">
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
                        
                        <div class="mt-4">
                            {{ $rooms->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>