<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Goods Receipt #{{ $receipt->receipt_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8"> {{-- Smaller max-width --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('accounting.goods-receipts.update', $receipt) }}">
                    @csrf
                    @method('PUT') {{-- Or PATCH --}}

                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        {{-- Display non-editable info --}}
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                             <div>
                                <span class="text-sm font-medium text-gray-500">PO Number:</span>
                                <span class="ml-2 text-sm text-gray-900">
                                    @if($receipt->purchaseOrder)
                                    <a href="{{ route('accounting.purchase-orders.show', $receipt->purchaseOrder) }}" class="text-indigo-600 hover:underline">
                                        {{ $receipt->purchaseOrder->po_number }}
                                    </a>
                                    @else N/A @endif
                                </span>
                             </div>
                             <div>
                                <span class="text-sm font-medium text-gray-500">Supplier:</span>
                                 <span class="ml-2 text-sm text-gray-900">
                                    @if($receipt->purchaseOrder?->supplier)
                                    <a href="{{ route('accounting.suppliers.show', $receipt->purchaseOrder->supplier) }}" class="text-indigo-600 hover:underline">
                                        {{ $receipt->purchaseOrder->supplier->name }}
                                    </a>
                                    @else Unknown @endif
                                </span>
                             </div>
                              <div>
                                <span class="text-sm font-medium text-gray-500">Status:</span>
                                <span class="ml-2 text-sm text-gray-900">{{ ucfirst($receipt->status) }}</span>
                             </div>
                         </div>

                        {{-- Editable Fields --}}
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div>
                                <x-input-label for="receipt_date" :value="__('Receipt Date')" class="required" />
                                <x-text-input id="receipt_date" class="block mt-1 w-full" type="date" name="receipt_date" :value="old('receipt_date', $receipt->receipt_date->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('receipt_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="delivery_note_number" :value="__('Delivery Note # (Optional)')" />
                                <x-text-input id="delivery_note_number" class="block mt-1 w-full" type="text" name="delivery_note_number" :value="old('delivery_note_number', $receipt->delivery_note_number)" />
                                <x-input-error :messages="$errors->get('delivery_note_number')" class="mt-2" />
                            </div>
                             <div>
                                <x-input-label for="received_by_name" :value="__('Received By')" class="required" />
                                <x-text-input id="received_by_name" class="block mt-1 w-full" type="text" name="received_by_name" :value="old('received_by_name', $receipt->received_by_name)" required />
                                <x-input-error :messages="$errors->get('received_by_name')" class="mt-2" />
                            </div>
                        </div>

                         <div>
                            <x-input-label for="notes" :value="__('Notes (Optional)')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes', $receipt->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                         <p class="text-sm text-gray-500 italic mt-4">Note: Received item quantities cannot be edited here. Please use stock adjustments or returns if necessary.</p>

                    </div>

                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('accounting.goods-receipts.show', $receipt) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __('Cancel') }}
                        </a>

                        <x-primary-button type="submit">
                            {{ __('Update Receipt Info') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>