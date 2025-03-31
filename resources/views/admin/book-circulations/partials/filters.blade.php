<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <h3 class="text-md font-medium text-gray-700 mb-3">{{ __('Filter Circulation Records') }}</h3>
    
    <form method="GET" action="{{ route('book-circulations.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

            <!-- Category Filter -->
            <div>
                <x-input-label for="category_id" :value="__('Book Category')" />
                <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Member Type Filter -->
            <div>
                <x-input-label for="member_type" :value="__('Member Type')" />
                <select id="member_type" name="member_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="student" {{ request('member_type') == 'student' ? 'selected' : '' }}>{{ __('Student') }}</option>
                    <option value="staff" {{ request('member_type') == 'staff' ? 'selected' : '' }}>{{ __('Staff') }}</option>
                    <option value="external" {{ request('member_type') == 'external' ? 'selected' : '' }}>{{ __('External') }}</option>
                </select>
            </div>

            <!-- Date Range Filter -->
            <div>
                <x-input-label for="date_type" :value="__('Date Range')" />
                <div class="flex items-center space-x-2 mb-2">
                    <select id="date_type" name="date_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="issue_date" {{ request('date_type') == 'issue_date' ? 'selected' : '' }}>{{ __('Issue Date') }}</option>
                        <option value="due_date" {{ request('date_type') == 'due_date' ? 'selected' : '' }}>{{ __('Due Date') }}</option>
                        <option value="return_date" {{ request('date_type') == 'return_date' ? 'selected' : '' }}>{{ __('Return Date') }}</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <x-text-input id="date_from" type="date" name="date_from" :value="request('date_from')" class="mt-1 block w-full" placeholder="{{ __('From') }}" />
                    <x-text-input id="date_to" type="date" name="date_to" :value="request('date_to')" class="mt-1 block w-full" placeholder="{{ __('To') }}" />
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <x-secondary-button type="button" onclick="window.location.href='{{ route('book-circulations.index') }}'" class="mr-2">
                {{ __('Reset') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ __('Apply Filters') }}
            </x-primary-button>
        </div>
    </form>
</div>