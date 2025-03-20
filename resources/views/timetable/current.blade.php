<!-- resources/views/timetable/current.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Current Timetable') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Current Timetable</h3>
                        <a href="{{ route('timetables.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">
                            Back to Timetable Dashboard
                        </a>
                    </div>
                    
                    @if($template)
                        <div class="mb-4">
                            <p><strong>Template:</strong> {{ $template->name }}</p>
                            <p><strong>Academic Year:</strong> {{ $template->academic_year }}</p>
                            <p><strong>Term:</strong> {{ $template->term }}</p>
                        </div>
                        
                        @if(count($entries) > 0)
                            <!-- Table structure showing timetable entries -->
                            <div class="mt-6">
                                <h4 class="font-medium mb-2">Timetable Entries</h4>
                                <!-- Your timetable display logic here -->
                                <p>Timetable entries will be displayed here in a formatted table.</p>
                            </div>
                        @else
                            <div class="bg-yellow-50 p-4 rounded-md text-yellow-700">
                                No entries have been added to this timetable yet.
                            </div>
                        @endif
                    @else
                        <div class="bg-yellow-50 p-4 rounded-md text-yellow-700">
                            No active timetable template found. Please create a timetable template first.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>