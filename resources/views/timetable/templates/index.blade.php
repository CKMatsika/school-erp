<!-- resources/views/timetable/templates/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Timetable Templates') }}
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
                        <h3 class="text-lg font-medium">Timetable Templates</h3>
                        <div class="space-x-2">
                            <a href="{{ route('timetables.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">
                                Back to Timetable Dashboard
                            </a>
                            <a href="{{ route('timetables.templates.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Add New Template
                            </a>
                        </div>
                    </div>
                    
                    @if(count($templates) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($templates as $template)
                                <div class="border rounded-lg shadow-sm overflow-hidden">
                                    <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                                        <h4 class="font-medium text-gray-800">{{ $template->name }}</h4>
                                        @if($template->is_active)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">Active</span>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <div class="mb-3">
                                            <p class="text-sm text-gray-500">Academic Year</p>
                                            <p class="font-medium">{{ $template->academic_year }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <p class="text-sm text-gray-500">Term</p>
                                            <p class="font-medium">{{ $template->term }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <p class="text-sm text-gray-500">Date Range</p>
                                            <p class="font-medium">{{ date('M d, Y', strtotime($template->start_date)) }} - {{ date('M d, Y', strtotime($template->end_date)) }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <p class="text-sm text-gray-500">Days of Week</p>
                                            <p class="font-medium">
                                                @php
                                                    $daysOfWeek = json_decode($template->days_of_week);
                                                    $dayNames = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                                    $selectedDays = [];
                                                    foreach($daysOfWeek as $day) {
                                                        $selectedDays[] = $dayNames[$day];
                                                    }
                                                    echo implode(', ', $selectedDays);
                                                @endphp
                                            </p>
                                        </div>
                                        
                                        @if($template->description)
                                            <div class="mb-3">
                                                <p class="text-sm text-gray-500">Description</p>
                                                <p class="text-sm">{{ $template->description }}</p>
                                            </div>
                                        @endif
                                        
                                        <div class="mt-4 flex flex-wrap justify-between items-center gap-2">
                                            <div class="space-x-1">
                                                <a href="{{ route('timetables.templates.edit', $template->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Edit</a>
                                                <form action="{{ route('timetables.templates.delete', $template->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this template?')">Delete</button>
                                                </form>
                                            </div>
                                            
                                            @if(!$template->is_active)
                                                <form action="{{ route('timetables.templates.activate', $template->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-sm px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                                        Set as Active
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-yellow-50 p-4 rounded-md text-yellow-700">
                            No timetable templates have been created yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>