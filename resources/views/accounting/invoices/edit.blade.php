<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Invoice') }} #{{ $invoice->invoice_number }}
            </h2>
            <div>
                <a href="{{ route('accounting.invoices.show', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    View Invoice
                </a>
                <a href="{{ route('accounting.invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to Invoices
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('accounting.invoices.update', $invoice) }}">
                        @csrf
                        @method('PUT')

                        <!-- Invoice Details Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Contact -->
                                <div>
                                    <label for="contact_id" class="block text-sm font-medium text-gray-700">Contact</label>
                                    <select id="contact_id" name="contact_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required {{ $invoice->status != 'draft' ? 'disabled' : '' }}>
                                        <option value="">Select Contact</option>
                                        @foreach($contacts as $contact)
                                            <option value="{{ $contact->id }}" {{ old('contact_id', $invoice->contact_id) == $contact->id ? 'selected' : '' }}>
                                                {{ $contact->name }} ({{ $contact->type }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($invoice->status != 'draft')
                                        <input type="hidden" name="contact_id" value="{{ $invoice->contact_id }}">
                                    @endif
                                    @error('contact_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Invoice Number -->
                                <div>
                                    <label for="invoice_number" class="block text-sm font-medium text-gray-700">Invoice Number</label>
                                    <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required {{ $invoice->status != 'draft' ? 'readonly' : '' }}>
                                    @error('invoice_number')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Reference -->
                                <div>
                                    <label for="reference" class="block text-sm font-medium text-gray-700">Reference (Optional)</label>
                                    <input type="text" name="reference" id="reference" value="{{ old('reference', $invoice->reference) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" {{ $invoice->status != 'draft' ? 'readonly' : '' }}>
                                </div>

                                <!-- Issue Date -->
                                <div>
                                    <label for="issue_date" class="block text-sm font-medium text-gray-700">Issue Date</label>
                                    <input type="date" name="issue_date" id="issue_date" value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required {{ $invoice->status != 'draft' ? 'readonly' : '' }}>
                                    @error('issue_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Due Date -->
                                <div>
                                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                    @error('due_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select id="status" name="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required {{ $invoice->status != 'draft' ? 'disabled' : '' }}>
                                        <option value="draft" {{ old('status', $invoice->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="sent" {{ old('status', $invoice->status) == 'sent' ? 'selected' : '' }}>Sent</option>
                                    </select>
                                    @if($invoice->status != 'draft')
                                        <input type="hidden" name="status" value="{{ $invoice->status }}">
                                    @endif
                                    @error('status')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Items Section -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Invoice Items</h3>
                                @if($invoice->status == 'draft')
                                <button type="button" id="add-item" class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Add Item
                                </button>
                                @endif
                            </div>

                            <div class="bg-white overflow-hidden border border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200" id="items-table">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax Rate</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            @if($invoice->status == 'draft')
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="items-container">
                                        @foreach($invoice->items as $index => $item)
                                            <tr class="item-row">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="text" name="items[{{ $index }}][description]" value="{{ old('items.'.$index.'.description', $item->description) }}" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required {{ $invoice->status != 'draft' ? 'readonly' : '' }}>
                                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="number" name="items[{{ $index }}][quantity]" value="{{ old('items.'.$index.'.quantity', $item->quantity) }}" class="item-quantity focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" min="1" step="1" required {{ $invoice->status != 'draft' ? 'readonly' : '' }}>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="number" name="items[{{ $index }}][unit_price]" value="{{ old('items.'.$index.'.unit_price', $item->unit_price) }}" class="item-price focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" min="0" step="0.01" required {{ $invoice->status != 'draft' ? 'readonly' : '' }}>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <select name="items[{{ $index }}][tax_rate_id]" class="item-tax block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" {{ $invoice->status != 'draft' ? 'disabled' : '' }}>
                                                        <option value="">No Tax</option>
                                                        @foreach($taxRates as $tax)
                                                            <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}" {{ old('items.'.$index.'.tax_rate_id', $item->tax_rate_id) == $tax->id ? 'selected' : '' }}>
                                                                {{ $tax->name }} ({{ $tax->rate }}%)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @if($invoice->status != 'draft')
                                                        <input type="hidden" name="items[{{ $index }}][tax_rate_id]" value="{{ $item->tax_rate_id }}">
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <select name="items[{{ $index }}][account_id]" class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required {{ $invoice->status != 'draft' ? 'disabled' : '' }}>
                                                        @foreach($accounts as $account)
                                                            <option value="{{ $account->id }}" {{ old('items.'.$index.'.account_id', $item->account_id) == $account->id ? 'selected' : '' }}>
                                                                {{ $account->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @if($invoice->status != 'draft')
                                                        <input type="hidden" name="items[{{ $index }}][account_id]" value="{{ $item->account_id }}">
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="item-total">{{ number_format($item->subtotal, 2) }}</span>
                                                </td>
                                                @if($invoice->status == 'draft')
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <button type="button" class="text-red-600 hover:text-red-900 remove-item" {{ count($invoice->items) <= 1 ? 'disabled' : '' }}>Remove</button>
                                                </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Summary Section -->
                        <div class="mb-8">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-end">
                                    <div class="w-64">
                                        <div class="flex justify-between py-2 border-b border-gray-200">
                                            <span class="text-gray-700">Subtotal:</span>
                                            <span class="font-medium" id="subtotal">{{ number_format($invoice->subtotal, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-b border-gray-200">
                                            <span class="text-gray-700">Tax:</span>
                                            <span class="font-medium" id="tax-total">{{ number_format($invoice->tax_amount, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between py-2">
                                            <span class="text-gray-700 font-bold">Total:</span>
                                            <span class="font-bold" id="grand-total">{{ number_format($invoice->total, 2) }}</span>
                                            <input type="hidden" name="subtotal" id="subtotal-input" value="{{ $invoice->subtotal }}">
                                            <input type="hidden" name="tax_amount" id="tax-input" value="{{ $invoice->tax_amount }}">
                                            <input type="hidden" name="total" id="total-input" value="{{ $invoice->total }}">
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
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                                    <textarea id="notes" name="notes" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes', $invoice->notes) }}</textarea>
                                </div>

                                <!-- Terms & Conditions -->
                                <div>
                                    <label for="terms" class="block text-sm font-medium text-gray-700">Terms & Conditions (Optional)</label>
                                    <textarea id="terms" name="terms" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('terms', $invoice->terms) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Update Invoice
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
            let itemCount = {{ count($invoice->items) - 1 }};
            const addItemButton = document.getElementById('add-item');
            const itemsContainer = document.getElementById('items-container');
            
            // Add new item row
            if (addItemButton) {
                addItemButton.addEventListener('click', function() {
                    itemCount++;
                    const newRow = document.createElement('tr');
                    newRow.className = 'item-row';
                    newRow.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" name="items[${itemCount}][description]" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" name="items[${itemCount}][quantity]" class="item-quantity focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="1" min="1" step="1" required>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" name="items[${itemCount}][unit_price]" class="item-price focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="0.00" min="0" step="0.01" required>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select name="items[${itemCount}][tax_rate_id]" class="item-tax block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <option value="">No Tax</option>
                                ${Array.from(document.querySelectorAll('select[name^="items"][name$="[tax_rate_id]"] option')).filter((v, i, a) => i === a.findIndex(t => t.value === v.value)).map(opt => 
                                    `<option value="${opt.value}" data-rate="${opt.dataset.rate || 0}">${opt.textContent}</option>`
                                ).join('')}
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select name="items[${itemCount}][account_id]" class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                ${Array.from(document.querySelectorAll('select[name^="items"][name$="[account_id]"] option')).filter((v, i, a) => i === a.findIndex(t => t.value === v.value)).map(opt => 
                                    `<option value="${opt.value}">${opt.textContent}</option>`
                                ).join('')}
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="item-total">0.00</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-item">Remove</button>
                        </td>
                    `;
                    
                    itemsContainer.appendChild(newRow);
                    setupItemListeners(newRow);
                    enableAllRemoveButtons();
                });
            }
            
            // Setup initial rows
            document.querySelectorAll('.item-row').forEach(row => {
                setupItemListeners(row);
            });
            
            // Handle remove item
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('remove-item') && !e.target.disabled) {
                    const row = e.target.closest('tr');
                    if (document.querySelectorAll('.item-row').length > 1) {
                        row.remove();
                        calculateTotals();
                        
                        // If there's only one row left, disable its remove button
                        if (document.querySelectorAll('.item-row').length === 1) {
                            const removeBtn = document.querySelector('.remove-item');
                            if (removeBtn) {
                                removeBtn.disabled = true;
                            }
                        }
                    }
                }
            });
            
            // Setup item listeners for calculation
            function setupItemListeners(row) {
                const quantityInput = row.querySelector('.item-quantity');
                const priceInput = row.querySelector('.item-price');
                const taxSelect = row.querySelector('.item-tax');
                
                if (quantityInput && priceInput && taxSelect) {
                    [quantityInput, priceInput, taxSelect].forEach(el => {
                        if (!el.readOnly && !el.disabled) {
                            el.addEventListener('change', calculateTotals);
                            el.addEventListener('keyup', calculateTotals);
                        }
                    });
                }
            }
            
            // Enable all remove buttons when we have multiple rows
            function enableAllRemoveButtons() {
                if (document.querySelectorAll('.item-row').length > 1) {
                    document.querySelectorAll('.remove-item').forEach(button => {
                        if (button) {
                            button.disabled = false;
                        }
                    });
                }
            }
            
            // Calculate all totals
            function calculateTotals() {
                let subtotal = 0;
                let taxTotal = 0;
                
                document.querySelectorAll('.item-row').forEach(row => {
                    const quantityEl = row.querySelector('.item-quantity');
                    const priceEl = row.querySelector('.item-price');
                    const taxSelect = row.querySelector('.item-tax');
                    const totalEl = row.querySelector('.item-total');
                    
                    if (quantityEl && priceEl && taxSelect && totalEl) {
                        const quantity = parseFloat(quantityEl.value) || 0;
                        const price = parseFloat(priceEl.value) || 0;
                        
                        let taxRate = 0;
                        if (taxSelect.value) {
                            const selectedOption = taxSelect.options[taxSelect.selectedIndex];
                            if (selectedOption && selectedOption.dataset.rate) {
                                taxRate = parseFloat(selectedOption.dataset.rate) || 0;
                            }
                        }
                        
                        const lineTotal = quantity * price;
                        const lineTax = lineTotal * (taxRate / 100);
                        
                        subtotal += lineTotal;
                        taxTotal += lineTax;
                        
                        totalEl.textContent = lineTotal.toFixed(2);
                    }
                });
                
                const grandTotal = subtotal + taxTotal;
                
                const subtotalEl = document.getElementById('subtotal');
                const taxTotalEl = document.getElementById('tax-total');
                const grandTotalEl = document.getElementById('grand-total');
                
                if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
                if (taxTotalEl) taxTotalEl.textContent = taxTotal.toFixed(2);
                if (grandTotalEl) grandTotalEl.textContent = grandTotal.toFixed(2);
                
                const subtotalInput = document.getElementById('subtotal-input');
                const taxInput = document.getElementById('tax-input');
                const totalInput = document.getElementById('total-input');
                
                if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
                if (taxInput) taxInput.value = taxTotal.toFixed(2);
                if (totalInput) totalInput.value = grandTotal.toFixed(2);
            }
            
            // Set default due date (30 days from issue date)
            const issueDateInput = document.getElementById('issue_date');
            const dueDateInput = document.getElementById('due_date');
            
            if (issueDateInput && dueDateInput && !issueDateInput.readOnly) {
                issueDateInput.addEventListener('change', function() {
                    const issueDate = new Date(this.value);
                    const dueDate = new Date(issueDate);
                    dueDate.setDate(dueDate.getDate() + 30);
                    
                    dueDateInput.value = dueDate.toISOString().split('T')[0];
                });
            }
            
            // Initialize calculations
            if ('{{ $invoice->status }}' === 'draft') {
                calculateTotals();
            }
        });
    </script>
    @endpush
</x-app-layout>