<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Hostel Management Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Key Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Total Capacity</h3>
                            <p class="text-3xl font-bold">{{ $totalBeds }}</p>
                        </div>
                        <div class="h-12 w-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Total beds across all hostels</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Occupied Beds</h3>
                            <p class="text-3xl font-bold">{{ $occupiedBeds }}</p>
                        </div>
                        <div class="h-12 w-12 bg-red-100 rounded-full flex items-center justify-center text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Currently allocated beds</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Available Beds</h3>
                            <p class="text-3xl font-bold">{{ $availableBeds }}</p>
                        </div>
                        <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center text-green-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Beds available for allocation</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Occupancy Rate</h3>
                            <p class="text-3xl font-bold">{{ $occupancyRate }}%</p>
                        </div>
                        <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $occupancyRate }}%"></div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Pending Applications</h3>
                            <p class="text-3xl font-bold">{{ $pendingAllocations }}</p>
                        </div>
                        <div class="h-12 w-12 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Waiting for approval</p>
                </div>
            </div>
            
            <!-- Quick Access Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <a href="{{ route('student.hostel.allocations.create') }}" class="block p-6 bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 rounded-lg shadow-sm text-white transition-all duration-200 transform hover:-translate-y-1">
                    <div class="flex items-center">
                        <div class="rounded-full bg-white bg-opacity-30 p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-1">New Allocation</h3>
                            <p class="text-sm text-white text-opacity-80">Assign a student to a bed</p>
                        </div>
                    </div>
                </a>
                
                <!-- Removed the Bulk Allocation card that was causing the error -->
                
                <!-- Replaced with a "Manage Beds" card -->
                <a href="{{ route('student.hostel.beds.index') }}" class="block p-6 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 rounded-lg shadow-sm text-white transition-all duration-200 transform hover:-translate-y-1">
                    <div class="flex items-center">
                        <div class="rounded-full bg-white bg-opacity-30 p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-1">Manage Beds</h3>
                            <p class="text-sm text-white text-opacity-80">View and manage all beds</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- House Occupancy -->
                <div class="col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700">House Occupancy</h3>
                        </div>
                        <div class="p-6">
                            @foreach($houses as $house)
                            <div class="mb-4 last:mb-0">
                                <div class="flex justify-between items-center mb-1">
                                    <div class="flex items-center">
                                        <span class="font-medium">{{ $house->name }}</span>
                                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($house->gender == 'male') bg-blue-100 text-blue-800 
                                            @elseif($house->gender == 'female') bg-pink-100 text-pink-800 
                                            @else bg-purple-100 text-purple-800 @endif">
                                            {{ ucfirst($house->gender) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $house->occupied_beds }} / {{ $house->total_beds }} beds</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full 
                                        @if($house->occupancy_rate > 90) bg-red-600
                                        @elseif($house->occupancy_rate > 75) bg-yellow-500
                                        @elseif($house->occupancy_rate > 50) bg-green-500
                                        @else bg-blue-500 @endif" 
                                        style="width: {{ $house->occupancy_rate }}%">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Recent Allocations -->
                <div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="px-6 py-4 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700">Recent Allocations</h3>
                        </div>
                        <div class="p-6">
                            @if(count($recentAllocations) === 0)
                                <div class="text-gray-500 text-center py-4">No recent allocations found.</div>
                            @else
                                <div class="space-y-4">
                                    @foreach($recentAllocations as $allocation)
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-semibold text-gray-600">
                                                    @if(isset($allocation->student_name))
                                                        {{ substr($allocation->student_name, 0, 1) }}
                                                    @else
                                                        S
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $allocation->student_name ?? 'Unknown Student' }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    @if(isset($allocation->house_name) && isset($allocation->room_number) && isset($allocation->bed_number))
                                                        {{ $allocation->house_name }}, Room {{ $allocation->room_number }}, Bed {{ $allocation->bed_number }}
                                                    @else
                                                        Bed ID: {{ $allocation->bed_id }}
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-400">
                                                    @if(isset($allocation->created_at))
                                                        {{ \Carbon\Carbon::parse($allocation->created_at)->diffForHumans() }}
                                                    @else
                                                        Recently
                                                    @endif
                                                </p>
                                            </div>
                                            <div>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($allocation->status === 'approved') bg-green-100 text-green-800
                                                    @elseif($allocation->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($allocation->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="{{ route('student.hostel.allocations.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View all allocations</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Maintenance Issues -->
            <div class="mt-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 bg-white border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-700">Maintenance Issues</h3>
                        <!-- Removed maintenance create route that was causing the error -->
                    </div>
                    <div class="p-6">
                        @if(empty($maintenanceIssues) || count($maintenanceIssues) === 0)
                            <div class="text-gray-500 text-center py-4">No maintenance issues reported.</div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($maintenanceIssues as $issue)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ Str::limit($issue->title, 40) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $issue->location }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($issue->created_at)->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($issue->status === 'open') bg-red-100 text-red-800
                                                    @elseif($issue->status === 'in_progress') bg-yellow-100 text-yellow-800
                                                    @else bg-green-100 text-green-800 @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Removed maintenance index route that was causing the error -->
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>