<!-- resources/views/timetable/subjects/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Subject') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <a href="{{ route('timetables.subjects') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">
                            Back to Subjects
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

                    <form action="{{ route('timetables.subjects.update', $subject->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Subject Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $subject->name) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700">Subject Code</label>
                                <input type="text" name="code" id="code" value="{{ old('code', $subject->code) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <p class="mt-1 text-sm text-gray-500">E.g., "MATH", "ENG", "PHY"</p>
                            </div>

                            <div>
                                <label for="color_code" class="block text-sm font-medium text-gray-700">Color Code</label>
                                <div class="mt-1 flex">
                                    <input type="color" name="color_code" id="color_code" value="{{ old('color_code', $subject->color_code) }}"
                                        class="h-10 w-10 rounded-l-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <input type="text" id="color_text" value="{{ old('color_code', $subject->color_code) }}" 
                                        class="flex-grow rounded-r-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        onchange="document.getElementById('color_code').value = this.value">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Used for timetable display</p>
                            </div>

                            <div class="col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $subject->description) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Update Subject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Synchronize the color input and text field
        document.getElementById('color_code').addEventListener('input', function() {
            document.getElementById('color_text').value = this.value;
        });
    </script>
</x-app-layout>