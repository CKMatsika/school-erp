<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Purchase Order') }}: {{ $purchaseOrder->po_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.purchase-orders.update', $purchaseOrder) }}" id="purchase-order-form">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="supplier_id" :value="__('Supplier')" />
                                    <select id="supplier_id" name="supplier_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="date_issued" :value="__('Date Issued')" />
                                    <x-text-input id="date_issued" class="block mt-1 w-full" type="date" name="date_issued" :value="old('date_issued', $purchaseOrder->date_issued->format('Y-m-d'))" required />
                                    <x-input-error :messages="$errors->get('date_issued')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="date_expected" :value="__('Expected Delivery Date')" />
                                    <x-text-input id="date_expected" class="block mt-1 w-full" type="date" name="date_expected" :value="old('date_expected', $purchaseOrder->date_expected ? $purchaseOrder->date_expected->format('Y-m-d') : '')" />
                                    <x-input-error :messages="$errors->get('date_expected')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="payment_terms" :value="__('Payment Terms')" />
                                    <x-text-input id="payment_terms" class="block mt-1 w-full" type="text" name="payment_terms" :value="old('payment_terms', $purchaseOrder->payment_terms)" placeholder="e.g. Net 30" />
                                    <x-input-error :messages="$errors->get('payment_terms')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="shipping_method" :value="__('Shipping Method')" />
                                    <x-text-input id="shipping_method" class="block mt-1 w-full" type="text" name="shipping_method" :value="old('shipping_method', $purchaseOrder->shipping_method)" />
                                    <x-input-error :messages="$errors->get('shipping_method')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="requisition_id" :value="__('Related Requisition')" />
                                    <select id="requisition_id" name="requisition_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ $purchaseOrder->status != 'draft' ? 'disabled' : '' }}>
                                        <option value="">None</option>
                                        @foreach($requisitions as $requisition)
                                            <option value="{{ $requisition->id }}" {{ old('requisition_id', $purchaseOrder->requisition_id) == $requisition->id ? 'selected' : '' }}>
                                                {{ $requisition->requisition_number }} - {{ $requisition->department }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($purchaseOrder->status != 'draft')
                                        <input type="hidden" name="requisition_id" value="{{ $purchaseOrder->requisition_id }}">
                                        <p class="text-xs text-gray-500 mt-1">Requisition cannot be changed after the order is submitted.</p>
                                    @endif
                                    <x-input-error :messages="$errors->get('requisition_id')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Additional Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="shipping_address" :value="__('Shipping Address')" />
                                    <textarea id="shipping_address" name="shipping_address" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('shipping_address', $purchaseOrder->shipping_address) }}</textarea>
                                    <x-input-error :messages="$errors->get('shipping_address')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="notes" :value="__('Notes')" />
                                    <textarea id="notes" name="notes" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
                                @if($purchaseOrder->status == 'draft')
                                    <button type="button" id="add-item-btn" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Add Item
                                    </button>
                                @endif
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div id="items-container">
                                    @if(old('items'))
                                        @foreach(old('items') as $index => $item)
                                            <div class="item-row bg-white p-4 rounded-md shadow-sm mb-4">
                                                <div class="flex justify-between items-center mb-2">
                                                    <h4 class="font-medium">Item #<span class="item-number">{{ $index + 1 }}</span></h4>
                                                    @if($purchaseOrder->status == 'draft')
                                                        <button type="button" class="remove-item text-red-600 hover:text-red-900">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                                    <div class="md:col-span-2">
                                                        <label for="items[{{ $index }}][inventory_item_id]" class="block text-sm font-medium text-gray-700">Item</label>
                                                        <select name="items[{{ $index }}][inventory_item_id]" class="item-select mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required {{ $purchaseOrder->status != 'draft' ? 'disabled' : '' }}>
                                                            <option value="">Select Item</option>
                                                            @foreach($inventoryItems as $inventoryItem)
                                                                <option value="{{ $inventoryItem->id }}" {{ $item['inventory_item_id'] == $inventoryItem->id ? 'selected' : '' }}>
                                                                    {{ $inventoryItem->item_code }} - {{ $inventoryItem->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if($purchaseOrder->status != 'draft')
                                                            <input type="hidden" name="items[{{ $index }}][inventory_item_id]" value="{{ $item['inventory_item_id'] }}">
                                                        @endif
                                                        @error("items.{$index}.inventory_item_id")
                                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div>
                                                        <label for="items[{{ $index }}][quantity]" class="block text-sm font-medium text-gray-700">Quantity</label>
                                                        <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" class="item-quantity mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" step="0.01" min="0.01" required {{ $purchaseOrder->status != 'draft' ? 'readonly' : '' }}>
                                                        @error("items.{$index}.quantity")
                                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div>
                                                        <label for="items[{{ $index }}][unit_price]" class="block text-sm font-medium text-gray-700">Unit Price</label>
                                                        <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}" class="item-price mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" step="0.01" min="0" required>
                                                        @error("items.{$index}.unit_price")
                                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div>
                                                        <label for="items[{{ $index }}][tax_rate]" class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                                                        <input type="number" name="items[{{ $index }}][tax_rate]" value="{{ $item['tax_rate'] ?? 0 }}" class="item-tax mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" step="0.01" min="0">
                                                        @error("items.{$index}.tax_rate")
                                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div>
                                                        <label for="items[{{ $index }}][subtotal]" class="block text-sm font-medium text-gray-700">Subtotal</label>
                                                        <input type="number" name="items[{{ $index }}][subtotal]" value="{{ $item['subtotal'] }}" class="item-subtotal mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-100" step="0.01" min="0" readonly>
                                                    </div>
                                                    <div class="md:col-span-6">
                                                        <label for="items[{{ $index }}][description]" class="block text-sm font-medium text-gray-700">Description</label>
                                                        <textarea name="items[{{ $index }}][description]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="2">{{ $item['description'] }}</textarea>
                                                        @error("items.{$index}.description")
                                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        @foreach($purchaseOrder->items as $index => $item)
                                            <div class="item-row bg-white p-4 rounded-md shadow-sm mb-4">
                                                <div class="flex justify-between items-center mb-2">
                                                    <h4 class="font-medium">Item #<span class="item-number">{{ $index + 1 }}</span></h4>
                                                    @if($purchaseOrder->status == 'draft')
                                                        <button type="button" class="remove-item text-red-600 hover:text-red-900">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                                    <div class="md:col-span-2">
                                                        <label for="items[{{ $index }}][inventory_item_id]" class="block text-sm font-medium text-gray-700">Item</label>
                                                        <select name="items[{{ $index }}][inventory_item_id]" class="item-select mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required {{ $purchaseOrder->status != 'draft' ? 'disabled' : '' }}>
                                                            <option value="">Select Item</option>
                                                            @foreach($inventoryItems as $inventoryItem)
                                                                <option value="{{ $inventoryItem->id }}" {{ $item->inventory_item_id == $inventoryItem->id ? 'selected' : '' }}>
                                                                    {{ $inventoryItem->item_code }} - {{ $inventoryItem->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if($purchaseOrder->status != 'draft')
                                                            <input type="hidden" name="items[{{ $index }}][inventory_item_id]" value="{{ $item->inventory_item_id }}">
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <label for="items[{{ $index }}][quantity]" class="block text-sm font-medium text-gray-700">Quantity</label>
                                                        <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" class="item-quantity mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" step="0.01" min="0.01" required {{ $purchaseOrder->status != 'draft' ? 'readonly' : '' }}>
                                                        @if($purchaseOrder->status != 'draft')
                                                            <p class="text-xs text-gray-500 mt-1">Quantity cannot be changed after order is submitted.</p>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <label for="items[{{ $index }}][unit_price]" class="block text-sm font-medium text-gray-700">Unit Price</label>
                                                        <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}" class="item-price mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" step="0.01" min="0" required>
                                                    </div>
                                                    <div>
                                                        <label for="items[{{ $index }}][tax_rate]" class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                                                        <input type="number" name="items[{{ $index }}][tax_rate]" value="{{ $item->tax_rate }}" class="item-tax mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" step="0.01" min="0">
                                                    </div>
                                                    <div>
                                                        <label for="items[{{ $index }}][subtotal]" class="block text-sm font-medium text-gray-700">Subtotal</label>
                                                        <input type="number" name="items[{{ $index }}][subtotal]" value="{{ $item->subtotal }}" class="item-subtotal mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-100" step="0.01" min="0" readonly>
                                                    </div>
                                                    <div class="md:col-span-6">
                                                        <label for="items[{{ $index }}][description]" class="block text-sm font-medium text-gray-700">Description</label>
                                                        <textarea name="items[{{ $index }}][description]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="2">{{ $item->description }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <div id="no-items-message" class="text-center py-8 {{ count($purchaseOrder->items) > 0 ? 'hidden' : '' }}">
                                    <p class="text-gray-500">No items added yet. Click "Add Item" to start.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Totals -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Order Totals</h3>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-1 md:col-start-2">
                                        <div class="space-y-3">
                                            <div class="flex justify-between">
                                                <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                                                <span class="text-sm font-medium text-gray-900" id="order-subtotal">{{ number_format($purchaseOrder->subtotal, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm font-medium text-gray-700">Tax:</span>
                                                <span class="text-sm font-medium text-gray-900" id="order-tax">{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between pt-3 border-t border-gray-200">
                                                <span class="text-base font-semibold text-gray-900">Total:</span>
                                                <span class="text-base font-semibold text-gray-900" id="order-total">{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                                                <input type="hidden" name="total_amount" id="total-amount-input" value="{{ $purchaseOrder->total_amount }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('accounting.purchase-orders.show', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button id="submit-btn">
                                {{ __('Update Purchase Order') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($purchaseOrder->status == 'draft')
        <!-- Item Template -->
        <template id="item-template">
            <div class="item-row bg-white p-4 rounded-md shadow-sm mb-4">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-medium">Item #<span class="item-number">__INDEX__</span></h4>
                    <button type="button" class="remove-item text-red-600 hover:text-red-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div class="md:col-span-2">
                        <label for="items[__INDEX__][inventory_item_id]" class="block text-sm font-medium text-gray-700">Item</label>
                        <select name="items[__INDEX__][inventory_item_id]" class="item-select mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Select Item</option>
                            @foreach($inventoryItems as $inventoryItem)
                                <option value="{{ $inventoryItem->id }}" data-price="{{ $inventoryItem->unit_cost }}" data-tax="{{ $inventoryItem->tax_rate }}">
                                    {{ $inventoryItem->item_code }} - {{ $inventoryItem->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="items[__INDEX__][quantity]" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="items[__INDEX__][quantity]" value="1" class="item-quantity mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" step="0.01" min="0.01" required>
                    </div>
                    <div>
                        <label for="items[__INDEX__][unit_price]" class="block text-sm font-medium text-gray-700">Unit Price</label>
                        <input type="number" name="items[__INDEX__][unit_price]" value="0" class="item-price mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" step="0.01" min="0" required>
                    </div>
                    <div>
                        <label for="items[__INDEX__][tax_rate]" class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                        <input type="number" name="items[__INDEX__][tax_rate]" value="0" class="item-tax mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" step="0.01" min="0">
                    </div>
                    <div>
                        <label for="items[__INDEX__][subtotal]" class="block text-sm font-medium text-gray-700">Subtotal</label>
                        <input type="number" name="items[__INDEX__][subtotal]" value="0" class="item-subtotal mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-100" step="0.01" min="0" readonly>
                    </div>
                    <div class="md:col-span-6">
                        <label for="items[__INDEX__][description]" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="items[__INDEX__][description]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </template>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemsContainer = document.getElementById('items-container');
            const noItemsMessage = document.getElementById('no-items-message');
            const addItemBtn = document.getElementById('add-item-btn');
            const itemTemplate = document.getElementById('item-template');
            const form = document.getElementById('purchase-order-form');
            const submitBtn = document.getElementById('submit-btn');
            
            // Helper function to format currency
            function formatCurrency(value) {
                return parseFloat(value).toFixed(2);
            }
            
            // Calculate item subtotal
            function calculateItemSubtotal(itemRow) {
                const quantity = parseFloat(itemRow.querySelector('.item-quantity').value) || 0;
                const price = parseFloat(itemRow.querySelector('.item-price').value) || 0;
                const subtotal = quantity * price;
                itemRow.querySelector('.item-subtotal').value = formatCurrency(subtotal);
                return subtotal;
            }
            
            // Calculate order totals
            function calculateOrderTotals() {
                const itemRows = itemsContainer.querySelectorAll('.item-row');
                let orderSubtotal = 0;
                let orderTax = 0;
                
                itemRows.forEach(row => {
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    const taxRate = parseFloat(row.querySelector('.item-tax').value) || 0;
                    
                    const itemSubtotal = quantity * price;
                    const itemTax = itemSubtotal * (taxRate / 100);
                    
                    orderSubtotal += itemSubtotal;
                    orderTax += itemTax;
                });
                
                const orderTotal = orderSubtotal + orderTax;
                
                document.getElementById('order-subtotal').textContent = formatCurrency(orderSubtotal);
                document.getElementById('order-tax').textContent = formatCurrency(orderTax);
                document.getElementById('order-total').textContent = formatCurrency(orderTotal);
                document.getElementById('total-amount-input').value = formatCurrency(orderTotal);
            }
            
            // Check if there are any items
            function checkItems() {
                if (itemsContainer.children.length === 0) {
                    noItemsMessage.classList.remove('hidden');
                } else {
                    noItemsMessage.classList.add('hidden');
                }
            }
            
            // Update item numbers
            function updateItemNumbers() {
                const items = itemsContainer.querySelectorAll('.item-row');
                items.forEach((item, index) => {
                    const numberSpan = item.querySelector('.item-number');
                    numberSpan.textContent = index + 1;
                    
                    // Update name attributes
                    const selects = item.querySelectorAll('select');
                    const inputs = item.querySelectorAll('input');
                    const textareas = item.querySelectorAll('textarea');
                    
                    selects.forEach(select => {
                        const name = select.getAttribute('name');
                        if (name && name.startsWith('items[')) {
                            select.setAttribute('name', name.replace(/items\[\d+\]/, `items[${index}]`));
                        }
                    });
                    
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name && name.startsWith('items[')) {
                            input.setAttribute('name', name.replace(/items\[\d+\]/, `items[${index}]`));
                        }
                    });
                    
                    textareas.forEach(textarea => {
                        const name = textarea.getAttribute('name');
                        if (name && name.startsWith('items[')) {
                            textarea.setAttribute('name', name.replace(/items\[\d+\]/, `items[${index}]`));
                        }
                    });
                });
            }
            
            @if($purchaseOrder->status == 'draft')
                // Add a new item
                function addItem() {
                    const index = itemsContainer.children.length;
                    const newItemHtml = itemTemplate.innerHTML.replace(/__INDEX__/g, index);
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newItemHtml;
                    const newItem = tempDiv.firstElementChild;
                    
                    itemsContainer.appendChild(newItem);
                    
                    // Set up event listeners for the new item
                    setupItemEventListeners(newItem);
                    
                    // Add event listener to the remove button
                    const removeButton = newItem.querySelector('.remove-item');
                    removeButton.addEventListener('click', function() {
                        itemsContainer.removeChild(newItem);
                        updateItemNumbers();
                        checkItems();
                        calculateOrderTotals();
                    });
                    
                    checkItems();
                    calculateOrderTotals();
                }
                
                // Add event listener to the Add Item button
                if (addItemBtn) {
                    addItemBtn.addEventListener('click', addItem);
                }
            @endif
            
            // Setup event listeners for item
            function setupItemEventListeners(itemRow) {
                const itemSelect = itemRow.querySelector('.item-select');
                const quantityInput = itemRow.querySelector('.item-quantity');
                const priceInput = itemRow.querySelector('.item-price');
                const taxInput = itemRow.querySelector('.item-tax');
                
                if (itemSelect && !itemSelect.disabled) {
                    // Item selection change
                    itemSelect.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        if (selectedOption.dataset && selectedOption.dataset.price) {
                            priceInput.value = selectedOption.dataset.price;
                        }
                        if (selectedOption.dataset && selectedOption.dataset.tax) {
                            taxInput.value = selectedOption.dataset.tax;
                        }
                        calculateItemSubtotal(itemRow);
                        calculateOrderTotals();
                    });
                }
                
                // Quantity or price change
                [quantityInput, priceInput, taxInput].forEach(input => {
                    if (input && !input.readOnly) {
                        input.addEventListener('input', function() {
                            calculateItemSubtotal(itemRow);
                            calculateOrderTotals();
                        });
                    }
                });
            }
            
            // Setup event listeners for existing items
            const existingItems = itemsContainer.querySelectorAll('.item-row');
            existingItems.forEach(item => {
                setupItemEventListeners(item);
                
                // Add event listener to the remove button
                const removeButton = item.querySelector('.remove-item');
                if (removeButton) {
                    removeButton.addEventListener('click', function() {
                        itemsContainer.removeChild(item);
                        updateItemNumbers();
                        checkItems();
                        calculateOrderTotals();
                    });
                }
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                const items = itemsContainer.querySelectorAll('.item-row');
                
                if (items.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one item to the purchase order.');
                }
            });
            
            // Load supplier details when supplier is selected
            const supplierSelect = document.getElementById('supplier_id');
            const shippingAddressTextarea = document.getElementById('shipping_address');
            const paymentTermsInput = document.getElementById('payment_terms');
            
            if (supplierSelect && !supplierSelect.disabled) {
                supplierSelect.addEventListener('change', function() {
                    const supplierId = this.value;
                    if (supplierId) {
                        fetch(`/accounting/suppliers/${supplierId}/details`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.address) {
                                    shippingAddressTextarea.value = data.address;
                                }
                                if (data.payment_terms) {
                                    paymentTermsInput.value = data.payment_terms;
                                }
                            });
                    }
                });
            }
            
            // Calculate initial totals
            calculateOrderTotals();
            
            // Check initially
            checkItems();
        });
    </script>
</x-app-layout>