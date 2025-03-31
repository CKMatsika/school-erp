<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <h3 class="text-md font-medium text-gray-700 mb-3">{{ __('Filter Assets') }}</h3>
    
    <form method="GET" action="{{ route('assets.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Category Filter -->
            <div>
                <x-input-label for="category" :value="__('Category')" />
                <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
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

            <!-- Date Range -->
            <div>
                <x-input-label for="date_from" :value="__('Purchase Date From')" />
                <x-text-input id="date_from" type="date" name="date_from" :value="request('date_from')" class="mt-1 block w-full" />
            </div>

            <div>
                <x-input-label for="date_to" :value="__('Purchase Date To')" />
                <x-text-input id="date_to" type="date" name="date_to" :value="request('date_to')" class="mt-1 block w-full" />
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <x-secondary-button type="button" onclick="window.location.href='{{ route('assets.index') }}'" class="mr-2">
                {{ __('Reset') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ __('Apply Filters') }}
            </x-primary-button>
        </div>
    </form>
</div>