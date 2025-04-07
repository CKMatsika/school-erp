<form method="GET" action="{{ route('trip.index') }}" class="bg-gray-50 p-4 rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Status Filter -->
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Statuses') }}</option>
                @foreach(['Planned', 'Approved', 'In Progress', 'Completed', 'Cancelled'] as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Grade/Class Filter -->
        <div>
            <x-input-label for="grade_class" :value="__('Grade/Class')" />
            <select id="grade_class" name="grade_class" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Grades/Classes') }}</option>
                @foreach($gradeClasses as $gradeClass)
                    <option value="{{ $gradeClass }}" {{ request('grade_class') == $gradeClass ? 'selected' : '' }}>
                        {{ $gradeClass }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Date Range Type -->
        <div>
            <x-input-label for="date_type" :value="__('Date Type')" />
            <select id="date_type" name="date_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="start_date" {{ request('date_type', 'start_date') == 'start_date' ? 'selected' : '' }}>{{ __('Start Date') }}</option>
                <option value="end_date" {{ request('date_type') == 'end_date' ? 'selected' : '' }}>{{ __('End Date') }}</option>
                <option value="created_at" {{ request('date_type') == 'created_at' ? 'selected' : '' }}>{{ __('Created Date') }}</option>
            </select>
        </div>
        
        <!-- Date Range Filter -->
        <div>
            <x-input-label for="date_range" :value="__('Date Range')" />
            <select id="date_range" name="date_range" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Time') }}</option>
                <option value="upcoming" {{ request('date_range') == 'upcoming' ? 'selected' : '' }}>{{ __('Upcoming') }}</option>
                <option value="past" {{ request('date_range') == 'past' ? 'selected' : '' }}>{{ __('Past') }}</option>
                <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                <option value="next_month" {{ request('date_range') == 'next_month' ? 'selected' : '' }}>{{ __('Next Month') }}</option>
                <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
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
        <x-secondary-button type="button" onclick="window.location.href='{{ route('trip.index') }}'" class="mr-2">
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