<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Purchase Order') }}: {{ $purchaseOrder->po_number }}
            </h2>
            <div class="flex space-x-2">
                @if($purchaseOrder->status == 'draft' || $purchaseOrder->status == 'pending')
                    <a href="{{ route('accounting.purchase-orders.edit', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Edit
                    </a>
                @endif

                @if($purchaseOrder->status == 'pending')
                    <a href="{{ route('accounting.purchase-orders.approve', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Approve
                    </a>
                @endif

                @if($purchaseOrder->status == 'approved')
                    <a href="{{ route('accounting.purchase-orders.issue', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Issue to Supplier
                    </a>
                @endif

                @if($purchaseOrder->status == 'approved' || $purchaseOrder->status == 'issued' || $purchaseOrder->status == 'partial')
                    <a href="{{ route('accounting.purchase-orders.receive', $purchaseOrder) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Receive Items
                    </a>
                @endif

                <a href="{{ route('accounting.purchase-orders.print', $purchaseOrder) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print
                </a>
            </div>
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

            <!-- Status Banner -->
            <div class="mb-6 {{ 
                $purchaseOrder->status == 'approved' ? 'bg-green-100 border-green-500 text-green-700' : 
                ($purchaseOrder->status == 'issued' ? 'bg-blue-100 border-blue-500 text-blue-700' : 
                ($purchaseOrder->status == 'pending' ? 'bg-yellow-100 border-yellow-500 text-yellow-700' : 
                ($purchaseOrder->status == 'cancelled' ? 'bg-red-100 border-red-500 text-red-700' : 
                ($purchaseOrder->status == 'partial' ? 'bg-purple-100 border-purple-500 text-purple-700' : 
                ($purchaseOrder->status == 'completed' ? 'bg-teal-100 border-teal-500 text-teal-700' : 
                'bg-gray-100 border-gray-500 text-gray-700'))))) 
            }} border-l-4 p-4 rounded shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($purchaseOrder->status == 'approved')
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($purchaseOrder->status == 'issued')
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        @elseif($purchaseOrder->status == 'pending')
                            <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($purchaseOrder->status == 'cancelled')
                            <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($purchaseOrder->status == 'partial')
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        @elseif($purchaseOrder->status == 'completed')
                            <svg class="h-5 w-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <h3 class="font-medium">
                            Status: <span class="font-semibold">{{ ucfirst($purchaseOrder->status) }}</span>
                        </h3>
                        @if($purchaseOrder->status == 'approved')
                            <p>This purchase order was approved by {{ $purchaseOrder->approved_by_name }} on {{ $purchaseOrder->approved_at->format('M d, Y H:i') }}</p>
                        @elseif($purchaseOrder->status == 'issued')
                            <p>This purchase order was issued to the supplier on {{ $purchaseOrder->issued_at->format('M d, Y') }}</p>
                        @elseif($purchaseOrder->status == 'partial')
                            <p>This purchase order has been partially received. {{ $purchaseOrder->items_received_count }} of {{ $purchaseOrder->items_count }} items received.</p>
                        @elseif($purchaseOrder->status == 'completed')
                            <p>All items on this purchase order have been received.</p>
                        @elseif($purchaseOrder->status == 'cancelled')
                            <p>This purchase order was cancelled by {{ $purchaseOrder->cancelled_by_name }} on {{ $purchaseOrder->cancelled_at->format('M d, Y H:i') }}</p>
                            @if($purchaseOrder->cancellation_reason)
                                <p class="mt-1">Reason: {{ $purchaseOrder->cancellation_reason }}</p>
                            @endif
                        @elseif($purchaseOrder->status == 'draft')
                            <p>This is a draft purchase order that has not been submitted for approval.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Purchase Order Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Supplier Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
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
                                <div class="mt-2">
                                    <a href="{{ route('accounting.suppliers.show', $purchaseOrder->supplier) }}" class="text-sm text-indigo-600 hover:text-indigo-900">View Supplier Details</a>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Purchase Order Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
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
                                        <p class="text-sm font-medium text-gray-500">Payment Terms</p>
                                        <p class="text-sm text-gray-900">{{ $purchaseOrder->payment_terms ?? 'Not specified' }}</p>
                                    </div>
                                </div>
                                @if($purchaseOrder->requisition)
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p class="text-sm font-medium text-gray-500">Related Requisition</p>
                                        <p class="text-sm text-indigo-600 hover:text-indigo-900">
                                            <a href="{{ route('accounting.purchase-requisitions.show', $purchaseOrder->requisition) }}">
                                                {{ $purchaseOrder->requisition->requisition_number }} ({{ $purchaseOrder->requisition->department }})
                                            </a>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($purchaseOrder->shipping_address || $purchaseOrder->shipping_method || $purchaseOrder->notes)
                        <div class="mt-6">
                            @if($purchaseOrder->shipping_address)
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Shipping Address</h3>
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <p class="whitespace-pre-line">{{ $purchaseOrder->shipping_address }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($purchaseOrder->shipping_method)
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Shipping Method</h3>
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <p>{{ $purchaseOrder->shipping_method }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($purchaseOrder->notes)
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Notes</h3>
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <p class="whitespace-pre-line">{{ $purchaseOrder->notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Purchase Order Items -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
                </div>
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($purchaseOrder->items as $index => $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($item->inventory_item)
                                                <a href="{{ route('accounting.inventory-items.show', $item->inventory_item) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $item->inventory_item->item_code }}
                                                </a>
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            @if($item->inventory_item)
                                                <div class="font-medium">{{ $item->inventory_item->name }}</div>
                                            @endif
                                            @if($item->description)
                                                <div class="text-gray-600 mt-1">{{ $item->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                            {{ number_format($item->quantity, 2) }}
                                            @if($item->inventory_item)
                                                {{ $item->inventory_item->unit_of_measure }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $item->quantity_received < $item->quantity ? 'text-yellow-600' : 'text-green-600' }}">
                                            {{ number_format($item->quantity_received, 2) }}
                                            @if($item->inventory_item)
                                                {{ $item->inventory_item->unit_of_measure }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                            {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                            {{ number_format($item->tax_amount, 2) }}
                                            @if($item->tax_rate > 0)
                                                <span class="text-xs text-gray-500">({{ number_format($item->tax_rate, 2) }}%)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                            {{ number_format($item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No items found in this purchase order</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Subtotal:</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">{{ number_format($purchaseOrder->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Tax:</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">{{ number_format($purchaseOrder->tax_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-base font-semibold text-gray-900 text-right">Total:</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-base font-semibold text-gray-900 text-right">{{ number_format($purchaseOrder->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Goods Receipts -->
            @if(count($goodsReceipts) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="px-6 py-4 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Goods Receipts</h3>
                    </div>
                    <div class="px-6 py-4 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received By</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Note</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($goodsReceipts as $receipt)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <a href="{{ route('accounting.goods-receipts.show', $receipt) }}">{{ $receipt->receipt_number }}</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $receipt->receipt_date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $receipt->received_by_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $receipt->delivery_note_number ?? 'Not specified' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($receipt->status == 'received') bg-green-100 text-green-800
                                                    @elseif($receipt->status == 'partial') bg-yellow-100 text-yellow-800
                                                    @elseif($receipt->status == 'inspecting') bg-purple-100 text-purple-800
                                                    @elseif($receipt->status == 'returned') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($receipt->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <a href="{{ route('accounting.goods-receipts.show', $receipt) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Activity Log -->
            @if(count($purchaseOrder->activityLog) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Activity Log</h3>
                    </div>
                    <div class="px-6 py-4 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($purchaseOrder->activityLog as $activity)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $activity->created_at->format('M d, Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($activity->action == 'approved') bg-green-100 text-green-800
                                                    @elseif($activity->action == 'issued') bg-blue-100 text-blue-800
                                                    @elseif($activity->action == 'received') bg-purple-100 text-purple-800
                                                    @elseif($activity->action == 'cancelled') bg-red-100 text-red-800
                                                    @elseif($activity->action == 'created') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($activity->action) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activity->user_name }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ $activity->comments ?? 'No comments' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>