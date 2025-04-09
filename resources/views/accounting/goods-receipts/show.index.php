<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
             <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Goods Receipt #{{ $receipt->receipt_number }}
                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                    @if($receipt->status == 'received' || $receipt->status == 'completed') bg-green-100 text-green-800
                    @elseif($receipt->status == 'partial') bg-yellow-100 text-yellow-800
                    @elseif($receipt->status == 'inspecting') bg-purple-100 text-purple-800
                    @elseif($receipt->status == 'returned') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst($receipt->status) }}
                </span>
            </h2>
             <div>
                {{-- Add actions like Print or Edit if applicable --}}
                {{-- <a href="#" class="ml-3 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Print
                </a> --}}
                {{-- Add Edit link if editing is allowed --}}
                {{-- @if($receipt->status == 'draft')
                    <a href="{{ route('accounting.goods-receipts.edit', $receipt) }}" class="ml-3 inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Edit
                    </a>
                @endif --}}
             </div>
         </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                    {{-- Summary Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Purchase Order</h3>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($receipt->purchaseOrder)
                                <a href="{{ route('accounting.purchase-orders.show', $receipt->purchaseOrder) }}" class="text-indigo-600 hover:underline">
                                    {{ $receipt->purchaseOrder->po_number }}
                                </a>
                                @else
                                <span class="text-gray-500 italic">N/A</span>
                                @endif
                            </p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Supplier</h3>
                            <p class="mt-1 text-sm text-gray-900">
                                 @if($receipt->purchaseOrder?->supplier)
                                <a href="{{ route('accounting.suppliers.show', $receipt->purchaseOrder->supplier) }}" class="text-indigo-600 hover:underline">
                                    {{ $receipt->purchaseOrder->supplier->name }}
                                </a>
                                @else
                                <span class="text-gray-500 italic">Unknown</span>
                                @endif
                            </p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Receipt Date</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $receipt->receipt_date->format('F d, Y') }}</p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Delivery Note #</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $receipt->delivery_note_number ?? 'N/A' }}</p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Received By</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $receipt->received_by_name }}</p> {{-- Adjust if using user ID --}}
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Created By</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $receipt->creator->name ?? 'N/A' }} on {{ $receipt->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                     {{-- Notes --}}
                     @if($receipt->notes)
                     <div class="pt-4 border-t mt-4">
                         <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Notes</h3>
                         <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $receipt->notes }}</p>
                     </div>
                     @endif

                      {{-- Received Items --}}
                    <div class="mt-6 pt-4 border-t">
                         <h3 class="text-lg font-medium text-gray-900 mb-4">Items Received</h3>
                         <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                         <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Received</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Rejected</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejection Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {{-- Ensure controller passes $receipt with 'items.purchaseOrderItem.inventoryItem' --}}
                                    @forelse($receipt->items as $item)
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->purchaseOrderItem?->inventoryItem?->item_code ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item->purchaseOrderItem?->description ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                {{ $item->purchaseOrderItem?->unit ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($item->quantity_received, 2) }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right {{ $item->quantity_rejected > 0 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">{{ number_format($item->quantity_rejected, 2) }}</td>
                                            <td class="px-4 py-4 whitespace-normal text-sm text-gray-500">{{ $item->rejection_reason ?? '' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center italic">No items recorded on this receipt.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                 </div>
                 <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                      <a href="{{ route('accounting.goods-receipts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                          {{ __('Back to Goods Receipts List') }}
                      </a>
                 </div>
            </div>
        </div>
    </div>
</x-app-layout>