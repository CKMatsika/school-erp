<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Hostel Occupancy Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('student.hostel.reports.occupancy') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Filter by Gender</label>
                                <select id="gender" name="gender" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                    <option value="all" {{ $genderFilter == 'all' ? 'selected' : '' }}>All Genders</option>
                                    <option value="male" {{ $genderFilter == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ $genderFilter == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="mixed" {{ $genderFilter == 'mixed' ? 'selected' : '' }}>Mixed</option>
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
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Total Capacity</h3>
                    <p class="text-3xl font-bold">{{ $totalBeds }}</p>
                    <p class="text-sm text-gray-500 mt-1">Total beds across all hostels</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Occupied Beds</h3>
                    <p class="text-3xl font-bold">{{ $totalOccupied }}</p>
                    <p class="text-sm text-gray-500 mt-1">Beds currently assigned</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Overall Occupancy</h3>
                    <p class="text-3xl font-bold">{{ $totalRate }}%</p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $totalRate }}%"></div>
                    </div>
                </div>
            </div>

            <!-- House Occupancy -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">House Occupancy Details</h3>
                    
                    @if($houses->isEmpty())
                        <div class="text-gray-500 text-center py-4">No houses match the selected filters.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">House</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupied</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupancy Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($houses as $house)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $house->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $house->code }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($house->gender == 'male') bg-blue-100 text-blue-800 
                                                @elseif($house->gender == 'female') bg-pink-100 text-pink-800 
                                                @else bg-purple-100 text-purple-800 @endif">
                                                {{ ucfirst($house->gender) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $house->total_beds }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $house->occupied_beds }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $house->total_beds - $house->occupied_beds }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-full">
                                                    <div class="text-sm text-gray-900 font-medium">{{ $house->occupancy_rate }}%</div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="h-2 rounded-full 
                                                            @if($house->occupancy_rate > 90) bg-red-600
                                                            @elseif($house->occupancy_rate > 75) bg-yellow-500
                                                            @elseif($house->occupancy_rate > 50) bg-green-500
                                                            @else bg-blue-500 @endif" 
                                                            style="width: {{ $house->occupancy_rate }}%">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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