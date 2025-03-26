{{-- resources/views/student/hostel/beds/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Beds') }}
            </h2>
            <a href="{{ route('student.hostel.beds.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                Add New Bed
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('student.hostel.beds.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                                <label for="room" class="block text-sm font-medium text-gray-700 mb-1">Filter by Room</label>
                                <select id="room" name="room" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                    <option value="">All Rooms</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ request('room') == $room->id ? 'selected' : '' }}>
                                            {{ $room->room_number }} ({{ $room->house->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="bed_type" class="block text-sm font-medium text-gray-700 mb-1">Filter by Bed Type</label>
                                <select id="bed_type" name="bed_type" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                    <option value="">All Types</option>
                                    <option value="single" {{ request('bed_type') == 'single' ? 'selected' : '' }}>Single</option>
                                    <option value="bunk_upper" {{ request('bed_type') == 'bunk_upper' ? 'selected' : '' }}>Bunk (Upper)</option>
                                    <option value="bunk_lower" {{ request('bed_type') == 'bunk_lower' ? 'selected' : '' }}>Bunk (Lower)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                                <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                    <option value="">All Status</option>
                                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
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

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Total Beds</h3>
                    <p class="text-3xl font-bold">{{ $totalBeds }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Available Beds</h3>
                    <p class="text-3xl font-bold">{{ $availableBeds }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Occupancy Rate</h3>
                    <p class="text-3xl font-bold">{{ $occupancyRate }}%</p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $occupancyRate }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Bed List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Bed List</h3>
                    
                    @if($beds->isEmpty())
                        <div class="text-gray-500 text-center py-4">No beds match the selected filters.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bed Number</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">House</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $bed->room->room_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $bed->room->house->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $bed->room->house->code }}</div>
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
                        
                        <div class="mt-4">
                            {{ $beds->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>