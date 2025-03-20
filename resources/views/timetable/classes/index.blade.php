<!-- resources/views/timetable/classes/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Classes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">School Classes</h3>
                        <div class="space-x-2">
                            <a href="{{ route('timetables.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">
                                Back to Timetable Dashboard
                            </a>
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Add New Class
                            </button>
                        </div>
                    </div>
                    
                    @if(count($classes) > 0)
                        <table class="min-w-full border divide-y">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Home Room</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y">
                                @foreach($classes as $class)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $class->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $class->level }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $class->capacity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $class->home_room }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <a href="#" class="ml-2 text-red-600 hover:text-red-900">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="bg-yellow-50 p-4 rounded-md text-yellow-700">
                            No classes have been created yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>