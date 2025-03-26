{{-- resources/views/student/hostel/maintenance/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Maintenance Issues') }}
            </h2>
            <a href="{{ route('student.hostel.maintenance.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                Report New Issue
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('student.hostel.maintenance.index') }}" method="GET" class="space-y-4">
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
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                                <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                    <option value="">All Status</option>
                                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Filter by Priority</label>
                                <select id="priority" name="priority" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                    <option value="">All Priorities</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="emergency" {{ request('priority') == 'emergency' ? 'selected' : '' }}>Emergency</option>
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

            <!-- Issues List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Maintenance Issues</h3>
                    
                    @if($issues->isEmpty())
                        <div class="text-gray-500 text-center py-4">No maintenance issues found matching your criteria.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Details</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($issues as $issue)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $issue->title }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($issue->description, 60) }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($issue->category) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $issue->house->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                @if($issue->room)
                                                    Room {{ $issue->room->room_number }}, 
                                                @endif
                                                {{ $issue->location }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($issue->priority === 'emergency') bg-red-100 text-red-800 
                                                @elseif($issue->priority === 'high') bg-orange-100 text-orange-800
                                                @elseif($issue->priority === 'medium') bg-yellow-100 text-yellow-800
                                                @else bg-blue-100 text-blue-800 @endif">
                                                {{ ucfirst($issue->priority) }}
                                            </span>
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
                                            <div class="text-xs text-gray-400">{{ $issue->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('student.hostel.maintenance.show', $issue->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">View</a>
                                            @if($issue->status !== 'resolved')
                                                <a href="{{ route('student.hostel.maintenance.edit', $issue->id) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">Update</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $issues->links() }}
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Total Issues</h3>
                    <p class="text-3xl font-bold">{{ $counts['total'] }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Open Issues</h3>
                    <p class="text-3xl font-bold">{{ $counts['open'] }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">In Progress</h3>
                    <p class="text-3xl font-bold">{{ $counts['in_progress'] }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Resolved</h3>
                    <p class="text-3xl font-bold">{{ $counts['resolved'] }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>