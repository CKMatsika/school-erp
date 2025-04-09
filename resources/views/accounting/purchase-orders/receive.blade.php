<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Receive Goods') }}: PO #{{ $purchaseOrder->po_number }}
            </h2>
            <a href="{{ route('accounting.purchase-orders.show', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Purchase Order
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            
            @if (session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Order Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Supplier Information</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $purchaseOrder->supplier->name }}</p>
                            @if($purchaseOrder->supplier->contact_person)
                                <p class="text-sm text-gray-600">Contact: {{ $purchaseOrder->supplier->contact_person }}</p>
                            @endif
                            @if($purchaseOrder->supplier->email)
                                <p class="text-sm text-gray-600">Email: {{ $purchaseOrder->supplier->email }}</p>
                            @endif
                            @if($purchaseOrder->supplier->phone)
                                <p class="text-sm text-gray-600">Phone: {{ $purchaseOrder->supplier->phone }}</p>
                            @endif
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Purchase Order Information</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">PO Number</p>
                                    <p class="text-sm text-gray-900">{{ $purchaseOrder->po_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Date Issued</p>
                                    <p class="text-sm text-gray-900">{{ $purchaseOrder->date_issued->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Expected Delivery</p>
                                    <p class="text-sm text-gray-900">{{ $purchaseOrder->date_expected ? $purchaseOrder->date_expected->format('M d, Y') : 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Status</p>
                                    <p class="text-sm font-medium {{ 
                                        $purchaseOrder->status == 'approved' ? 'text-green-600' : 
                                        ($purchaseOrder->status == 'issued' ? 'text-blue-600' : 
                                        ($purchaseOrder->status == 'partial' ? 'text-yellow-600' : 'text-gray-600')) 
                                    }}">
                                        {{ ucfirst($purchaseOrder->status) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goods Receipt Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.goods-receipts.store') }}">
                        @csrf
                        <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <x-input-label for="receipt_date" :value="__('Receipt Date')" />
                                <x-text-input id="receipt_date" class="block mt-1 w-full" type="date" name="receipt_date" :value="old('receipt_date', now()->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('receipt_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="delivery_note_number" :value="__('Delivery Note #')" />
                                <x-text-input id="delivery_note_number" class="block mt-1 w-full" type="text" name="delivery_note_number" :value="old('delivery_note_number')" />
                                <x-input-error :messages="$errors->get('delivery_note_number')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="carrier" :value="__('Carrier/Shipping Company')" />
                                <x-text-input id="carrier" class="block mt-1 w-full" type="text" name="carrier" :value="old('carrier')" />
                                <x-input-error :messages="$errors->get('carrier')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Items to Receive</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Previously Received</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Receive Now</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Complete?</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($purchaseOrder->items as $index => $item)
                                            @if($item->quantity > $item->quantity_received)
                                                <tr>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <div class="font-medium">
                                                            @if($item->inventory_item)
                                                                {{ $item->inventory_item->item_code }} - {{ $item->inventory_item->name }}
                                                                <input type="hidden" name="receipt_items[{{ $index }}][po_item_id]" value="{{ $item->id }}">
                                                                <input type="hidden" name="receipt_items[{{ $index }}][inventory_item_id]" value="{{ $item->inventory_item_id }}">
                                                            @else
                                                                Unknown Item
                                                            @endif
                                                        </div>
                                                        @if($item->description)
                                                            <div class="text-xs text-gray-500 mt-1">{{ $item->description }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                        {{ number_format($item->quantity, 2) }}
                                                        @if($item->inventory_item)
                                                            {{ $item->inventory_item->unit_of_measure }}
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                        {{ number_format($item->quantity_received, 2) }}
                                                        @if($item->inventory_item)
                                                            {{ $item->inventory_item->unit_of_measure }}
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center justify-end">
                                                            <input type="number" name="receipt_items[{{ $index }}][quantity]" class="quantity-input rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-24 text-right" 
                                                                value="{{ old('receipt_items.' . $index . '.quantity', $item->quantity - $item->quantity_received) }}" 
                                                                min="0.01" 
                                                                max="{{ $item->quantity - $item->quantity_received }}" 
                                                                step="0.01" 
                                                                required>
                                                            <span class="ml-2">
                                                                @if($item->inventory_item)
                                                                    {{ $item->inventory_item->unit_of_measure }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                        @error("receipt_items.{$index}.quantity")
                                                            <p class="text-red-500 text-xs mt-1 text-right">{{ $message }}</p>
                                                        @enderror
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <input type="checkbox" name="receipt_items[{{ $index }}][is_complete]" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                                            {{ old('receipt_items.' . $index . '.is_complete') || (old('receipt_items.' . $index . '.quantity', $item->quantity - $item->quantity_received) >= $item->quantity - $item->quantity_received) ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <select name="receipt_items[{{ $index }}][condition]" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                            <option value="good" {{ old('receipt_items.' . $index . '.condition') == 'good' ? 'selected' : '' }}>Good</option>
                                                            <option value="damaged" {{ old('receipt_items.' . $index . '.condition') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                                            <option value="partial" {{ old('receipt_items.' . $index . '.condition') == 'partial' ? 'selected' : '' }}>Partially Damaged</option>
                                                            <option value="wrong" {{ old('receipt_items.' . $index . '.condition') == 'wrong' ? 'selected' : '' }}>Wrong Item</option>
                                                        </select>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <input type="text" name="receipt_items[{{ $index }}][notes]" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-full" 
                                                            value="{{ old('receipt_items.' . $index . '.notes') }}" placeholder="Optional notes">
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Additional Information</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <x-input-label for="notes" :value="__('Receipt Notes')" />
                                    <textarea id="notes" name="notes" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('notes') }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <div class="flex items-center mr-auto">
                                <input type="checkbox" id="process_inventory" name="process_inventory" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" checked>
                                <label for="process_inventory" class="ml-2 text-sm text-gray-600">{{ __('Update Inventory Immediately') }}</label>
                            </div>

                            <a href="{{ route('accounting.purchase-orders.show', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Record Receipt') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInputs = document.querySelectorAll('.quantity-input');
            
            quantityInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const maxQuantity = parseFloat(this.getAttribute('max'));
                    const currentValue = parseFloat(this.value);
                    const completeCheckbox = this.closest('tr').querySelector('input[type="checkbox"]');
                    
                    if (currentValue >= maxQuantity) {
                        completeCheckbox.checked = true;
                    } else {
                        completeCheckbox.checked = false;
                    }
                });
            });
        });
    </script>
</x-app-layout>