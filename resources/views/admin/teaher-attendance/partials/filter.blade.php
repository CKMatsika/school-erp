<form method="GET" action="{{ route('teacher-attendance.index') }}" class="bg-gray-50 p-4 rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Department Filter -->
        <div>
            <x-input-label for="department" :value="__('Department')" />
            <select id="department" name="department" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Departments') }}</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" {{ request('department') == $department ? 'selected' : '' }}>
                        {{ $department }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Status Filter -->
        <div>
            <x-input-label for="status" :value="__('Attendance Status')" />
            <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="Present" {{ request('status') == 'Present' ? 'selected' : '' }}>{{ __('Present') }}</option>
                <option value="Late" {{ request('status') == 'Late' ? 'selected' : '' }}>{{ __('Late') }}</option>
                <option value="Absent" {{ request('status') == 'Absent' ? 'selected' : '' }}>{{ __('Absent') }}</option>
                <option value="Leave" {{ request('status') == 'Leave' ? 'selected' : '' }}>{{ __('On Leave') }}</option>
                <option value="Half Day" {{ request('status') == 'Half Day' ? 'selected' : '' }}>{{ __('Half Day') }}</option>
            </select>
        </div>
        
        <!-- Date Range -->
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
        
        <!-- Teacher Type -->
        <div>
            <x-input-label for="teacher_type" :value="__('Teacher Type')" />
            <select id="teacher_type" name="teacher_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">{{ __('All Types') }}</option>
                <option value="permanent" {{ request('teacher_type') == 'permanent' ? 'selected' : '' }}>{{ __('Permanent') }}</option>
                <option value="contract" {{ request('teacher_type') == 'contract' ? 'selected' : '' }}>{{ __('Contract') }}</option>
                <option value="part_time" {{ request('teacher_type') == 'part_time' ? 'selected' : '' }}>{{ __('Part-Time') }}</option>
                <option value="substitute" {{ request('teacher_type') == 'substitute' ? 'selected' : '' }}>{{ __('Substitute') }}</option>
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
        <x-secondary-button type="button" onclick="window.location.href='{{ route('teacher-attendance.index') }}'" class="mr-2">
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