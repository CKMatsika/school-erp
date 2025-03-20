<!-- resources/views/timetable/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('School Timetable') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium mb-4">Timetable Management</h3>
                    
                    <p class="text-gray-700 mb-4">
                        Welcome to the School Timetable module. Here you can manage your school timetables.
                    </p>
                    
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white border rounded-lg shadow-sm p-4">
                            <h4 class="font-medium mb-2">Your Imported Data</h4>
                            <ul class="list-disc pl-5 text-gray-700">
                                <li>Periods: {{ $counts['periods'] }}</li>
                                <li>School Classes: {{ $counts['classes'] }}</li>
                                <li>Subjects: {{ $counts['subjects'] }}</li>
                                <li>Teachers: {{ $counts['teachers'] }}</li>
                                <li>Timetable Templates: {{ $counts['templates'] }}</li>
                            </ul>
                        </div>

                        <div class="bg-white border rounded-lg shadow-sm p-4">
                            <h4 class="font-medium mb-2">Quick Actions</h4>
                            <div class="flex flex-col space-y-2">
                                <a href="{{ route('timetables.current') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-center">
                                    View Current Timetable
                                </a>
                                <a href="{{ route('timetables.periods') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 text-center">
                                    Manage Periods
                                </a>
                                <a href="{{ route('timetables.classes') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 text-center">
                                    Manage Classes
                                </a>
                                <a href="{{ route('timetables.subjects') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 text-center">
                                    Manage Subjects
                                </a>
                                <a href="{{ route('timetables.teachers') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 text-center">
                                    Manage Teachers
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>