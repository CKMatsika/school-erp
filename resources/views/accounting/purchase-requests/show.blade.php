<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Purchase Requisition') }}: {{ $requisition->requisition_number }}
            </h2>
            <div class="flex">
                @if($requisition->status == 'draft' || $requisition->status == 'pending')
                    <a href="{{ route('accounting.purchase-requisitions.edit', $requisition) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Edit
                    </a>
                @endif

                @if($requisition->status == 'pending' && auth()->user()->can('approve-purchase-requisitions'))
                    <a href="{{ route('accounting.purchase-requisitions.approve', $requisition) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Approve
                    </a>
                    <a href="{{ route('accounting.purchase-requisitions.reject', $requisition) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Reject
                    </a>
                @endif

                @if($requisition->status == 'approved' && !$requisition->has_purchase_order)
                    <a href="{{ route('accounting.purchase-orders.create', ['requisition_id' => $requisition->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Create PO
                    </a>
                @endif

                <a href="{{ route('accounting.purchase-requisitions.print', $requisition) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 ml-2">
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
                $requisition->status == 'approved' ? 'bg-green-100 border-green-500 text-green-700' : 
                ($requisition->status == 'pending' ? 'bg-yellow-100 border-yellow-500 text-yellow-700' : 
                ($requisition->status == 'rejected' || $requisition->status == 'cancelled' ? 'bg-red-100 border-red-500 text-red-700' : 
                ($requisition->status == 'ordered' ? 'bg-blue-100 border-blue-500 text-blue-700' : 
                'bg-gray-100 border-gray-500 text-gray-700'))) 
            }} border-l-4 p-4 rounded shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($requisition->status == 'approved')
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($requisition->status == 'pending')
                            <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($requisition->status == 'rejected' || $requisition->status == 'cancelled')
                            <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($requisition->status == 'ordered')
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <h3 class="font-medium">
                            Status: <span class="font-semibold">{{ ucfirst($requisition->status) }}</span>
                        </h3>
                        @if($requisition->status == 'approved')
                            <p>This requisition was approved by {{ $requisition->approved_by_name }} on {{ $requisition->approved_at->format('M d, Y H:i') }}</p>
                        @elseif($requisition->status == 'rejected')
                            <p>This requisition was rejected by {{ $requisition->rejected_by_name }} on {{ $requisition->rejected_at->format('M d, Y H:i') }}</p>
                            @if($requisition->rejection_reason)
                                <p class="mt-1">Reason: {{ $requisition->rejection_reason }}</p>
                            @endif
                        @elseif($requisition->status == 'ordered')
                            <p>This requisition has been converted to purchase order(s).</p>
                        @elseif($requisition->status == 'draft')
                            <p>This is a draft requisition that has not been submitted for approval.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Requisition Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Requisition Information</h3>
                            <table class="min-w-full">
                                <tbody class="divide-y divide-gray-200">
                                    <tr>
                                        <td class="py-2 text-sm font-medium text-gray-500">Requisition Number</td>
                                        <td class="py-2 text-sm text-gray-900">{{ $requisition->requisition_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-sm font-medium text-gray-500">Department</td>
                                        <td class="py-2 text-sm text-gray-900">{{ $requisition->department ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-sm font-medium text-gray-500">Priority</td>
                                        <td class="py-2 text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($requisition->priority == 'low') bg-green-100 text-green-800
                                                @elseif($requisition->priority == 'medium') bg-blue-100 text-blue-800
                                                @elseif($requisition->priority == 'high') bg-yellow-100 text-yellow-800
                                                @elseif($requisition->priority == 'critical') bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($requisition->priority) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-sm font-medium text-gray-500">Required Date</td>
                                        <td class="py-2 text-sm text-gray-900">{{ $requisition->required_date ? $requisition->required_date->format('M d, Y') : 'Not specified' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Request Information</h3>
                            <table class="min-w-full">
                                <tbody class="divide-y divide-gray-200">
                                    <tr>
                                        <td class="py-2 text-sm font-medium text-gray-500">Requested By</td>
                                        <td class="py-2 text-sm text-gray-900">{{ $requisition->requested_by_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-sm font-medium text-gray-500">Request Date</td>
                                        <td class="py-2 text-sm text-gray-900">{{ $requisition->request_date->format('M d, Y') }}</td>
                                    </tr>
                                    @if($requisition->approved_by)
                                    <tr>
                                        <td class="py-2 text-sm font-medium text-gray-500">Approved By</td>
                                        <td class="py-2 text-sm text-gray-900">{{ $requisition->approved_by_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-sm font-medium text-gray-500">Approved Date</td>
                                        <td class="py-2 text-sm text-gray-900">{{ $requisition->approved_at ? $requisition->approved_at->format('M d, Y') : 'N/A' }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($requisition->notes)
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Notes / Purpose</h3>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <p class="text-gray-700">{{ $requisition->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Requisition Items -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Requisition Items</h3>
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
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($requisition->items as $index => $item)
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
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($item->is_ordered) bg-blue-100 text-blue-800
                                                @else
                                                    @if($requisition->status == 'approved') bg-green-100 text-green-800
                                                    @elseif($requisition->status == 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($requisition->status == 'rejected' || $requisition->status == 'cancelled') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif
                                                @endif">
                                                {{ $item->is_ordered ? 'Ordered' : ucfirst($requisition->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No items found in this requisition</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Related Purchase Orders -->
            @if($purchaseOrders && count($purchaseOrders) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Related Purchase Orders</h3>
                    </div>
                    <div class="px-6 py-4 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($purchaseOrders as $po)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <a href="{{ route('accounting.purchase-orders.show', $po) }}">{{ $po->po_number }}</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <a href="{{ route('accounting.suppliers.show', $po->supplier) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $po->supplier->name }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $po->date_issued->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($po->total_amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($po->status == 'approved' || $po->status == 'issued') bg-green-100 text-green-800
                                                    @elseif($po->status == 'partial') bg-yellow-100 text-yellow-800
                                                    @elseif($po->status == 'completed') bg-blue-100 text-blue-800
                                                    @elseif($po->status == 'cancelled') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($po->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <a href="{{ route('accounting.purchase-orders.show', $po) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Approval History -->
            @if(count($requisition->approvalHistory) > 0)
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Approval History</h3>
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
                                    @foreach($requisition->approvalHistory as $history)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $history->created_at->format('M d, Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($history->action == 'approved') bg-green-100 text-green-800
                                                    @elseif($history->action == 'rejected') bg-red-100 text-red-800
                                                    @elseif($history->action == 'submitted') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($history->action) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $history->user_name }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ $history->comments ?? 'No comments' }}</td>
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