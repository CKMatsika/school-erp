<form method="GET" action="{{ route('support-ticket.index') }}" class="bg-gray-50 p-4 rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Status Filter -->
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Statuses') }}</option>
                @foreach(['New', 'Open', 'In Progress', 'Resolved', 'Closed'] as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Priority Filter -->
        <div>
            <x-input-label for="priority" :value="__('Priority')" />
            <select id="priority" name="priority" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Priorities') }}</option>
                @foreach(['Low', 'Medium', 'High', 'Critical'] as $priority)
                    <option value="{{ $priority }}" {{ request('priority') == $priority ? 'selected' : '' }}>
                        {{ $priority }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Category Filter -->
        <div>
            <x-input-label for="category" :value="__('Category')" />
            <select id="category" name="category" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Categories') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Date Range Filter -->
        <div>
            <x-input-label for="date_range" :value="__('Date Range')" />
            <select id="date_range" name="date_range" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Time') }}</option>
                <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>{{ __('Today') }}</option>
                <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>{{ __('Yesterday') }}</option>
                <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>{{ __('This Week') }}</option>
                <option value="last_week" {{ request('date_range') == 'last_week' ? 'selected' : '' }}>{{ __('Last Week') }}</option>
                <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>{{ __('Last Month') }}</option>
                <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>{{ __('Custom Range') }}</option>
            </select>
        </div>
        
        <!-- Custom Date Range Fields (initially hidden) -->
        <div id="custom_date_container" class="md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-4 {{ request('date_range') == 'custom' ? '' : 'hidden' }}">
            <div>
                <x-input-label for="date_from" :value="__('From Date')" />
                <x-text-input id="date_from" class="block mt-1 w-full" type="date" name="date_from" :value="request('date_from')" />
            </div>
            <div>
                <x-input-label for="date_to" :value="__('To Date')" />
                <x-text-input id="date_to" class="block mt-1 w-full" type="date" name="date_to" :value="request('date_to')" />
            </div>
        </div>
    </div>
    
    <div class="flex justify-end mt-4">
        <x-secondary-button type="button" onclick="window.location.href='{{ route('support-ticket.index') }}'" class="mr-2">
            {{ __('Reset') }}
        </x-secondary-button>
        
        <x-primary-button>
            {{ __('Apply Filters') }}
        </x-primary-button>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateRangeSelect = document.getElementById('date_range');
        const customDateContainer = document.getElementById('custom_date_container');
        
        if (dateRangeSelect && customDateContainer) {
            dateRangeSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateContainer.classList.remove('hidden');
                } else {
                    customDateContainer.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush