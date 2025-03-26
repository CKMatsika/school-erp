<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bulk Bed Allocation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Allocate Multiple Beds</h3>
                    
                    @if($maleStudents->isEmpty() && $femaleStudents->isEmpty() && $otherStudents->isEmpty())
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No students are pending bed allocation. All boarding students have been assigned beds.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('student.hostel.processBulkAllocate') }}" method="POST">
                            @csrf
                            
                            <div class="mb-6">
                                <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                                <select id="academic_year_id" name="academic_year_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $year->id === $currentAcademicYear->id ? 'selected' : '' }}>
                                            {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- Male Students -->
                                @if($maleStudents->isNotEmpty())
                                    <div class="border rounded-md p-4">
                                        <h4 class="font-semibold text-gray-700 mb-2">Male Students</h4>
                                        <div class="space-y-3">
                                            @foreach($maleStudents as $student)
                                                <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                                                    <div class="flex items-center">
                                                        <input type="checkbox" id="student_{{ $student->id }}" name="selected_students[]" value="{{ $student->id }}" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                        <label for="student_{{ $student->id }}" class="ml-2 block text-sm text-gray-900">
                                                            {{ $student->full_name }}
                                                        </label>
                                                    </div>
                                                    <select name="allocations[{{ $student->id }}][bed_id]" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                                        <option value="">-- Select Bed --</option>
                                                        @foreach($houses->where('gender', 'male')->merge($houses->where('gender', 'mixed')) as $house)
                                                            @foreach($house->rooms as $room)
                                                                @foreach($room->beds->where('status', 'available') as $bed)
                                                                    <option value="{{ $bed->id }}">{{ $house->name }} - Room {{ $room->room_number }} - Bed {{ $bed->bed_number }}</option>
                                                                @endforeach
                                                            @endforeach
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Female Students -->
                                @if($femaleStudents->isNotEmpty())
                                    <div class="border rounded-md p-4">
                                        <h4 class="font-semibold text-gray-700 mb-2">Female Students</h4>
                                        <div class="space-y-3">
                                            @foreach($femaleStudents as $student)
                                                <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                                                    <div class="flex items-center">
                                                        <input type="checkbox" id="student_{{ $student->id }}" name="selected_students[]" value="{{ $student->id }}" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                        <label for="student_{{ $student->id }}" class="ml-2 block text-sm text-gray-900">
                                                            {{ $student->full_name }}
                                                        </label>
                                                    </div>
                                                    <select name="allocations[{{ $student->id }}][bed_id]" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                                        <option value="">-- Select Bed --</option>
                                                        @foreach($houses->where('gender', 'female')->merge($houses->where('gender', 'mixed')) as $house)
                                                            @foreach($house->rooms as $room)
                                                                @foreach($room->beds->where('status', '
                                                                @foreach($room->beds->where('status', 'available') as $bed)
                                                                    <option value="{{ $bed->id }}">{{ $house->name }} - Room {{ $room->room_number }} - Bed {{ $bed->bed_number }}</option>
                                                                @endforeach
                                                            @endforeach
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Other Gender Students -->
                                @if($otherStudents->isNotEmpty())
                                    <div class="border rounded-md p-4">
                                        <h4 class="font-semibold text-gray-700 mb-2">Other Students</h4>
                                        <div class="space-y-3">
                                            @foreach($otherStudents as $student)
                                                <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                                                    <div class="flex items-center">
                                                        <input type="checkbox" id="student_{{ $student->id }}" name="selected_students[]" value="{{ $student->id }}" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                        <label for="student_{{ $student->id }}" class="ml-2 block text-sm text-gray-900">
                                                            {{ $student->full_name }}
                                                        </label>
                                                    </div>
                                                    <select name="allocations[{{ $student->id }}][bed_id]" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                                        <option value="">-- Select Bed --</option>
                                                        @foreach($houses->where('gender', 'mixed') as $house)
                                                            @foreach($house->rooms as $room)
                                                                @foreach($room->beds->where('status', 'available') as $bed)
                                                                    <option value="{{ $bed->id }}">{{ $house->name }} - Room {{ $room->room_number }} - Bed {{ $bed->bed_number }}</option>
                                                                @endforeach
                                                            @endforeach
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('student.hostel.allocations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                    Cancel
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Allocate Selected
                                </button>
                            </div>
                            
                            <!-- JavaScript to enable/disable selects based on checkboxes -->
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="selected_students[]"]');
                                    
                                    checkboxes.forEach(checkbox => {
                                        checkbox.addEventListener('change', function() {
                                            const studentId = this.value;
                                            const select = document.querySelector(`select[name="allocations[${studentId}][bed_id]"]`);
                                            
                                            if (this.checked) {
                                                select.disabled = false;
                                                select.required = true;
                                            } else {
                                                select.disabled = true;
                                                select.required = false;
                                                select.value = '';
                                            }
                                        });
                                    });
                                });
                            </script>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>