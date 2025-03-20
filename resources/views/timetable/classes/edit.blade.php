<!-- resources/views/timetable/classes/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Class') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <a href="{{ route('timetables.classes') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">
                            Back to Classes
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                                <p class="font-bold">Validation Error</p>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('timetables.classes.update', $class->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Class Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $class->name) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
                                <input type="text" name="level" id="level" value="{{ old('level', $class->level) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <p class="mt-1 text-sm text-gray-500">E.g., "Primary 5", "Form 3", "Grade 8"</p>
                            </div>

                            <div>
                                <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                                <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $class->capacity) }}" min="1"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label for="home_room" class="block text-sm font-medium text-gray-700">Home Room</label>
                                <input type="text" name="home_room" id="home_room" value="{{ old('home_room', $class->home_room) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>

                            <div class="col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes', $class->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Update Class
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>