<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <h3 class="text-md font-medium text-gray-700 mb-3">{{ __('Filter Members') }}</h3>
    
    <form method="GET" action="{{ route('library-members.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Member Type Filter -->
            <div>
                <x-input-label for="member_type" :value="__('Member Type')" />
                <select id="member_type" name="member_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="Student" {{ request('member_type') == 'Student' ? 'selected' : '' }}>{{ __('Student') }}</option>
                    <option value="Staff" {{ request('member_type') == 'Staff' ? 'selected' : '' }}>{{ __('Staff') }}</option>
                    <option value="External" {{ request('member_type') == 'External' ? 'selected' : '' }}>{{ __('External') }}</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <x-input-label for="status" :value="__('Status')" />
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>{{ __('Blocked') }}</option>
                </select>
            </div>

            <!-- Books Out Filter -->
            <div>
                <x-input-label for="books_out" :value="__('Books Out')" />
                <select id="books_out" name="books_out" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Members') }}</option>
                    <option value="with_books" {{ request('books_out') == 'with_books' ? 'selected' : '' }}>{{ __('With Books Out') }}</option>
                    <option value="without_books" {{ request('books_out') == 'without_books' ? 'selected' : '' }}>{{ __('Without Books Out') }}</option>
                    <option value="overdue" {{ request('books_out') == 'overdue' ? 'selected' : '' }}>{{ __('With Overdue Books') }}</option>
                </select>
            </div>

            <!-- Membership Date Filter -->
            <div>
                <x-input-label for="membership_date" :value="__('Membership Date')" />
                <div class="flex space-x-2">
                    <x-text-input id="date_from" type="date" name="date_from" :value="request('date_from')" class="mt-1 block w-full" placeholder="{{ __('From') }}" />
                    <x-text-input id="date_to" type="date" name="date_to" :value="request('date_to')" class="mt-1 block w-full" placeholder="{{ __('To') }}" />
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <x-secondary-button type="button" onclick="window.location.href='{{ route('library-members.index') }}'" class="mr-2">
                {{ __('Reset') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ __('Apply Filters') }}
            </x-primary-button>
        </div>
    </form>
</div>