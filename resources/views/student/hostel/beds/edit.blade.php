{{-- resources/views/student/hostel/beds/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Bed') }}
            </h2>
            <div>
                <a href="{{ route('student.hostel.beds.show', $bed->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    View Details
                </a>
                <a href="{{ route('student.hostel.beds.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to Beds
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('student.hostel.beds.update', $bed->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <!-- Room Selection -->
                        <div>
                            <x-label for="room_id" :value="__('Room')" />
                            <select id="room_id" name="room_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="">Select Room</option>
                                @foreach($houses as $house)
                                    <optgroup label="{{ $house->name }} ({{ $house->code }})">
                                        @foreach($house->rooms as $room)
                                            <option value="{{ $room->id }}" {{ old('room_id', $bed->room_id) == $room->id ? 'selected' : '' }}>
                                                Room {{ $room->room_number }} ({{ $room->available_beds }} of {{ $room->capacity }} beds available)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                            @if($bed->status === 'occupied')
                                <p class="text-sm text-red-600 mt-1">Note: Changing rooms for an occupied bed will require reassignment of the student. The current allocation will be lost.</p>
                            @endif
                        </div>
                        
                        <!-- Bed Number -->
                        <div>
                            <x-label for="bed_number" :value="__('Bed Number/Identifier')" />
                            <x-input id="bed_number" class="block mt-1 w-full" type="text" name="bed_number" :value="old('bed_number', $bed->bed_number)" required />
                            <x-input-error :messages="$errors->get('bed_number')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">Identifier for the bed within the room (e.g. A, B, 1, 2)</p>
                        </div>

                        <!-- Bed Type -->
                        <div>
                            <x-label for="bed_type" :value="__('Bed Type')" />
                            <select id="bed_type" name="bed_type" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="single" {{ old('bed_type', $bed->bed_type) == 'single' ? 'selected' : '' }}>Single</option>
                                <option value="bunk_upper" {{ old('bed_type', $bed->bed_type) == 'bunk_upper' ? 'selected' : '' }}>Bunk (Upper)</option>
                                <option value="bunk_lower" {{ old('bed_type', $bed->bed_type) == 'bunk_lower' ? 'selected' : '' }}>Bunk (Lower)</option>
                            </select>
                            <x-input-error :messages="$errors->get('bed_type')" class="mt-2" />
                        </div>
                        
                        <!-- Cost -->
                        <div>
                            <x-label for="cost" :value="__('Cost per Academic Year')" />
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <x-input id="cost" class="block mt-1 w-full pl-7" type="number" name="cost" :value="old('cost', $bed->cost)" step="0.01" />
                            </div>
                            <x-input-error :messages="$errors->get('cost')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">Leave blank to use default price for the room type</p>
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <x-label for="description" :value="__('Notes/Description (Optional)')" />
                            <textarea id="description" name="description" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('description', $bed->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <x-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="available" {{ old('status', $bed->status) == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="occupied" {{ old('status', $bed->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                <option value="maintenance" {{ old('status', $bed->status) == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            @if($bed->status === 'occupied')
                                <p class="text-sm text-red-600 mt-1">Warning: Changing status from 'Occupied' will remove the current student assignment.</p>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end mt-4">
                            <x-button>
                                {{ __('Update Bed') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>