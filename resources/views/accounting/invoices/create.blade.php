<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Invoice') }}
            </h2>
            <a href="{{ route('accounting.invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Back to Invoices
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif
                     {{-- Display Validation Errors (More detailed) --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Whoops!</strong>
                            <span class="block sm:inline">Please correct the errors below.</span>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <form method="POST" action="{{ route('accounting.invoices.store') }}">
                        @csrf

                        <!-- Invoice Details Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Contact -->
                                <div>
                                    <x-input-label for="contact_id" :value="__('Contact *')" />
                                    <select id="contact_id" name="contact_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm" required>
                                        <option value="">-- Select Contact --</option>
                                        @foreach($contacts as $contact)
                                            {{-- Assuming Contact model has name and contact_type --}}
                                            <option value="{{ $contact->id }}" {{ old('contact_id') == $contact->id ? 'selected' : '' }}>
                                                {{ $contact->name }} {{ $contact->contact_type ? '(' . ucfirst($contact->contact_type) . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('contact_id')" class="mt-2" />
                                </div>


                                <!-- Invoice Number -->
                                <div>
                                    <x-input-label for="invoice_number" :value="__('Invoice Number *')" />
                                    <x-text-input type="text" name="invoice_number" id="invoice_number" class="mt-1 block w-full" :value="old('invoice_number', $nextInvoiceNumber ?? 'INV-' . date('Ymd') . '-001')" required />
                                    <x-input-error :messages="$errors->get('invoice_number')" class="mt-2" />
                                </div>


                                <!-- Reference -->
                                <div>
                                    <x-input-label for="reference" :value="__('Reference (Optional)')" />
                                     <x-text-input type="text" name="reference" id="reference" class="mt-1 block w-full" :value="old('reference')" />
                                     <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                                </div>


                                <!-- Issue Date -->
                                <div>
                                    <x-input-label for="issue_date" :value="__('Issue Date *')" />
                                    <x-text-input type="date" name="issue_date" id="issue_date" class="mt-1 block w-full" :value="old('issue_date', now()->format('Y-m-d'))" required />
                                     <x-input-error :messages="$errors->get('issue_date')" class="mt-2" />
                                </div>


                                <!-- Due Date -->
                                <div>
                                     <x-input-label for="due_date" :value="__('Due Date *')" />
                                     <x-text-input type="date" name="due_date" id="due_date" class="mt-1 block w-full" :value="old('due_date', now()->addDays(30)->format('Y-m-d'))" required />
                                     <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                                </div>


                                <!-- Status (Keep as Draft on Create Form) -->
                                {{-- Removed status dropdown - invoices always start as draft --}}
                                {{-- You can add it back if you need to create 'sent' directly, but update controller --}}
                                {{-- <div>
                                    <x-input-label for="status" :value="__('Status *')" />
                                    <select id="status" name="status" class="mt-1 block w-full..." required>
                                        <option value="draft" selected>Draft</option>
                                        <option value="sent" {{ old('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                    </select>
                                     <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div> --}}
                            </div>
                        </div>

                        <!-- Invoice Items Section -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Invoice Items</h3>
                                <button type="button" id="add-item" class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Add Item
                                </button>
                            </div>

                            <div class="bg-white overflow-x-auto border border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200" id="items-table">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/6">Description</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Qty</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Unit Price</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Tax Rate</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Account</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Amount</th>
                                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="items-container">
                                         {{-- Loop through old input if validation fails, otherwise show one empty row --}}
                                        @php $itemIndex = 0; @endphp
                                        @if(old('items'))
                                            @foreach(old('items') as $item)
                                                <tr class="item-row">
                                                    <td class="px-4 py-2 whitespace-nowrap">
                                                        <textarea name="items[{{ $itemIndex }}][description]" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm" required>{{ $item['description'] }}</textarea>
                                                        <x-input-error :messages="$errors->get('items.'.$itemIndex.'.description')" class="mt-1" />
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap">
                                                        <x-text-input type="number" name="items[{{ $itemIndex }}][quantity]" class="item-quantity mt-1 block w-full text-right sm:text-sm" :value="$item['quantity'] ?? 1" min="0.0001" step="any" required />
                                                        <x-input-error :messages="$errors->get('items.'.$itemIndex.'.quantity')" class="mt-1" />
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap">
                                                         <x-text-input type="number" name="items[{{ $itemIndex }}][unit_price]" class="item-price mt-1 block w-full text-right sm:text-sm" :value="$item['unit_price'] ?? '0.00'" min="0" step="0.01" required />
                                                         <x-input-error :messages="$errors->get('items.'.$itemIndex.'.unit_price')" class="mt-1" />
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap">
                                                        <select name="items[{{ $itemIndex }}][tax_rate_id]" class="item-tax mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm">
                                                            <option value="">No Tax</option>
                                                            @foreach($taxRates as $tax)
                                                                <option value="{{ $tax->id }}" data-rate="{{ $tax->rate ?? 0 }}" {{ ($item['tax_rate_id'] ?? '') == $tax->id ? 'selected' : '' }}>
                                                                    {{ $tax->name }} ({{ $tax->rate ?? 0 }}%)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                         <x-input-error :messages="$errors->get('items.'.$itemIndex.'.tax_rate_id')" class="mt-1" />
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap">
                                                        <select name="items[{{ $itemIndex }}][account_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm" required>
                                                             <option value="">-- Select Account --</option>
                                                            @foreach($accounts as $account)
                                                                <option value="{{ $account->id }}" {{ ($item['account_id'] ?? '') == $account->id ? 'selected' : '' }}>
                                                                    {{ $account->name }} ({{ $account->account_code }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                         <x-input-error :messages="$errors->get('items.'.$itemIndex.'.account_id')" class="mt-1" />
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-right">
                                                        <span class="item-total text-sm text-gray-900 font-medium">0.00</span>
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-center">
                                                        {{-- Disable remove button for the first row if only one exists --}}
                                                        <button type="button" class="text-red-600 hover:text-red-900 remove-item" {{ $loop->count <= 1 ? 'disabled' : '' }}>Remove</button>
                                                    </td>
                                                </tr>
                                                @php $itemIndex++; @endphp
                                            @endforeach
                                        @else
                                            {{-- Default First Row - Serves as Template --}}
                                            <tr class="item-row">
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <textarea name="items[0][description]" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm" required></textarea>
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <x-text-input type="number" name="items[0][quantity]" class="item-quantity mt-1 block w-full text-right sm:text-sm" value="1" min="0.0001" step="any" required />
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <x-text-input type="number" name="items[0][unit_price]" class="item-price mt-1 block w-full text-right sm:text-sm" value="0.00" min="0" step="0.01" required />
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <select name="items[0][tax_rate_id]" class="item-tax mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm">
                                                        <option value="">No Tax</option>
                                                        @foreach($taxRates as $tax)
                                                            <option value="{{ $tax->id }}" data-rate="{{ $tax->rate ?? 0 }}">{{ $tax->name }} ({{ $tax->rate ?? 0 }}%)</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <select name="items[0][account_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm" required>
                                                         <option value="">-- Select Account --</option>
                                                        @foreach($accounts as $account)
                                                            <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->account_code }})</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap text-right">
                                                    <span class="item-total text-sm text-gray-900 font-medium">0.00</span>
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap text-center">
                                                    <button type="button" class="text-red-600 hover:text-red-900 remove-item" disabled>Remove</button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <x-input-error :messages="$errors->get('items')" class="mt-2" />
                        </div>


                        <!-- Summary Section -->
                        <div class="mb-8">
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex justify-end">
                                    <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4">
                                        <div class="flex justify-between py-1">
                                            <span class="text-sm text-gray-600">Subtotal:</span>
                                            <span class="text-sm font-medium text-gray-900" id="subtotal">0.00</span>
                                        </div>
                                        <div class="flex justify-between py-1">
                                            <span class="text-sm text-gray-600">Tax:</span>
                                            <span class="text-sm font-medium text-gray-900" id="tax-total">0.00</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-t mt-1">
                                            <span class="text-base font-bold text-gray-800">Total:</span>
                                            <span class="text-base font-bold text-gray-800" id="grand-total">0.00</span>
                                            {{-- Hidden inputs to submit calculated totals --}}
                                            <input type="hidden" name="subtotal" id="subtotal-input" value="0">
                                            <input type="hidden" name="tax_amount" id="tax-input" value="0">
                                            <input type="hidden" name="total" id="total-input" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Details -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Details</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <!-- Notes -->
                                <div>
                                    <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm">{{ old('notes') }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                                </div>

                                <!-- Terms & Conditions -->
                                <div>
                                     <x-input-label for="terms" :value="__('Terms & Conditions (Optional)')" />
                                    <textarea id="terms" name="terms" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm">{{ old('terms') }}</textarea>
                                     <x-input-error :messages="$errors->get('terms')" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                             <a href="{{ route('accounting.invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button type="submit">
                                {{ __('Save Draft Invoice') }}
                            </x-primary-button>
                            {{-- Optionally add a "Save and Send" button if desired, setting status=sent --}}
                            {{-- <button type="submit" name="status" value="sent" class="ml-4 inline-flex ...">Save and Send</button> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- *** SCRIPT USING CLONING (MORE ROBUST) *** --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Start index based on how many 'old' items exist, otherwise start at 1 for the next item
            let itemIndex = {{ old('items') ? count(old('items')) : 1 }};
            const addItemButton = document.getElementById('add-item');
            const itemsContainer = document.getElementById('items-container');
            // Get the first row directly as template ONLY IF IT EXISTS
            const itemRowTemplate = itemsContainer?.querySelector('.item-row'); // Use optional chaining

            if (!addItemButton || !itemsContainer || !itemRowTemplate) {
                console.error("Invoice item script: Could not find essential elements (button, container, or template row). Add item functionality disabled.");
                if(addItemButton) addItemButton.disabled = true; // Disable button if setup fails
                return; // Stop script execution
            }

            // Function to clone and append a new row
            function addNewItemRow() {
                const newRow = itemRowTemplate.cloneNode(true); // Clone the first row deeply

                // Clear input values in the clone and update names/IDs
                newRow.querySelectorAll('input, select, textarea').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        // Update the index in the name attribute
                        input.setAttribute('name', name.replace(/items\[\d+\]/, `items[${itemIndex}]`));
                    }
                    // Also update IDs if they use the index (add IDs to your inputs/selects if needed)
                    // const id = input.getAttribute('id');
                    // if (id) { input.setAttribute('id', id.replace(/_\d+_/, `_${itemIndex}_`)); }

                    // Reset values for the new row
                    if (input.tagName === 'TEXTAREA') {
                        input.value = '';
                    } else if (input.type === 'number' && input.name.includes('[quantity]')) {
                         input.value = '1'; // Default quantity
                    } else if (input.type === 'number') {
                        input.value = '0.00';
                    } else if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0; // Reset select to the first option (e.g., "No Tax", "-- Select Account --")
                    } else if (input.type !== 'button' && input.type !== 'hidden') {
                         input.value = '';
                    }

                    // Clear previous validation errors if any (simple approach)
                    const errorElement = input.closest('td')?.querySelector('.text-red-500');
                    if(errorElement) errorElement.remove();

                });

                // Clear the calculated total span in the clone
                const itemTotalSpan = newRow.querySelector('.item-total');
                if(itemTotalSpan) itemTotalSpan.textContent = '0.00';

                // Ensure the remove button in the new row is enabled
                const removeBtn = newRow.querySelector('.remove-item');
                if (removeBtn) removeBtn.disabled = false;

                itemsContainer.appendChild(newRow); // Append the modified clone
                setupItemListeners(newRow);      // Add listeners to the new row
                enableAllRemoveButtons();      // Make sure all remove buttons (except potentially first) are enabled
                itemIndex++;                     // Increment index for the *next* row
                calculateTotals();               // Recalculate after adding
            }

            // Attach listener to Add Item button
            addItemButton.addEventListener('click', addNewItemRow);

            // Handle remove item (delegated listener on container)
            itemsContainer.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('remove-item')) {
                    const row = e.target.closest('tr');
                    // Prevent removing the last row
                    if (itemsContainer.querySelectorAll('.item-row').length > 1) {
                        row.remove();
                        calculateTotals(); // Recalculate after removing
                        // After removing, re-check if only one row remains and disable its button
                        if (itemsContainer.querySelectorAll('.item-row').length === 1) {
                             const firstRemoveBtn = itemsContainer.querySelector('.item-row .remove-item');
                             if (firstRemoveBtn) firstRemoveBtn.disabled = true;
                        }
                    } else {
                        // console.warn("Cannot remove the last item row."); // Optional warning
                    }
                }
            });

            // Setup item listeners for calculation on a given row
            function setupItemListeners(row) {
                const inputs = row.querySelectorAll('.item-quantity, .item-price, .item-tax');
                inputs.forEach(el => {
                    // Use 'input' event for quicker feedback on number fields
                    const eventType = (el.type === 'number' || el.type === 'text') ? 'input' : 'change';
                    el.addEventListener(eventType, calculateTotals);
                });
            }

            // Calculate all totals
            function calculateTotals() {
                let subtotal = 0;
                let taxTotal = 0;

                itemsContainer.querySelectorAll('.item-row').forEach(row => {
                    const quantity = parseFloat(row.querySelector('.item-quantity')?.value) || 0;
                    const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
                    const taxSelect = row.querySelector('.item-tax');
                    const itemTotalSpan = row.querySelector('.item-total');

                    let taxRate = 0;
                    if (taxSelect?.value && taxSelect.options[taxSelect.selectedIndex]) { // Check if option exists
                        taxRate = parseFloat(taxSelect.options[taxSelect.selectedIndex].dataset.rate) || 0;
                    }

                    const lineSubtotal = quantity * price;
                    const lineTax = lineSubtotal * (taxRate / 100);

                    subtotal += lineSubtotal;
                    taxTotal += lineTax;

                    if(itemTotalSpan) {
                        // Display line total including tax
                        itemTotalSpan.textContent = (lineSubtotal + lineTax).toFixed(2);
                    }
                });

                const grandTotal = subtotal + taxTotal;

                // Update display elements safely
                document.getElementById('subtotal').textContent = subtotal.toFixed(2);
                document.getElementById('tax-total').textContent = taxTotal.toFixed(2);
                document.getElementById('grand-total').textContent = grandTotal.toFixed(2);

                // Update hidden inputs for form submission
                document.getElementById('subtotal-input').value = subtotal.toFixed(2);
                document.getElementById('tax-input').value = taxTotal.toFixed(2);
                document.getElementById('total-input').value = grandTotal.toFixed(2);
            }

            // Enable/disable remove buttons based on row count
             function enableAllRemoveButtons() {
                 const rows = itemsContainer.querySelectorAll('.item-row');
                 const disableFirst = rows.length === 1;
                 rows.forEach((row, index) => {
                     const btn = row.querySelector('.remove-item');
                     // Disable only if it's the first row AND it's the only row
                     if (btn) btn.disabled = (index === 0 && disableFirst);
                 });
             }

            // Setup listeners for any existing rows on page load (e.g., from old() input)
            itemsContainer.querySelectorAll('.item-row').forEach(row => {
                setupItemListeners(row);
            });

            // Initial calculation and button state check
            calculateTotals();
            enableAllRemoveButtons(); // Checks initial state

            // Default due date logic
            const issueDateInput = document.getElementById('issue_date');
            const dueDateInput = document.getElementById('due_date');
            if(issueDateInput && dueDateInput) {
                 issueDateInput.addEventListener('change', function() {
                    try{
                        const issueDate = new Date(this.value + 'T00:00:00'); // Ensure time doesn't cause off-by-one
                        if(!isNaN(issueDate.getTime())){
                             const dueDate = new Date(issueDate);
                             dueDate.setDate(dueDate.getDate() + 30); // Add 30 days
                             dueDateInput.value = dueDate.toISOString().split('T')[0]; // Format as YYYY-MM-DD
                        }
                    } catch(e){ console.error("Error setting due date:", e); }
                });
            }

        });
    </script>
    @endpush
</x-app-layout>