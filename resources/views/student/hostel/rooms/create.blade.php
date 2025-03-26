{{-- resources/views/student/hostel/rooms/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Room') }}
            </h2>
            <a href="{{ route('student.hostel.rooms.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Back to Rooms
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('student.hostel.rooms.store') }}" class="space-y-6">
                        @csrf
                        
                        <!-- House Selection -->
                        <div>
                            <x-label for="house_id" :value="__('House')" />
                            <select id="house_id" name="house_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="">Select House</option>
                                @foreach($houses as $house)
                                    <option value="{{ $house->id }}" {{ old('house_id') == $house->id ? 'selected' : '' }}>
                                        {{ $house->name }} ({{ $house->code }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('house_id')" class="mt-2" />
                        </div>
                        
                        <!-- Room Number -->
                        <div>
                            <x-label for="room_number" :value="__('Room Number')" />
                            <x-input id="room_number" class="block mt-1 w-full" type="text" name="room_number" :value="old('room_number')" required />
                            <x-input-error :messages="$errors->get('room_number')" class="mt-2" />
                        </div>

                        <!-- Floor -->
                        <div>
                            <x-label for="floor" :value="__('Floor')" />
                            <x-input id="floor" class="block mt-1 w-full" type="text" name="floor" :value="old('floor')" required />
                            <x-input-error :messages="$errors->get('floor')" class="mt-2" />
                        </div>
                        
                        <!-- Capacity -->
                        <div>
                            <x-label for="capacity" :value="__('Capacity (Number of Beds)')" />
                            <x-input id="capacity" class="block mt-1 w-full" type="number" name="capacity" :value="old('capacity', 2)" min="1" required />
                            <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                        </div>
                        
                        <!-- Room Type -->
                        <div>
                            <x-label for="room_type" :value="__('Room Type')" />
                            <select id="room_type" name="room_type" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="standard" {{ old('room_type') == 'standard' ? 'selected' : '' }}>Standard</option>
                                <option value="ensuite" {{ old('room_type') == 'ensuite' ? 'selected' : '' }}>En-suite</option>
                                <option value="accessible" {{ old('room_type') == 'accessible' ? 'selected' : '' }}>Accessible</option>
                                <option value="premium" {{ old('room_type') == 'premium' ? 'selected' : '' }}>Premium</option>
                            </select>
                            <x-input-error :messages="$errors->get('room_type')" class="mt-2" />
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <x-label for="description" :value="__('Description (Optional)')" />
                            <textarea id="description" name="description" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <x-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end mt-4">
                            <x-button>
                                {{ __('Create Room') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>