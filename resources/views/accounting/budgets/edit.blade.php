<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Budget') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Edit Budget') }}: {{ $budget->name }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('accounting.budgets.show', $budget->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                {{ __('Back to Details') }}
                            </a>
                        </div>
                    </div>

                    @include('components.flash-messages')

                    <form action="{{ route('accounting.budgets.update', $budget->id) }}" method="POST" id="budget-form">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Budget Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('name', $budget->name) }}" required>
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="fiscal_year" class="block text-sm font-medium text-gray-700">{{ __('Fiscal Year') }} <span class="text-red-500">*</span></label>
                                <select name="fiscal_year" id="fiscal_year" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                    @php
                                        $currentYear = date('Y');
                                        $years = range($currentYear - 2, $currentYear + 3);
                                    @endphp
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ old('fiscal_year', $budget->fiscal_year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                                @error('fiscal_year')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">{{ __('Start Date') }} <span class="text-red-500">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('start_date', $budget->start_date ? \Carbon\Carbon::parse($budget->start_date)->format('Y-m-d') : '') }}" required>
                                @error('start_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">{{ __('End Date') }} <span class="text-red-500">*</span></label>
                                <input type="date" name="end_date" id="end_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('end_date', $budget->end_date ? \Carbon\Carbon::parse($budget->end_date)->format('Y-m-d') : '') }}" required>
                                @error('end_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="total_amount" class="block text-sm font-medium text-gray-700">{{ __('Total Budget Amount') }} <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="total_amount" id="total_amount" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" step="0.01" min="0" value="{{ old('total_amount', $budget->total_amount) }}" required>
                                </div>
                                @error('total_amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                                <select name="status" id="status" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="active" {{ old('status', $budget->status) == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="draft" {{ old('status', $budget->status) == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                    <option value="closed" {{ old('status', $budget->status) == 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                                </select>
                                @error('status')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $budget->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <h4 class="text-md font-medium text-gray-700 mb-4">{{ __('Budget Categories') }} <span class="text-red-500">*</span></h4>
                        
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <div id="budget-categories">
                                <div class="grid grid-cols-12 gap-4 mb-2 items-center">
                                    <div class="col-span-5 font-medium text-gray-700 text-sm">{{ __('Category') }}</div>
                                    <div class="col-span-5 font-medium text-gray-700 text-sm">{{ __('Amount') }}</div>
                                    <div class="col-span-2"></div>
                                </div>
                                
                                @if(isset($budget->categories) && count($budget->categories) > 0)
                                    @foreach($budget->categories as $index => $category)
                                    <div class="budget-category-row grid grid-cols-12 gap-4 mb-3 items-center">
                                        <div class="col-span-5">
                                            <input type="text" name="categories[]" class="category-name focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Category name" value="{{ $category->name }}" required>
                                            @error('categories.'.$index)
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="col-span-5">
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">$</span>
                                                </div>
                                                <input type="number" name="category_amounts[]" class="category-amount focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" step="0.01" min="0" value="{{ $category->amount }}" required>
                                                @error('category_amounts.'.$index)
                                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-span-2">
                                            <button type="button" class="remove-category text-red-600 hover:text-red-900">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                <div class="budget-category-row grid grid-cols-12 gap-4 mb-3 items-center">
                                    <div class="col-span-5">
                                        <input type="text" name="categories[]" class="category-name focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Category name" required>
                                    </div>
                                    <div class="col-span-5">
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" name="category_amounts[]" class="category-amount focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-span-2">
                                        <button type="button" class="remove-category text-red-600 hover:text-red-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            <button type="button" id="add-category" class="mt-2 inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                {{ __('Add Category') }}
                            </button>
                            
                            <div id="category-totals" class="mt-4 text-right text-sm">
                                <span class="font-medium">{{ __('Categories Total:') }}</span>
                                <span id="categories-sum">$0.00</span>
                                <span class="mx-2">|</span>
                                <span class="font-medium">{{ __('Budget Remaining:') }}</span>
                                <span id="budget-remaining">$0.00</span>
                            </div>
                            
                            <div id="categories-error" class="mt-2 text-red-500 text-sm hidden">
                                {{ __('Warning: Total of categories exceeds the budget amount!') }}
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('accounting.budgets.show', $budget->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" id="submit-button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Update Budget') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('budget-categories');
            const addButton = document.getElementById('add-category');
            const form = document.getElementById('budget-form');
            const totalAmountInput = document.getElementById('total_amount');
            const categoriesSumDisplay = document.getElementById('categories-sum');
            const budgetRemainingDisplay = document.getElementById('budget-remaining');
            const categoriesError = document.getElementById('categories-error');
            
            // Function to format currency
            function formatCurrency(amount) {
                return '$' + parseFloat(amount).toFixed(2);
            }
            
            // Function to handle remove category button click
            function handleRemoveClick(button) {
                button.addEventListener('click', function() {
                    const rows = document.querySelectorAll('.budget-category-row');
                    if (rows.length > 1) {
                        button.closest('.budget-category-row').remove();
                        updateCategoryTotals();
                    } else {
                        // If it's the last row, just clear the inputs
                        const row = button.closest('.budget-category-row');
                        row.querySelectorAll('input').forEach(input => {
                            input.value = '';
                        });
                        alert('At least one category is required');
                    }
                });
            }
            
            // Function to update category totals
            function updateCategoryTotals() {
                const amountInputs = document.querySelectorAll('.category-amount');
                let total = 0;
                
                amountInputs.forEach(input => {
                    if (input.value && !isNaN(input.value)) {
                        total += parseFloat(input.value);
                    }
                });
                
                const budgetTotal = parseFloat(totalAmountInput.value) || 0;
                const remaining = budgetTotal - total;
                
                categoriesSumDisplay.textContent = formatCurrency(total);
                budgetRemainingDisplay.textContent = formatCurrency(remaining);
                
                // Show warning if categories exceed budget
                if (total > budgetTotal && budgetTotal > 0) {
                    categoriesError.classList.remove('hidden');
                    budgetRemainingDisplay.classList.add('text-red-500');
                    budgetRemainingDisplay.classList.remove('text-green-500');
                } else {
                    categoriesError.classList.add('hidden');
                    budgetRemainingDisplay.classList.remove('text-red-500');
                    budgetRemainingDisplay.classList.add('text-green-500');
                }
            }
            
            // Add event listener to add button
            addButton.addEventListener('click', function() {
                // Clone the first row
                const firstRow = document.querySelector('.budget-category-row');
                const newRow = firstRow.cloneNode(true);
                
                // Clear input values
                newRow.querySelectorAll('input').forEach(input => {
                    input.value = '';
                });
                
                // Add the new row to the container
                container.appendChild(newRow);
                
                // Add event listeners to the new inputs
                newRow.querySelectorAll('.category-amount').forEach(input => {
                    input.addEventListener('input', updateCategoryTotals);
                });
                
                // Add event listener to the new remove button
                const newRemoveButton = newRow.querySelector('.remove-category');
                handleRemoveClick(newRemoveButton);
            });
            
            // Initialize event listeners for existing remove buttons
            document.querySelectorAll('.remove-category').forEach(button => {
                handleRemoveClick(button);
            });
            
            // Initialize event listeners for amount inputs
            document.querySelectorAll('.category-amount').forEach(input => {
                input.addEventListener('input', updateCategoryTotals);
            });
            
            // Update when total budget changes
            totalAmountInput.addEventListener('input', updateCategoryTotals);
            
            // Form validation before submit
            form.addEventListener('submit', function(e) {
                // Check if at least one category is defined
                const categoryNames = document.querySelectorAll('.category-name');
                const categoryAmounts = document.querySelectorAll('.category-amount');
                let valid = false;
                
                for (let i = 0; i < categoryNames.length; i++) {
                    if (categoryNames[i].value.trim() !== '' && categoryAmounts[i].value.trim() !== '') {
                        valid = true;
                        break;
                    }
                }
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please define at least one budget category with name and amount');
                    return false;
                }
                
                // Optional: Warn if categories exceed budget
                const totalBudget = parseFloat(totalAmountInput.value) || 0;
                let categoriesTotal = 0;
                
                categoryAmounts.forEach(input => {
                    if (input.value && !isNaN(input.value)) {
                        categoriesTotal += parseFloat(input.value);
                    }
                });
                
                if (categoriesTotal > totalBudget) {
                    if (!confirm('The sum of categories exceeds the total budget. Do you want to continue anyway?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
            
            // Initialize the totals on page load
            updateCategoryTotals();
        });
    </script>
    @endpush
</x-app-layout>