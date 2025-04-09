<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record Goods Receipt for PO #{{ $purchaseOrder->po_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('accounting.goods-receipts.store') }}">
                    @csrf
                    <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">

                        {{-- PO & Supplier Info --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 pb-4 border-b">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Purchase Order Details</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    PO Number: <a href="{{ route('accounting.purchase-orders.show', $purchaseOrder) }}" class="text-indigo-600 hover:underline">{{ $purchaseOrder->po_number }}</a><br>
                                    Date Issued: {{ $purchaseOrder->date_issued->format('M d, Y') }}<br>
                                    Date Expected: {{ $purchaseOrder->date_expected ? $purchaseOrder->date_expected->format('M d, Y') : 'N/A' }}
                                </p>
                            </div>
                             <div>
                                <h3 class="text-lg font-medium text-gray-900">Supplier</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    <a href="{{ route('accounting.suppliers.show', $purchaseOrder->supplier) }}" class="text-indigo-600 hover:underline">{{ $purchaseOrder->supplier->name }}</a><br>
                                    {{ $purchaseOrder->supplier->address ?? '' }}<br>
                                     {{ $purchaseOrder->supplier->phone ?? '' }}
                                </p>
                            </div>
                        </div>

                        {{-- Receipt Header Details --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                             <div>
                                <x-input-label for="receipt_date" :value="__('Receipt Date')" class="required" />
                                <x-text-input id="receipt_date" class="block mt-1 w-full" type="date" name="receipt_date" :value="old('receipt_date', now()->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('receipt_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="delivery_note_number" :value="__('Delivery Note # (Optional)')" />
                                <x-text-input id="delivery_note_number" class="block mt-1 w-full" type="text" name="delivery_note_number" :value="old('delivery_note_number')" />
                                <x-input-error :messages="$errors->get('delivery_note_number')" class="mt-2" />
                            </div>
                             <div>
                                {{-- Assuming received_by is a name string for now --}}
                                <x-input-label for="received_by_name" :value="__('Received By')" class="required" />
                                <x-text-input id="received_by_name" class="block mt-1 w-full" type="text" name="received_by_name" :value="old('received_by_name', auth()->user()->name)" required />
                                <x-input-error :messages="$errors->get('received_by_name')" class="mt-2" />
                                {{-- Alternative: Dropdown of users --}}
                                {{-- <select name="received_by_user_id" id="received_by_user_id" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select User...</option>
                                    @foreach($users as $user) // Pass $users from controller
                                        <option value="{{ $user->id }}" {{ old('received_by_user_id', auth()->id()) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select> --}}
                            </div>
                        </div>

                         <div>
                            <x-input-label for="notes" :value="__('Notes (Optional)')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        {{-- Receipt Items --}}
                        <div class="mt-6 pt-4 border-t">
                             <h3 class="text-lg font-medium text-gray-900 mb-4">Items Received</h3>
                             <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Received (Prev)</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Received Now</th>
                                            {{-- Optional Rejection Fields --}}
                                            {{-- <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Rejected</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejection Reason</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($purchaseOrder->items as $index => $poItem)
                                            @php
                                                $outstanding = $poItem->quantity - $poItem->quantity_received;
                                            @endphp
                                            {{-- Only show items with outstanding quantity --}}
                                            @if($outstanding > 0)
                                            <tr>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                     {{ $poItem->inventoryItem->item_code ?? 'N/A' }} - {{ $poItem->description }}
                                                     <input type="hidden" name="items[{{ $poItem->id }}][purchase_order_item_id]" value="{{ $poItem->id }}">
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{ $poItem->unit }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($poItem->quantity, 2) }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($poItem->quantity_received, 2) }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-700">{{ number_format($outstanding, 2) }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right">
                                                     <x-text-input type="number" step="0.01" min="0" max="{{ $outstanding }}"
                                                                   name="items[{{ $poItem->id }}][quantity_received]"
                                                                   :value="old('items.'.$poItem->id.'.quantity_received', $outstanding)" {{-- Default to outstanding --}}
                                                                   class="w-24 text-right" required />
                                                     <x-input-error :messages="$errors->get('items.'.$poItem->id.'.quantity_received')" class="mt-1 text-xs" />
                                                </td>
                                                {{-- Optional Rejection Fields --}}
                                                {{-- <td class="px-4 py-4 whitespace-nowrap text-sm text-right">
                                                    <x-text-input type="number" step="0.01" min="0" name="items[{{ $poItem->id }}][quantity_rejected]" :value="old('items.'.$poItem->id.'.quantity_rejected', 0)" class="w-24 text-right" />
                                                </td>
                                                 <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                    <x-text-input type="text" name="items[{{ $poItem->id }}][rejection_reason]" :value="old('items.'.$poItem->id.'.rejection_reason')" class="w-full" />
                                                </td> --}}
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('accounting.purchase-orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __('Cancel') }}
                        </a>

                        <x-primary-button type="submit">
                            {{ __('Save Goods Receipt') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>