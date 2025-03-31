<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <h3 class="text-md font-medium text-gray-700 mb-3">{{ __('Filter Allocations') }}</h3>
    
    <form method="GET" action="{{ route('asset-allocations.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Asset Filter -->
            <div>
                <x-input-label for="asset_id" :value="__('Asset')" />
                <select id="asset_id" name="asset_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Assets') }}</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                            {{ $asset->name }} ({{ $asset->asset_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Allocation Type Filter -->
            <div>
                <x-input-label for="allocatable_type" :value="__('Allocated To')" />
                <select id="allocatable_type" name="allocatable_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="App\Models\Staff" {{ request('allocatable_type') == 'App\Models\Staff' ? 'selected' : '' }}>{{ __('Staff') }}</option>
                    <option value="App\Models\Student" {{ request('allocatable_type') == 'App\Models\Student' ? 'selected' : '' }}>{{ __('Student') }}</option>
                    <option value="App\Models\Department" {{ request('allocatable_type') == 'App\Models\Department' ? 'selected' : '' }}>{{ __('Department') }}</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <x-input-label for="status" :value="__('Status')" />
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Filter -->
            <div>
                <x-input-label for="date_range" :value="__('Allocation Date')" />
                <div class="flex space-x-2">
                    <x-text-input id="date_from" type="date" name="date_from" :value="request('date_from')" class="mt-1 block w-full" placeholder="{{ __('From') }}" />
                    <x-text-input id="date_to" type="date" name="date_to" :value="request('date_to')" class="mt-1 block w-full" placeholder="{{ __('To') }}" />
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <x-secondary-button type="button" onclick="window.location.href='{{ route('asset-allocations.index') }}'" class="mr-2">
                {{ __('Reset') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ __('Apply Filters') }}
            </x-primary-button>
        </div>
    </form>
</div>