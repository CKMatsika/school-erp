<!-- resources/views/timetable/subjects/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Subjects') }}
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
                        <h3 class="text-lg font-medium">School Subjects</h3>
                        <div class="space-x-2">
                            <a href="{{ route('timetables.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">
                                Back to Timetable Dashboard
                            </a>
                            <a href="{{ route('timetables.subjects.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Add New Subject
                            </a>
                        </div>
                    </div>
                    
                    @if(count($subjects) > 0)
                        <table class="min-w-full border divide-y">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y">
                                @foreach($subjects as $subject)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $subject->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $subject->code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 rounded mr-2" style="background-color: {{ $subject->color_code }}"></div>
                                                <span>{{ $subject->color_code }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ Str::limit($subject->description, 30) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('timetables.subjects.edit', $subject->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('timetables.subjects.delete', $subject->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ml-2 text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this subject?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="bg-yellow-50 p-4 rounded-md text-yellow-700">
                            No subjects have been created yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>