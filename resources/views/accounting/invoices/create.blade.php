{{-- resources/views/accounting/invoices/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Invoice') }}
            </h2>
            <a href="{{ route('accounting.invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                {{ __('Back to Invoices') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Added ID for easier debugging if needed --}}
                <div id="invoice-form-container" class="p-6 bg-white border-b border-gray-200"
                     x-data="invoiceForm({
                        initialItems: {{ json_encode(old('items', [])) }},
                        taxRates: {{ json_encode($taxRates) }},
                        incomeAccounts: {{ json_encode($incomeAccounts) }},
                        feeItems: {{ json_encode($feeItems) }},
                        inventoryItems: {{ json_encode($inventoryItems) }},
                        initialInvoiceType: '{{ old('invoice_type', 'inventory') }}'
                     })"
                     x-init="init()">

                    {{-- Display General Validation Errors --}}
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
                    {{-- Other Session Errors --}}
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('accounting.invoices.store') }}" @submit.prevent="submitForm($el)">
                        @csrf

                        <!-- Invoice Details Section -->
                        <div class="mb-6 border-b pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- Contact --}}
                                <div>
                                    <label for="contact_id" class="block text-sm font-medium text-gray-700">{{ __('Contact *') }}</label>
                                    <select id="contact_id" name="contact_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                        <option value="">-- Select Contact --</option>
                                        @foreach($contacts as $contact)
                                            <option value="{{ $contact->id }}" @selected(old('contact_id') == $contact->id)>
                                                {{ $contact->name }} {{ $contact->contact_type ? '(' . ucfirst($contact->contact_type) . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('contact_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Invoice Number --}}
                                <div>
                                    <label for="invoice_number" class="block text-sm font-medium text-gray-700">{{ __('Invoice Number *') }}</label>
                                    <input type="text" name="invoice_number" id="invoice_number" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" value="{{ old('invoice_number', $nextInvoiceNumber) }}" required />
                                     @error('invoice_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Reference --}}
                                <div>
                                    <label for="reference" class="block text-sm font-medium text-gray-700">{{ __('Reference') }}</label>
                                    <input type="text" name="reference" id="reference" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" value="{{ old('reference') }}" />
                                     @error('reference') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Issue Date --}}
                                <div>
                                    <label for="issue_date" class="block text-sm font-medium text-gray-700">{{ __('Issue Date *') }}</label>
                                    <input type="date" name="issue_date" id="issue_date" x-model="issueDate" @change="updateDueDate()" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required />
                                     @error('issue_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Due Date --}}
                                <div>
                                     <label for="due_date" class="block text-sm font-medium text-gray-700">{{ __('Due Date *') }}</label>
                                     <input type="date" name="due_date" id="due_date" x-model="dueDate" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required />
                                     @error('due_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Invoice Type --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Invoice Type *') }}</label>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="invoice_type" value="fees" x-model="invoiceType" @change="handleInvoiceTypeChange()">
                                            <span class="ml-2 text-sm">Fees Invoice</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="invoice_type" value="inventory" x-model="invoiceType" @change="handleInvoiceTypeChange()">
                                            <span class="ml-2 text-sm">Inventory/Service Invoice</span>
                                        </label>
                                    </div>
                                     @error('invoice_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Invoice Items Section --}}
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Invoice Items</h3>
                                <button type="button" @click.prevent="addItem()" class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Add Item
                                </button>
                            </div>

                            <div class="overflow-x-auto border border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[20%]">Item</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[25%]">Description</th>
                                            <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-[8%]">Qty</th>
                                            <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">Unit Price</th>
                                            <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-[8%]">Discount</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">Tax</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[15%]">Account *</th>
                                            <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">Amount</th>
                                            <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        {{-- Alpine template loop --}}
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr :class="{'bg-red-50 border-l-4 border-red-400': hasItemError(index)}">
                                                {{-- Hidden inputs --}}
                                                <input type="hidden" :name="`items[${index}][id]`" x-model="item.id">
                                                <input type="hidden" :name="`items[${index}][item_source_id]`" x-model="item.item_source_id">
                                                <input type="hidden" :name="`items[${index}][name]`" x-model="item.name"> {{-- Hidden field for name --}}

                                                {{-- Item Selection --}}
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <select x-model="item.item_source_id"
                                                            @change="populateItemDetails(index)"
                                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                            :disabled="!invoiceType">
                                                        <option value="">-- Type or Select --</option>
                                                        <template x-if="invoiceType === 'fees'">
                                                            <template x-for="feeItem in availableFeeItems" :key="feeItem.id">
                                                                <option :value="feeItem.id" x-text="feeItem.name"></option>
                                                            </template>
                                                        </template>
                                                        <template x-if="invoiceType === 'inventory'">
                                                            <template x-for="invItem in availableInventoryItems" :key="invItem.id">
                                                                <option :value="invItem.id" x-text="invItem.name + (invItem.item_code ? ' ('+invItem.item_code+')' : '')"></option>
                                                            </template>
                                                        </template>
                                                    </select>
                                                </td>

                                                {{-- Description --}}
                                                <td class="px-3 py-2">
                                                    <textarea :name="`items[${index}][description]`" x-model="item.description" @input="updateItemTotals(index)" rows="1" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required></textarea>
                                                </td>

                                                {{-- Quantity --}}
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" @input.debounce.500ms="updateItemTotals(index)" class="mt-1 block w-full text-right border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" min="0.0001" step="any" required />
                                                </td>

                                                {{-- Unit Price --}}
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                     <input type="number" :name="`items[${index}][unit_price]`" x-model.number="item.price" @input.debounce.500ms="updateItemTotals(index)" class="mt-1 block w-full text-right border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" min="0" step="0.01" required />
                                                </td>

                                                {{-- Discount --}}
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" :name="`items[${index}][discount]`" x-model.number="item.discount" @input.debounce.500ms="updateItemTotals(index)" class="mt-1 block w-full text-right border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" min="0" step="0.01" />
                                                </td>

                                                {{-- Tax Rate --}}
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <select :name="`items[${index}][tax_rate_id]`" x-model="item.tax_rate_id" @change="updateItemTotals(index)" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                        <option value="">No Tax</option>
                                                        <template x-for="tax in availableTaxRates" :key="tax.id">
                                                            <option :value="tax.id" x-text="tax.name + ' (' + parseFloat(tax.rate || 0).toFixed(2) + '%)'"></option>
                                                        </template>
                                                    </select>
                                                </td>

                                                {{-- Account --}}
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <select :name="`items[${index}][account_id]`" x-model="item.account_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                                         <option value="">-- Select Account --</option>
                                                        <template x-for="acc in availableIncomeAccounts" :key="acc.id">
                                                             <option :value="acc.id" x-text="acc.name + (acc.account_code ? ' ('+acc.account_code+')' : '')"></option>
                                                         </template>
                                                    </select>
                                                </td>

                                                {{-- Line Amount (Display Only) --}}
                                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                                    <span class="text-sm text-gray-900 font-medium" x-text="formatCurrency(item.display_total)"></span>
                                                </td>

                                                {{-- Action --}}
                                                <td class="px-3 py-2 whitespace-nowrap text-center">
                                                    <button type="button" @click.prevent="removeItem(index)" class="text-red-600 hover:text-red-800 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="items.length <= 1">
                                                        {{-- Remove Icon SVG --}}
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            @error('items') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror {{-- General error for items array --}}
                        </div>

                         {{-- Summary Section --}}
                        <div class="mb-6">
                             <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                 <div class="flex justify-end">
                                     <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 space-y-1">
                                         <div class="flex justify-between">
                                             <span class="text-sm text-gray-600">Subtotal (Pre-discount):</span>
                                             <span class="text-sm font-medium text-gray-900" x-text="formatCurrency(summary.subtotal)"></span>
                                         </div>
                                         <div class="flex justify-between">
                                             <span class="text-sm text-gray-600">Total Discount:</span>
                                             <span class="text-sm font-medium text-gray-900" x-text="formatCurrency(summary.totalDiscount)"></span>
                                         </div>
                                          <div class="flex justify-between border-t pt-1">
                                             <span class="text-sm text-gray-600">Subtotal (After Discount):</span>
                                             <span class="text-sm font-medium text-gray-900" x-text="formatCurrency(summary.subtotal - summary.totalDiscount)"></span>
                                         </div>
                                         <div class="flex justify-between">
                                             <span class="text-sm text-gray-600">Total Tax:</span>
                                             <span class="text-sm font-medium text-gray-900" x-text="formatCurrency(summary.totalTax)"></span>
                                         </div>
                                         <div class="flex justify-between border-t mt-1 pt-2">
                                             <span class="text-base font-bold text-gray-800">Total:</span>
                                             <span class="text-base font-bold text-gray-800" x-text="formatCurrency(summary.grandTotal)"></span>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>

                        {{-- Additional Details --}}
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Notes --}}
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
                                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('notes') }}</textarea>
                                    @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                {{-- Terms & Conditions --}}
                                <div>
                                     <label for="terms" class="block text-sm font-medium text-gray-700">{{ __('Terms & Conditions') }}</label>
                                    <textarea id="terms" name="terms" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('terms') }}</textarea>
                                     @error('terms') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Form Buttons --}}
                        <div class="flex justify-end mt-6">
                             <a href="{{ route('accounting.invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4 pt-2">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Save Draft Invoice') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine.js Logic --}}
    @push('scripts')
    <script>
        // Add console log right before the function definition
        console.log('Defining invoiceForm function...');

        function invoiceForm(config) {
             // Add console log right inside the function definition
             console.log('invoiceForm function called with config:', config);
            return {
                // --- State ---
                invoiceType: config.initialInvoiceType,
                items: [],
                issueDate: '{{ old('issue_date', now()->format('Y-m-d')) }}',
                dueDate: '{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}',
                summary: {
                    subtotal: 0, totalDiscount: 0, totalTax: 0, grandTotal: 0,
                },
                // --- Data Sources ---
                availableTaxRates: config.taxRates || [],
                availableIncomeAccounts: config.incomeAccounts || [],
                availableFeeItems: config.feeItems || [],
                availableInventoryItems: config.inventoryItems || [],
                // --- Validation ---
                itemErrors: config.itemErrors || @json(session()->has('errors') ? session('errors')->getBag('default')->messages() : new stdClass),

                // --- Methods ---
                init() {
                    console.log('[INIT] Alpine component initializing...'); // Log initialization start
                    try { // Wrap init logic in try...catch for better error isolation
                        // Map initial items
                        this.items = (config.initialItems || []).map((item, index) => {
                            const unitPrice = item?.unit_price ?? 0.00;
                            const quantity = item?.quantity ?? 1;
                            const discount = item?.discount ?? 0.00;
                            const taxRateId = item?.tax_rate_id ?? '';
                            const accountId = item?.account_id ?? '';

                            return {
                                id: item?.id || null,
                                item_source_id: item?.item_source_id || '',
                                name: item?.name || '',
                                description: item?.description || '',
                                quantity: parseFloat(quantity) || 1,
                                price: parseFloat(unitPrice) || 0.00,
                                discount: parseFloat(discount) || 0.00,
                                tax_rate_id: taxRateId,
                                account_id: accountId,
                                display_subtotal: 0, display_tax: 0, display_total: 0,
                                has_error: this.checkItemError(index)
                            };
                        });
                         console.log('[INIT] Mapped Items:', this.items.length);

                        if (this.items.length === 0) {
                            console.log('[INIT] No initial items, adding one blank row.');
                            // Directly call addItem logic instead of separate function call during init
                             this.items.push({
                                id: null, item_source_id: '', name: '', description: '', quantity: 1,
                                price: 0.00, discount: 0.00, tax_rate_id: '', account_id: '',
                                display_subtotal: 0, display_tax: 0, display_total: 0, has_error: false
                            });
                             console.log('[INIT] Blank row added. Items count:', this.items.length);
                        }

                        // Initial calculations only if items exist
                        if (this.items.length > 0) {
                             this.items.forEach((_, index) => this.updateItemTotals(index, true));
                             this.calculateSummaryTotals();
                        }
                         console.log('[INIT] Initial Summary:', JSON.parse(JSON.stringify(this.summary)));

                    } catch (error) {
                         console.error("[INIT] Error during initialization:", error);
                    }
                     console.log('[INIT] Initialization complete.');
                },

                addItem() {
                    console.log('[addItem] Add Item button clicked.'); // Log button click
                    try {
                        this.items.push({
                            id: null, item_source_id: '', name: '', description: '', quantity: 1,
                            price: 0.00, discount: 0.00, tax_rate_id: '', account_id: '',
                            display_subtotal: 0, display_tax: 0, display_total: 0, has_error: false
                        });
                        console.log('[addItem] Row added. Items count:', this.items.length);
                        this.$nextTick(() => {
                            this.calculateSummaryTotals();
                            console.log('[addItem] Summary recalculated.');
                        });
                    } catch (error) {
                         console.error("[addItem] Error adding item:", error);
                    }
                },

                removeItem(index) {
                    console.log(`[removeItem] Removing item at index: ${index}`);
                    if (this.items.length <= 1) {
                        console.warn('[removeItem] Cannot remove the last item.');
                        return;
                    }
                    try {
                        this.items.splice(index, 1);
                        console.log('[removeItem] Items after remove:', this.items.length);
                        this.calculateSummaryTotals();
                        console.log('[removeItem] Summary recalculated.');
                    } catch (error) {
                         console.error(`[removeItem] Error removing item at index ${index}:`, error);
                    }
                },

                handleInvoiceTypeChange() {
                     console.log('[handleInvoiceTypeChange] Invoice type changed to:', this.invoiceType);
                    try {
                        this.items.forEach(item => {
                            item.item_source_id = ''; item.name = ''; item.description = '';
                            item.price = 0.00; item.account_id = '';
                        });
                        this.items.forEach((_, index) => this.updateItemTotals(index, true));
                        this.calculateSummaryTotals();
                         console.log('[handleInvoiceTypeChange] Items reset.');
                    } catch (error) {
                         console.error("[handleInvoiceTypeChange] Error resetting items:", error);
                    }
                 },

                 populateItemDetails(index) {
                     if (!this.items || !this.items[index]) return; // Safety check
                     const selectedSourceId = this.items[index].item_source_id;
                     console.log(`[populateItemDetails index ${index}] Selected source ID:`, selectedSourceId);

                    try {
                        if (!selectedSourceId) {
                            this.items[index].name = ''; this.items[index].description = '';
                            this.items[index].price = 0.00; this.items[index].account_id = '';
                        } else {
                            let sourceItem = null;
                            if (this.invoiceType === 'fees') {
                                sourceItem = this.availableFeeItems.find(f => f.id == selectedSourceId);
                            } else if (this.invoiceType === 'inventory') {
                                sourceItem = this.availableInventoryItems.find(i => i.id == selectedSourceId);
                            }

                            if (sourceItem) {
                                this.items[index].name = sourceItem.name || '';
                                this.items[index].description = sourceItem.description || '';
                                this.items[index].price = parseFloat(sourceItem.amount || sourceItem.unit_cost || 0.00);
                                this.items[index].account_id = sourceItem.income_account_id || sourceItem.default_income_account_id || '';
                            } else {
                                console.warn(`[populateItemDetails index ${index}] Source item ID ${selectedSourceId} not found.`);
                                this.items[index].name = ''; this.items[index].description = '';
                                this.items[index].price = 0.00; this.items[index].account_id = '';
                            }
                        }
                        this.updateItemTotals(index); // Recalculate totals after populating
                    } catch (error) {
                         console.error(`[populateItemDetails index ${index}] Error populating details:`, error);
                    }
                 },

                updateItemTotals(index, suppressSummaryRecalc = false) {
                    if (!this.items || !this.items[index]) return;
                    const item = this.items[index];
                    try {
                        const quantity = Number(item.quantity) || 0;
                        const price = Number(item.price) || 0;
                        const discount = Number(item.discount) || 0;

                        const subtotalBeforeDiscount = quantity * price;
                        item.display_subtotal = Math.max(0, subtotalBeforeDiscount - discount);

                        let taxAmount = 0;
                        if (item.tax_rate_id) {
                            const taxRate = this.availableTaxRates.find(tr => tr.id == item.tax_rate_id);
                            if (taxRate) {
                                taxAmount = (item.display_subtotal * (Number(taxRate.rate) || 0)) / 100;
                            }
                        }
                        item.display_tax = taxAmount;
                        item.display_total = item.display_subtotal + item.display_tax;

                        if (!suppressSummaryRecalc) {
                            this.calculateSummaryTotals();
                        }
                    } catch (error) {
                         console.error(`[updateItemTotals index ${index}] Error calculating totals:`, error);
                    }
                },

                calculateSummaryTotals() {
                     // console.log('[calculateSummaryTotals] Recalculating summary...'); // Reduce noise?
                     try {
                        let runningSubtotal = 0, runningTotalDiscount = 0, runningTotalTax = 0;
                        this.items.forEach(item => {
                            const quantity = Number(item.quantity) || 0;
                            const price = Number(item.price) || 0;
                            const discount = Number(item.discount) || 0;
                            const displayTax = Number(item.display_tax) || 0;

                            runningSubtotal += quantity * price;
                            runningTotalDiscount += discount;
                            runningTotalTax += displayTax;
                        });
                        const calculatedGrandTotal = (runningSubtotal - runningTotalDiscount) + runningTotalTax;
                        this.summary.subtotal = runningSubtotal;
                        this.summary.totalDiscount = runningTotalDiscount;
                        this.summary.totalTax = runningTotalTax;
                        this.summary.grandTotal = calculatedGrandTotal;
                        // console.log('[calculateSummaryTotals] Summary:', this.summary); // Reduce noise?
                     } catch (error) {
                          console.error("[calculateSummaryTotals] Error calculating summary:", error);
                     }
                 },

                 formatCurrency(amount) {
                     if (amount === null || typeof amount === 'undefined' || isNaN(Number(amount))) return '0.00';
                     return Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                 },

                 updateDueDate() {
                     try {
                         const issue = new Date(this.issueDate + 'T00:00:00');
                         if (!isNaN(issue.getTime())) {
                             const due = new Date(issue);
                             due.setDate(due.getDate() + 30);
                             this.dueDate = due.toISOString().split('T')[0];
                             // console.log('[updateDueDate] Due date set to:', this.dueDate); // Reduce noise?
                         }
                     } catch (e) { console.error("Error setting due date:", e); }
                 },

                 checkItemError(index) {
                    const prefix = `items.${index}.`;
                    if (typeof this.itemErrors !== 'object' || this.itemErrors === null) return false;
                    for (const key in this.itemErrors) {
                        if (Object.prototype.hasOwnProperty.call(this.itemErrors, key) && key.startsWith(prefix)) {
                             return true;
                        }
                    }
                    return false;
                },
                hasItemError(index) {
                    return this.items && this.items[index] ? this.items[index].has_error : false;
                },

                submitForm(formElement) {
                    console.log('Preparing form submission...');
                    try {
                        this.calculateSummaryTotals();
                        formElement.querySelectorAll('input[data-summary-field="true"]').forEach(el => el.remove());
                        const nameMap = { subtotal: 'subtotal', totalDiscount: 'discount_amount', totalTax: 'tax_amount', grandTotal: 'total' };
                        Object.entries(this.summary).forEach(([key, value]) => {
                            if (nameMap[key]) {
                                const input = document.createElement('input');
                                input.type = 'hidden'; input.name = nameMap[key];
                                input.value = (Number(value) || 0).toFixed(2);
                                input.setAttribute('data-summary-field', 'true');
                                formElement.appendChild(input);
                            }
                        });
                        console.log('Submitting form now.');
                        formElement.submit();
                    } catch (error) {
                         console.error("[submitForm] Error preparing/submitting form:", error);
                    }
                 }
            }
        }
        // Add log to confirm script block is reached
        console.log('Alpine script block executed.');
    </script>
    @endpush
</x-app-layout>