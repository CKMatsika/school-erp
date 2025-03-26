{{-- resources/views/student/hostel/allocations/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Allocation') }}
            </h2>
            <div>
                <a href="{{ route('student.hostel.allocations.show', $allocation->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    View Details
                </a>
                <a href="{{ route('student.hostel.allocations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to Allocations
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('student.hostel.allocations.update', $allocation->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <!-- Student Information (non-editable) -->
                        <div>
                            <x-label :value="__('Student')" />
                            <div class="mt-1 p-3 bg-gray-50 rounded-md flex items-center">
                                <div class="text-sm font-medium text-gray-900">{{ $allocation->student->name }}</div>
                                <span class="mx-2 text-gray-500">|</span>
                                <div class="text-sm text-gray-500">{{ $allocation->student->student_id }}</div>
                            </div>
                        </div>
                        
                        <!-- Current Bed Information (non-editable) -->
                        <div>
                            <x-label :value="__('Current Bed')" />
                            <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $allocation->bed->room->house->name }}, Room {{ $allocation->bed->room->room_number }}, Bed {{ $allocation->bed->bed_number }}
                                </div>
                                <div class="text-sm text-gray-500 mt-1">
                                    {{ ucfirst(str_replace('_', ' ', $allocation->bed->bed_type)) }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Change Bed Option -->
                        <div>
                            <div class="flex items-center">
                                <input id="change_bed" name="change_bed" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="change_bed" class="ml-2 block text-sm font-medium text-gray-700">Change bed assignment</label>
                            </div>
                        </div>
                        
                        <!-- New Bed Selection (shown/hidden with JavaScript) -->
                        <div id="new_bed_section" class="hidden">
                            <x-label for="new_bed_id" :value="__('New Bed Assignment')" />
                            <select id="new_bed_id" name="new_bed_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                <option value="">Select New Bed</option>
                                @foreach($houses as $house)
                                    <optgroup label="{{ $house->name }} ({{ $house->code }})">
                                        @foreach($house->rooms as $room)
                                            @foreach($room->availableBeds as $bed)
                                                <option value="{{ $bed->id }}">
                                                    Room {{ $room->room_number }}, Bed {{ $bed->bed_number }} ({{ ucfirst(str_replace('_', ' ', $bed->bed_type)) }})
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('new_bed_id')" class="mt-2" />
                        </div>
                        
                        <!-- Start Date -->
                        <div>
                            <x-label for="start_date" :value="__('Start Date')" />
                            <x-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $allocation->start_date->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>
                        
                        <!-- End Date -->
                        <div>
                            <x-label for="end_date" :value="__('End Date')" />
                            <x-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $allocation->end_date->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>
                        
                        <!-- Payment Status -->
                        <div>
                            <x-label for="payment_status" :value="__('Payment Status')" />
                            <select id="payment_status" name="payment_status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="pending" {{ old('payment_status', $allocation->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="partial" {{ old('payment_status', $allocation->payment_status) == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ old('payment_status', $allocation->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_status')" class="mt-2" />
                        </div>
                        
                        <!-- Notes -->
                        <div>
                            <x-label for="notes" :value="__('Notes (Optional)')" />
                            <textarea id="notes" name="notes" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('notes', $allocation->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <x-label for="status" :value="__('Allocation Status')" />
                            <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                <option value="pending" {{ old('status', $allocation->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ old('status', $allocation->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status', $allocation->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end mt-4">
                            <x-button>
                                {{ __('Update Allocation') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const changeBedCheckbox = document.getElementById('change_bed');
            const newBedSection = document.getElementById('new_bed_section');
            
            changeBedCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    newBedSection.classList.remove('hidden');
                } else {
                    newBedSection.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>