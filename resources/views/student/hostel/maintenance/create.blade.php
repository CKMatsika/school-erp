{{-- resources/views/student/hostel/maintenance/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Report Maintenance Issue') }}
            </h2>
            <a href="{{ route('student.hostel.maintenance.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                View All Issues
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('student.hostel.maintenance.store') }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Title -->
                        <div>
                            <x-label for="title" :value="__('Issue Title')" />
                            <x-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus placeholder="e.g. Broken Window, Leaking Tap" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- House -->
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
                        
                        <!-- Room (Optional) -->
                        <div>
                            <x-label for="room_id" :value="__('Room (Optional)')" />
                            <select id="room_id" name="room_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                <option value="">Select Room</option>
                                <!-- Rooms will be loaded dynamically based on house selection -->
                            </select>
                            <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                        </div>
                        
                        <!-- Specific Location -->
                        <div>
                            <x-label for="location" :value="__('Specific Location')" />
                            <x-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location')" required placeholder="e.g. Common Room, Corridor, Bathroom" />
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>
                        
                        <!-- Issue Category -->
                        <div>
                            <x-label for="category" :value="__('Issue Category')" />
                            <select id="category" name="category" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="">Select Category</option>
                                <option value="plumbing" {{ old('category') == 'plumbing' ? 'selected' : '' }}>Plumbing</option>
                                <option value="electrical" {{ old('category') == 'electrical' ? 'selected' : '' }}>Electrical</option>
                                <option value="furniture" {{ old('category') == 'furniture' ? 'selected' : '' }}>Furniture</option>
                                <option value="appliance" {{ old('category') == 'appliance' ? 'selected' : '' }}>Appliance</option>
                                <option value="structural" {{ old('category') == 'structural' ? 'selected' : '' }}>Structural (Walls, Windows, Doors)</option>
                                <option value="cleaning" {{ old('category') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>
                        
                        <!-- Priority -->
                        <div>
                            <x-label for="priority" :value="__('Priority')" />
                            <select id="priority" name="priority" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low - Not Urgent</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium - Needs Attention</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High - Urgent Problem</option>
                                <option value="emergency" {{ old('priority') == 'emergency' ? 'selected' : '' }}>Emergency - Immediate Action Required</option>
                            </select>
                            <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <x-label for="description" :value="__('Detailed Description')" />
                            <textarea id="description" name="description" rows="4" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        
                        <!-- Images (Optional) -->
                        <div>
                            <x-label for="images" :value="__('Images (Optional)')" />
                            <input id="images" type="file" name="images[]" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <p class="text-sm text-gray-500 mt-1">Upload up to 3 images (JPG, PNG, max 5MB each)</p>
                            <x-input-error :messages="$errors->get('images')" class="mt-2" />
                            <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
                        </div>
                        
                        <!-- Availability for Access -->
                        <div>
                            <x-label for="access_info" :value="__('Access Information (Optional)')" />
                            <textarea id="access_info" name="access_info" rows="2" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" placeholder="e.g. Available weekdays after 4pm, please call before entering">{{ old('access_info') }}</textarea>
                            <x-input-error :messages="$errors->get('access_info')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">Provide information about when maintenance staff can access the area</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end mt-4">
                            <x-button>
                                {{ __('Submit Issue Report') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get DOM elements
            const houseSelect = document.getElementById('house_id');
            const roomSelect = document.getElementById('room_id');
            
            // Handle house selection change
            houseSelect.addEventListener('change', function() {
                const houseId = this.value;
                
                // Clear room options
                roomSelect.innerHTML = '<option value="">Select Room</option>';
                
                if (!houseId) {
                    return;
                }
                
                // Show loading state
                roomSelect.innerHTML += '<option value="" disabled selected>Loading rooms...</option>';
                
                // Fetch rooms for the selected house
                fetch(`/api/houses/${houseId}/rooms`)
                    .then(response => response.json())
                    .then(data => {
                        // Reset dropdown
                        roomSelect.innerHTML = '<option value="">Select Room</option>';
                        
                        // Add rooms to dropdown
                        if (data.length > 0) {
                            data.forEach(room => {
                                const option = document.createElement('option');
                                option.value = room.id;
                                option.textContent = `Room ${room.room_number}`;
                                roomSelect.appendChild(option);
                            });
                        } else {
                            roomSelect.innerHTML += '<option value="" disabled>No rooms available</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching rooms:', error);
                        roomSelect.innerHTML = '<option value="">Select Room</option>';
                        roomSelect.innerHTML += '<option value="" disabled>Error loading rooms</option>';
                    });
            });
        });
    </script>
</x-app-layout>