<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Allocate Bed to Student') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Student Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Student Information</h3>
                    
                    <div class="flex flex-col md:flex-row md:items-center">
                        <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-4">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->full_name }}" class="h-20 w-20 rounded-full object-cover">
                            @else
                                <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-grow">
                            <h4 class="text-xl font-bold">{{ $student->full_name }}</h4>
                            <p class="text-sm text-gray-600">{{ $student->admission_number }}</p>
                            
                            <div class="grid grid-cols-2 gap-4 mt-3">
                                <div>
                                    <p class="text-sm text-gray-500">Gender</p>
                                    <p class="font-medium">{{ ucfirst($student->gender) }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Class</p>
                                    <p class="font-medium">{{ $student->class->name ?? 'Not assigned' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Boarding Status</p>
                                    <p class="font-medium">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ ucfirst($student->boarding_status ?? 'Pending') }}
                                        </span>
                                    </p>
                                </div>
                                
                                @if($student->boarding_preferences)
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-500">Boarding Preferences</p>
                                    <p class
                                    <p class="text-sm text-gray-600">{{ $student->boarding_preferences }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bed Allocation Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Assign Bed</h3>
                    
                    @if($houses->isEmpty())
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No available beds found for this student. Please check that there are active houses with available beds that match the student's gender.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('student.hostel.allocations.store', $student) }}" method="POST">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="hostel_house_id" class="block text-sm font-medium text-gray-700">Select House</label>
                                    <select id="hostel_house_id" name="hostel_house_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        <option value="">-- Select House --</option>
                                        @foreach($houses as $house)
                                            <option value="{{ $house->id }}" data-gender="{{ $house->gender }}">
                                                {{ $house->name }} ({{ ucfirst($house->gender) }}) - {{ $house->rooms->sum(function($room) { return $room->beds_count; }) }} beds
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="hostel_room_id" class="block text-sm font-medium text-gray-700">Select Room</label>
                                    <select id="hostel_room_id" name="hostel_room_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required disabled>
                                        <option value="">-- First Select a House --</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="hostel_bed_id" class="block text-sm font-medium text-gray-700">Select Bed</label>
                                    <select id="hostel_bed_id" name="hostel_bed_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required disabled>
                                        <option value="">-- First Select a Room --</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
                                    <select id="academic_year_id" name="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>
                                                {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-end mt-4">
                                <a href="{{ route('student.hostel.allocations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                    Cancel
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Allocate Bed
                                </button>
                            </div>
                        </form>
                        
                        <!-- JavaScript for cascading dropdowns -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const houseSelect = document.getElementById('hostel_house_id');
                                const roomSelect = document.getElementById('hostel_room_id');
                                const bedSelect = document.getElementById('hostel_bed_id');
                                const studentGender = "{{ $student->gender }}";
                                
                                // When house is selected
                                houseSelect.addEventListener('change', function() {
                                    const houseId = this.value;
                                    const selectedOption = this.options[this.selectedIndex];
                                    const houseGender = selectedOption.dataset.gender;
                                    
                                    // Check gender compatibility
                                    if (houseGender !== 'mixed' && houseGender !== studentGender) {
                                        alert('Warning: This house is designated for ' + houseGender + ' students, but the student is ' + studentGender + '.');
                                    }
                                    
                                    // Reset room and bed dropdowns
                                    roomSelect.innerHTML = '<option value="">-- Select Room --</option>';
                                    bedSelect.innerHTML = '<option value="">-- First Select a Room --</option>';
                                    
                                    if (houseId) {
                                        // Enable room dropdown
                                        roomSelect.disabled = false;
                                        
                                        // Fetch rooms for this house
                                        fetch(`/api/hostel/houses/${houseId}/rooms`)
                                            .then(response => response.json())
                                            .then(rooms => {
                                                rooms.forEach(room => {
                                                    const option = document.createElement('option');
                                                    option.value = room.id;
                                                    option.textContent = `Room ${room.room_number} (${room.available_beds} beds available)`;
                                                    option.dataset.availableBeds = room.available_beds;
                                                    roomSelect.appendChild(option);
                                                });
                                            });
                                    } else {
                                        // Disable dropdowns if no house selected
                                        roomSelect.disabled = true;
                                        bedSelect.disabled = true;
                                    }
                                });
                                
                                // When room is selected
                                roomSelect.addEventListener('change', function() {
                                    const roomId = this.value;
                                    
                                    // Reset bed dropdown
                                    bedSelect.innerHTML = '<option value="">-- Select Bed --</option>';
                                    
                                    if (roomId) {
                                        // Enable bed dropdown
                                        bedSelect.disabled = false;
                                        
                                        // Fetch beds for this room
                                        fetch(`/api/hostel/rooms/${roomId}/beds`)
                                            .then(response => response.json())
                                            .then(beds => {
                                                beds.forEach(bed => {
                                                    const option = document.createElement('option');
                                                    option.value = bed.id;
                                                    option.textContent = `Bed ${bed.bed_number}`;
                                                    bedSelect.appendChild(option);
                                                });
                                            });
                                    } else {
                                        // Disable bed dropdown if no room selected
                                        bedSelect.disabled = true;
                                    }
                                });
                            });
                        </script>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>