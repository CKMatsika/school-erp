<!-- resources/views/timetable/periods/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Periods') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                            {{ session('error') }}
                        </div>
                    @endif
                
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">School Periods</h3>
                        <div class="space-x-2">
                            <a href="{{ route('timetables.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">
                                Back to Timetable Dashboard
                            </a>
                            <a href="{{ route('timetables.periods.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Add New Period
                            </a>
                        </div>
                    </div>
                    
                    @if(count($periods) > 0)
                        <table class="min-w-full border divide-y">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Is Break</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y">
                                @foreach($periods as $period)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $period->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $period->start_time }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $period->end_time }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $period->is_break ? 'Yes' : 'No' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $period->order }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('timetables.periods.edit', $period->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('timetables.periods.delete', $period->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ml-2 text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this period?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="bg-yellow-50 p-4 rounded-md text-yellow-700">
                            No periods have been created yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>