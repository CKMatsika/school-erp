<form method="GET" action="{{ route('vehicle.index') }}" class="bg-gray-50 p-4 rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Vehicle Type Filter -->
        <div>
            <x-input-label for="type" :value="__('Vehicle Type')" />
            <select id="type" name="type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Types') }}</option>
                @foreach(['Bus', 'Mini Bus', 'Van', 'Car', 'Truck'] as $type)
                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Status Filter -->
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="In Maintenance" {{ request('status') == 'In Maintenance' ? 'selected' : '' }}>{{ __('In Maintenance') }}</option>
                <option value="Out of Service" {{ request('status') == 'Out of Service' ? 'selected' : '' }}>{{ __('Out of Service') }}</option>
                <option value="Reserved" {{ request('status') == 'Reserved' ? 'selected' : '' }}>{{ __('Reserved') }}</option>
            </select>
        </div>
        
        <!-- Driver Filter -->
        <div>
            <x-input-label for="driver_id" :value="__('Driver')" />
            <select id="driver_id" name="driver_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Drivers') }}</option>
                <option value="unassigned" {{ request('driver_id') == 'unassigned' ? 'selected' : '' }}>{{ __('Unassigned') }}</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                        {{ $driver->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Capacity Filter -->
        <div>
            <x-input-label for="capacity" :value="__('Minimum Capacity')" />
            <x-text-input id="capacity" class="block mt-1 w-full" type="number" name="capacity" :value="request('capacity')" min="1" placeholder="{{ __('Minimum seats') }}" />
        </div>
    </div>
    
    <div class="flex justify-end mt-4">
        <x-secondary-button type="button" onclick="window.location.href='{{ route('vehicle.index') }}'" class="mr-2">
            {{ __('Reset') }}
        </x-secondary-button>
        
        <x-primary-button>
            {{ __('Apply Filters') }}
        </x-primary-button>
    </div>
</form>