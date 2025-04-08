<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New Journal Entry') }}
            </h2>
             <a href="{{ route('accounting.journals.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Back to Journals
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> {{-- Increased max-width --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Display Validation Errors --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Whoops! Something went wrong.</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('accounting.journals.store') }}" id="journal-form">
                        @csrf

                        {{-- Journal Header --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <x-input-label for="journal_date" :value="__('Date *')" />
                                <x-text-input type="date" name="journal_date" id="journal_date" class="mt-1 block w-full" :value="old('journal_date', now()->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('journal_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="reference" :value="__('Reference (Optional)')" />
                                <x-text-input type="text" name="reference" id="reference" class="mt-1 block w-full" :value="old('reference')" />
                                 <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                            </div>
                            {{-- Moved description below header grid --}}
                        </div>

                         {{-- Journal Description --}}
                         <div class="mb-6">
                            <x-input-label for="description" :value="__('Description / Memo *')" />
                            <textarea id="description" name="description" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>


                        {{-- Journal Entries Table --}}
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-2 border-b pb-2">Entries</h3>
                            <div class="overflow-x-auto border border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">Account *</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">Contact (Optional)</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Debit *</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Credit *</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                            {{-- <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line Description</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody id="journal-entries-body">
                                        {{-- Use partial for cleaner code and template cloning --}}
                                        @php $initialRows = max(2, count(old('entries', []))); @endphp
                                        @if(old('entries')) {{-- Check if there's old input --}}
                                            @foreach(old('entries') as $i => $entry) {{-- Use $i for index --}}
                                                @include('accounting.journals.partials.entry-row', [
                                                    'index' => $i, // Pass the current index
                                                    'entry' => $entry, // Pass the old data for this row
                                                    'accounts' => $accounts,
                                                    'contacts' => $contacts
                                                ])
                                            @endforeach
                                        @else {{-- Otherwise, show 2 empty rows --}}
                                            @include('accounting.journals.partials.entry-row', ['index' => 0, 'entry' => null, 'accounts' => $accounts, 'contacts' => $contacts])
                                            @include('accounting.journals.partials.entry-row', ['index' => 1, 'entry' => null, 'accounts' => $accounts, 'contacts' => $contacts])
                                        @endif {{-- ***** Closing @endif ADDED HERE ***** --}}
                                    </tbody>
                                    {{-- Totals Row --}}
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <th colspan="2" class="px-4 py-2 text-right text-sm font-medium text-gray-700 uppercase">Totals:</th>
                                            <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900" id="total-debit">0.00</td>
                                            <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900" id="total-credit">0.00</td>
                                            <td></td> {{-- Empty cell for action column --}}
                                        </tr>
                                         <tr id="balance-warning-row" class="hidden"> {{-- Balance warning row --}}
                                            <td colspan="5" class="text-center text-red-600 text-sm py-1">Debits and Credits must balance!</td>
                                         </tr>
                                    </tfoot>
                                </table>
                            </div>
                             <x-input-error :messages="$errors->get('entries')" class="mt-2" /> {{-- General error for entries array --}}
                             {{-- Catch specific item errors if using dot notation in validation --}}
                             {{-- @foreach($errors->get('entries.*') as $message)
                                 <p class="text-red-500 text-xs mt-1">{{ $message[0] }}</p>
                             @endforeach --}}
                            <div class="mt-4">
                                <button type="button" id="add-entry" class="text-sm text-indigo-600 hover:text-indigo-800 focus:outline-none">
                                    + Add Another Line
                                </button>
                            </div>
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('accounting.journals.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            {{-- Disable button initially if totals don't balance --}}
                            <x-primary-button id="save-journal-button" type="submit" disabled class="opacity-50 cursor-not-allowed">
                                {{ __('Save Journal Entry') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- Template for new journal entry rows (using the partial) --}}
    <template id="journal-entry-template">
         @include('accounting.journals.partials.entry-row', [
            'index' => '__INDEX__',
            'entry' => null,
            'accounts' => $accounts,
            'contacts' => $contacts
         ])
    </template>


    {{-- JavaScript for adding/removing rows and calculating totals --}}
    @push('scripts')
    <script>
         document.addEventListener('DOMContentLoaded', function() {
            const entriesContainer = document.getElementById('journal-entries-body');
            const addEntryButton = document.getElementById('add-entry');
            const entryTemplateContainer = document.getElementById('journal-entry-template'); // Get the template tag
            const totalDebitEl = document.getElementById('total-debit');
            const totalCreditEl = document.getElementById('total-credit');
            const balanceWarningRow = document.getElementById('balance-warning-row');
            const saveButton = document.getElementById('save-journal-button');
            // Correctly calculate starting index based on actual rendered rows
            let entryIndex = entriesContainer.querySelectorAll('.entry-row').length;

            if (!entriesContainer || !addEntryButton || !entryTemplateContainer || !totalDebitEl || !totalCreditEl || !balanceWarningRow || !saveButton) {
                console.error("Journal script: Essential elements missing.");
                return; // Stop if elements missing
            }
            // Get the actual HTML content from the template tag
            const entryTemplateHtml = entryTemplateContainer.innerHTML;

            function calculateTotals() {
                let debitTotal = 0;
                let creditTotal = 0;
                entriesContainer.querySelectorAll('.entry-row').forEach(row => {
                    debitTotal += parseFloat(row.querySelector('.entry-debit')?.value) || 0;
                    creditTotal += parseFloat(row.querySelector('.entry-credit')?.value) || 0;
                });

                totalDebitEl.textContent = debitTotal.toFixed(2);
                totalCreditEl.textContent = creditTotal.toFixed(2);

                // Check balance & enable/disable save button
                const difference = Math.abs(debitTotal - creditTotal);
                // Enable save ONLY if balanced AND total is not zero
                if (difference < 0.001 && debitTotal > 0.001) {
                    balanceWarningRow.classList.add('hidden');
                    saveButton.disabled = false;
                    saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    balanceWarningRow.classList.remove('hidden');
                    saveButton.disabled = true;
                    saveButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }

            function setupRowListeners(row) {
                 // Prevent entering both debit and credit on the same line
                row.querySelectorAll('.entry-debit, .entry-credit').forEach(input => {
                     input.addEventListener('input', (e) => {
                        const targetInput = e.target;
                        const isDebit = targetInput.classList.contains('entry-debit');
                        const otherInputClass = isDebit ? '.entry-credit' : '.entry-debit';
                        const otherInput = targetInput.closest('tr')?.querySelector(otherInputClass);

                        // If user types in one field, zero out the other if value > 0
                        if (parseFloat(targetInput.value) > 0 && otherInput && parseFloat(otherInput.value) > 0) {
                            otherInput.value = '0.00';
                        }
                        calculateTotals(); // Recalculate on any input
                    });
                     // Format on blur
                     input.addEventListener('blur', (e) => {
                         const value = parseFloat(e.target.value);
                         e.target.value = !isNaN(value) ? value.toFixed(2) : '0.00';
                         calculateTotals(); // Recalculate after formatting
                     });
                });

                // Remove button listener
                const removeButton = row.querySelector('.remove-entry');
                if (removeButton) {
                    removeButton.addEventListener('click', function() {
                        const currentRow = this.closest('tr');
                         // Only allow removal if more than 2 rows exist
                        if (entriesContainer.querySelectorAll('.entry-row').length > 2) {
                            currentRow.remove();
                            calculateTotals();
                            enableDisableRemoveButtons(); // Re-evaluate disable state
                        } else {
                             alert('A journal entry must have at least two lines.');
                        }
                    });
                }
            }

            function addEntryRow() {
                // Replace placeholder index in the template HTML
                const newRowHtml = entryTemplateHtml.replace(/__INDEX__/g, entryIndex);
                // Insert the new row HTML into the table body
                entriesContainer.insertAdjacentHTML('beforeend', newRowHtml);
                // Get the newly added row element
                const newRowElement = entriesContainer.lastElementChild; // Assumes partial includes <tr>

                if (newRowElement && newRowElement.matches('.entry-row')) { // Check if element is valid row
                     setupRowListeners(newRowElement); // Add listeners to new row inputs
                     enableDisableRemoveButtons(); // Ensure remove buttons are enabled correctly
                     entryIndex++;
                     calculateTotals(); // Recalculate after adding
                } else {
                    console.error("Failed to add new journal entry row element or template structure mismatch.");
                }
            }

             function enableDisableRemoveButtons() {
                 const rows = entriesContainer.querySelectorAll('.entry-row');
                 const canRemove = rows.length > 2; // Can only remove if more than 2 exist
                 rows.forEach((row) => {
                     const btn = row.querySelector('.remove-entry');
                     if (btn) btn.disabled = !canRemove;
                 });
             }


            // Add listener to the main Add button
            addEntryButton.addEventListener('click', addEntryRow);

            // Setup listeners for existing rows on page load
            entriesContainer.querySelectorAll('.entry-row').forEach(row => {
                setupRowListeners(row);
            });

            // Initial calculation & button state
            calculateTotals();
            enableDisableRemoveButtons();

        });
    </script>
    @endpush

</x-app-layout>