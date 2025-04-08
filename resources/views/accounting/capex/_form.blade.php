@csrf
{{-- Use PUT method spoofing for the update route --}}
@isset($capexItem)
    @method('PUT')
@endisset

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Name -->
    <div>
        <label for="name" class="block font-medium text-sm text-gray-700">{{ __('Project Name / Title') }} <span class="text-red-600">*</span></label>
        <input type="text" name="name" id="name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
               value="{{ old('name', $capexItem->name ?? '') }}" required>
        @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <!-- Budgeted Amount -->
    <div>
        <label for="budgeted_amount" class="block font-medium text-sm text-gray-700">{{ __('Budgeted Amount') }} <span class="text-red-600">*</span></label>
        <input type="number" name="budgeted_amount" id="budgeted_amount" step="0.01" min="0" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
               value="{{ old('budgeted_amount', $capexItem->budgeted_amount ?? '') }}" required>
        @error('budgeted_amount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <!-- Status -->
    <div>
        <label for="status" class="block font-medium text-sm text-gray-700">{{ __('Status') }}</label>
        <select name="status" id="status" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            {{-- Define your relevant statuses --}}
            <option value="planned" {{ old('status', $capexItem->status ?? 'planned') == 'planned' ? 'selected' : '' }}>{{ __('Planned') }}</option>
            <option value="approved" {{ old('status', $capexItem->status ?? '') == 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
            <option value="in_progress" {{ old('status', $capexItem->status ?? '') == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
            <option value="completed" {{ old('status', $capexItem->status ?? '') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
            <option value="on_hold" {{ old('status', $capexItem->status ?? '') == 'on_hold' ? 'selected' : '' }}>{{ __('On Hold') }}</option>
            <option value="cancelled" {{ old('status', $capexItem->status ?? '') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
        </select>
        @error('status') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <!-- Start Date -->
    <div>
        <label for="start_date" class="block font-medium text-sm text-gray-700">{{ __('Start Date') }}</label>
        <input type="date" name="start_date" id="start_date" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
               {{-- UPDATED: Using isset() for clarity and robustness --}}
               value="{{ old('start_date', isset($capexItem->start_date) ? \Carbon\Carbon::parse($capexItem->start_date)->format('Y-m-d') : '') }}">
        @error('start_date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

     <!-- Expected Completion Date -->
    <div>
        <label for="completion_date" class="block font-medium text-sm text-gray-700">{{ __('Expected Completion Date') }}</label>
        <input type="date" name="completion_date" id="completion_date" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
               {{-- UPDATED: Using isset() for clarity and robustness --}}
               value="{{ old('completion_date', isset($capexItem->completion_date) ? \Carbon\Carbon::parse($capexItem->completion_date)->format('Y-m-d') : '') }}">
        @error('completion_date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <!-- Associated Account (Optional Example) -->
    {{-- <div>
        <label for="chart_of_account_id" class="block font-medium text-sm text-gray-700">{{ __('Associated Account (Optional)') }}</label>
        <select name="chart_of_account_id" id="chart_of_account_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
             <option value="">{{ __('-- Select Account --') }}</option>
             @isset($accounts) // Pass $accounts from controller if needed
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('chart_of_account_id', $capexItem->chart_of_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->name }} ({{ $account->code }})
                    </option>
                @endforeach
            @endisset
        </select>
        @error('chart_of_account_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div> --}}

</div>

<!-- Description -->
<div class="mt-6">
    <label for="description" class="block font-medium text-sm text-gray-700">{{ __('Description') }}</label>
    <textarea name="description" id="description" rows="4" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $capexItem->description ?? '') }}</textarea>
    @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
</div>