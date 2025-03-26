{{-- resources/views/student/hostel/rooms/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Room') }}
            </h2>
            <div>
                <a href="{{ route('student.hostel.rooms.show', $room->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    View Details
                </a>
                <a href="{{ route('student.hostel.rooms.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to Rooms
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('student.hostel.rooms.update', $room->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <!-- House Selection -->
                        <div>
                            <x-label for="house_id" :value="__('House')" />
                            <select id="house_id" name="house_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="">Select House</option>
                                @foreach($houses as $house)
                                    <option value="{{ $house->id }}" {{ old('house_id', $room->house_id) == $house->id ? 'selected' : '' }}>
                                        {{ $house->name }} ({{ $house->code }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('house_id')" class="mt-2" />
                        </div>
                        
                        <!-- Room Number -->
                        <div>
                            <x-label for="room_number" :value="__('Room Number')" />
                            <x-input id="room_number" class="block mt-1 w-full" type="text" name="room_number" :value="old('room_number', $room->room_number)" required />
                            <x-input-error :messages="$errors->get('room_number')" class="mt-2" />
                        </div>

                        <!-- Floor -->
                        <div>
                            <x-label for="floor" :value="__('Floor')" />
                            <x-input id="floor" class="block mt-1 w-full" type="text" name="floor" :value="old('floor', $room->floor)" required />
                            <x-input-error :messages="$errors->get('floor')" class="mt-2" />
                        </div>
                        
                        <!-- Room Type -->
                        <div>
                            <x-label for="room_type" :value="__('Room Type')" />
                            <select id="room_type" name="room_type" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="standard" {{ old('room_type', $room->room_type) == 'standard' ? 'selected' : '' }}>Standard</option>
                                <option value="ensuite" {{ old('room_type', $room->room_type) == 'ensuite' ? 'selected' : '' }}>En-suite</option>
                                <option value="accessible" {{ old('room_type', $room->room_type) == 'accessible' ? 'selected' : '' }}>Accessible</option>
                                <option value="premium" {{ old('room_type', $room->room_type) == 'premium' ? 'selected' : '' }}>Premium</option>
                            </select>
                            <x-input-error :messages="$errors->get('room_type')" class="mt-2" />
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <x-label for="description" :value="__('Description (Optional)')" />
                            <textarea id="description" name="description" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('description', $room->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <x-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="available" {{ old('status', $room->status) == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="full" {{ old('status', $room->status) == 'full' ? 'selected' : '' }}>Full</option>
                                <option value="maintenance" {{ old('status', $room->status) == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            @if($room->status == 'full')
                                <p class="text-sm text-red-600 mt-1">Note: If all beds are occupied, setting status to 'Available' will not change actual availability.</p>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end mt-4">
                            <x-button>
                                {{ __('Update Room') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>