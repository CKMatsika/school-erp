<div>
    <form method="POST" action="{{ isset($vehicle) ? route('vehicle.update', $vehicle) : route('vehicle.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($vehicle))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Vehicle Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Vehicle Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Vehicle Name -->
                    <div>
                        <x-input-label for="name" :value="__('Vehicle Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', isset($vehicle) ? $vehicle->name : '')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    
                    <!-- Vehicle Type -->
                    <div>
                        <x-input-label for="type" :value="__('Vehicle Type')" />
                        <select id="type" name="type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">{{ __('Select Type') }}</option>
                            @foreach(['Bus', 'Mini Bus', 'Van', 'Car', 'Truck'] as $type)
                                <option value="{{ $type }}" {{ (old('type', isset($vehicle) ? $vehicle->type : '')) == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <!-- Registration Number -->
                    <div>
                        <x-input-label for="registration_number" :value="__('Registration Number')" />
                        <x-text-input id="registration_number" class="block mt-1 w-full" type="text" name="registration_number" :value="old('registration_number', isset($vehicle) ? $vehicle->registration_number : '')" required />
                        <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
                    </div>

                    <!-- Capacity -->
                    <div>
                        <x-input-label for="capacity" :value="__('Seating Capacity')" />
                        <x-text-input id="capacity" class="block mt-1 w-full" type="number" min="1" name="capacity" :value="old('capacity', isset($vehicle) ? $vehicle->capacity : '')" required />
                        <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            @foreach(['Active', 'In Maintenance', 'Out of Service', 'Reserved'] as $status)
                                <option value="{{ $status }}" {{ (old('status', isset($vehicle) ? $vehicle->status : 'Active')) == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <!-- Driver -->
                    <div>
                        <x-input-label for="driver_id" :value="__('Assigned Driver')" />
                        <select id="driver_id" name="driver_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ (old('driver_id', isset($vehicle) ? $vehicle->driver_id : '')) == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }} ({{ $driver->license_number }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('driver_id')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Vehicle Details -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Vehicle Details') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Make -->
                    <div>
                        <x-input-label for="make" :value="__('Make')" />
                        <x-text-input id="make" class="block mt-1 w-full" type="text" name="make" :value="old('make', isset($vehicle) ? $vehicle->make : '')" />
                        <x-input-error :messages="$errors->get('make')" class="mt-2" />
                    </div>

                    <!-- Model -->
                    <div>
                        <x-input-label for="model" :value="__('Model')" />
                        <x-text-input id="model" class="block mt-1 w-full" type="text" name="model" :value="old('model', isset($vehicle) ? $vehicle->model : '')" />
                        <x-input-error :messages="$errors->get('model')" class="mt-2" />
                    </div>

                    <!-- Year -->
                    <div>
                        <x-input-label for="year" :value="__('Year')" />
                        <x-text-input id="year" class="block mt-1 w-full" type="number" min="1900" max="{{ date('Y') + 1 }}" name="year" :value="old('year', isset($vehicle) ? $vehicle->year : '')" />
                        <x-input-error :messages="$errors->get('year')" class="mt-2" />
                    </div>

                    <!-- Color -->
                    <div>
                        <x-input-label for="color" :value="__('Color')" />
                        <x-text-input id="color" class="block mt-1 w-full" type="text" name="color" :value="old('color', isset($vehicle) ? $vehicle->color : '')" />
                        <x-input-error :messages="$errors->get('color')" class="mt-2" />
                    </div>

                    <!-- Fuel Type -->
                    <div>
                        <x-input-label for="fuel_type" :value="__('Fuel Type')" />
                        <select id="fuel_type" name="fuel_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">{{ __('Select Fuel Type') }}</option>
                            @foreach(['Petrol', 'Diesel', 'Electric', 'Hybrid', 'CNG'] as $fuelType)
                                <option value="{{ $fuelType }}" {{ (old('fuel_type', isset($vehicle) ? $vehicle->fuel_type : '')) == $fuelType ? 'selected' : '' }}>
                                    {{ $fuelType }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('fuel_type')" class="mt-2" />
                    </div>

                    <!-- Insurance Expiry -->
                    <div>
                        <x-input-label for="insurance_expiry" :value="__('Insurance Expiry')" />
                        <x-text-input id="insurance_expiry" class="block mt-1 w-full" type="date" name="insurance_expiry" :value="old('insurance_expiry', isset($vehicle) && $vehicle->insurance_expiry ? $vehicle->insurance_expiry->format('Y-m-d') : '')" />
                        <x-input-error :messages="$errors->get('insurance_expiry')" class="mt-2" />
                    </div>

                    <!-- Last Service Date -->
                    <div>
                        <x-input-label for="last_service_date" :value="__('Last Service Date')" />
                        <x-text-input id="last_service_date" class="block mt-1 w-full" type="date" name="last_service_date" :value="old('last_service_date', isset($vehicle) && $vehicle->last_service_date ? $vehicle->last_service_date->format('Y-m-d') : '')" />
                        <x-input-error :messages="$errors->get('last_service_date')" class="mt-2" />
                    </div>

                    <!-- Next Service Date -->
                    <div>
                        <x-input-label for="next_service_date" :value="__('Next Service Date')" />
                        <x-text-input id="next_service_date" class="block mt-1 w-full" type="date" name="next_service_date" :value="old('next_service_date', isset($vehicle) && $vehicle->next_service_date ? $vehicle->next_service_date->format('Y-m-d') : '')" />
                        <x-input-error :messages="$errors->get('next_service_date')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Additional Information') }}</h3>
                
                <div class="grid grid-cols-1 gap-4">
                    <!-- Notes -->
                    <div>
                        <x-input-label for="notes" :value="__('Notes')" />
                        <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes', isset($vehicle) ? $vehicle->notes : '') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>

                    <!-- Vehicle Image -->
                    <div>
                        <x-input-label for="image" :value="__('Vehicle Image')" />
                        <input id="image" name="image" type="file" class="block mt-1 w-full text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100"
                        />
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        
                        @if(isset($vehicle) && $vehicle->image)
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-1">{{ __('Current Image:') }}</p>
                                <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->name }}" class="h-32 w-auto object-cover rounded">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                {{ __('Cancel') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ isset($vehicle) ? __('Update Vehicle') : __('Add Vehicle') }}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calculate next service date based on last service date
        const lastServiceDate = document.getElementById('last_service_date');
        const nextServiceDate = document.getElementById('next_service_date');
        
        if (lastServiceDate && nextServiceDate) {
            lastServiceDate.addEventListener('change', function() {
                if (this.value && !nextServiceDate.value) {
                    // Set next service to 3 months after last service by default
                    const date = new Date(this.value);
                    date.setMonth(date.getMonth() + 3);
                    nextServiceDate.value = date.toISOString().slice(0, 10);
                }
            });
        }
    });
</script>
@endpush