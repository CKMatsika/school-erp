<!-- resources/views/timetable/templates/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Timetable Template') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <a href="{{ route('timetables.templates') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">
                            Back to Templates
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

                    <form action="{{ route('timetables.templates.update', $template->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $template->name) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label for="academic_year" class="block text-sm font-medium text-gray-700">Academic Year</label>
                                <input type="text" name="academic_year" id="academic_year" value="{{ old('academic_year', $template->academic_year) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <p class="mt-1 text-sm text-gray-500">E.g., "2023-2024"</p>
                            </div>

                            <div>
                                <label for="term" class="block text-sm font-medium text-gray-700">Term</label>
                                <input type="text" name="term" id="term" value="{{ old('term', $template->term) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <p class="mt-1 text-sm text-gray-500">E.g., "First Term", "Semester 1"</p>
                            </div>

                            <div>
                                <label for="days_of_week" class="block text-sm font-medium text-gray-700">Days of Week</label>
                                <div class="mt-1 space-y-2">
                                    @php
                                        $days = [
                                            1 => 'Monday',
                                            2 => 'Tuesday',
                                            3 => 'Wednesday',
                                            4 => 'Thursday',
                                            5 => 'Friday',
                                            6 => 'Saturday',
                                            7 => 'Sunday'
                                        ];
                                        $oldDays = old('days_of_week', $template->days_of_week);
                                    @endphp
                                    
                                    @foreach($days as $value => $day)
                                        <div class="flex items-center">
                                            <input type="checkbox" name="days_of_week[]" id="day_{{ $value }}" value="{{ $value }}" 
                                                {{ in_array($value, $oldDays) ? 'checked' : '' }}
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="day_{{ $value }}" class="ml-2 block text-sm text-gray-700">
                                                {{ $day }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $template->start_date) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $template->end_date) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>

                            <div class="col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $template->description) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Update Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>