<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <h3 class="text-md font-medium text-gray-700 mb-3">{{ __('Filter Books') }}</h3>
    
    <form method="GET" action="{{ route('books.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Category Filter -->
            <div>
                <x-input-label for="category_id" :value="__('Category')" />
                <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Publication Year Filter -->
            <div>
                <x-input-label for="publication_year" :value="__('Publication Year')" />
                <select id="publication_year" name="publication_year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Years') }}</option>
                    @foreach ($publicationYears as $year)
                        <option value="{{ $year }}" {{ request('publication_year') == $year ? 'selected' : '' }}>
                            {{ $year }}
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

            <!-- Availability Filter -->
            <div>
                <x-input-label for="availability" :value="__('Availability')" />
                <select id="availability" name="availability" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('All Books') }}</option>
                    <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>{{ __('Available Books') }}</option>
                    <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>{{ __('Unavailable Books') }}</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <x-secondary-button type="button" onclick="window.location.href='{{ route('books.index') }}'" class="mr-2">
                {{ __('Reset') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ __('Apply Filters') }}
            </x-primary-button>
        </div>
    </form>
</div>