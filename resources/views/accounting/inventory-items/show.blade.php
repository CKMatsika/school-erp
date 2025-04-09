<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Item Details') }}: {{ $item->name }}
            </h2>
            <div>
                <a href="{{ route('accounting.inventory-items.edit', $item) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                </a>
                <a href="{{ route('accounting.inventory-items.history', $item) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Inventory History
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

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('accounting.inventory-transactions.create', ['type' => 'stock_in', 'item_id' => $item->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Stock In
                        </a>
                        <a href="{{ route('accounting.inventory-transactions.create', ['type' => 'stock_out', 'item_id' => $item->id]) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            Stock Out
                        </a>
                        <a href="{{ route('accounting.purchase-requisitions.create', ['item_id' => $item->id]) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring ring-purple-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Create Requisition
                        </a>
                    </div>
                </div>
            </div>

            <!-- Item Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-semibold text-gray-800 mb-2">{{ $item->name }}</h3>
                            <div class="flex items-center mb-4">
                                <span class="text-xs bg-gray-200 text-gray-800 px-2 py-1 rounded-full mr-2">{{ $item->item_code }}</span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($item->category)
                                    <span class="ml-4 text-sm text-gray-600">
                                        Category: 
                                        <a href="{{ route('accounting.item-categories.show', $item->category) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $item->category->name }}
                                        </a>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('accounting.inventory-items.destroy', $item) }}" class="flex" onsubmit="return confirm('Are you sure you want to delete this item?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 focus:outline-none">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <!-- Inventory Status -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h4 class="font-medium text-lg text-gray-900 mb-2">Inventory Status</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Current Stock:</span>
                                    <span class="text-sm font-medium {{ $item->isLowOnStock() ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ number_format($item->current_stock, 2) }} {{ $item->unit_of_measure }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Reorder Level:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($item->reorder_level, 2) }} {{ $item->unit_of_measure }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Reorder Quantity:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($item->reorder_quantity, 2) }} {{ $item->unit_of_measure }}</span>
                                </div>
                                @if($item->isLowOnStock())
                                    <div class="bg-red-100 text-red-800 px-3 py-2 rounded-md text-sm mt-2">
                                        <p class="font-medium">Low Stock Alert</p>
                                        <p>Current stock is below reorder level</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Cost Information -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h4 class="font-medium text-lg text-gray-900 mb-2">Cost Information</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Unit Cost:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($item->unit_cost, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Tax Rate:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($item->tax_rate, 2) }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Total Value:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($item->current_stock * $item->unit_cost, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Suppliers Information -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h4 class="font-medium text-lg text-gray-900 mb-2">Preferred Suppliers</h4>
                            <div class="space-y-2">
                                @if(count($preferredSuppliers) > 0)
                                    @foreach($preferredSuppliers as $supplier)
                                        <div class="text-sm">
                                            <a href="{{ route('accounting.suppliers.show', $supplier) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $supplier->name }}
                                            </a>
                                            @if($supplier->pivot->unit_price)
                                                <span class="text-gray-600 ml-2">{{ number_format($supplier->pivot->unit_price, 2) }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-sm text-gray-500">No preferred suppliers</p>
                                @endif
                                <div class="mt-2">
                                    <a href="{{ route('accounting.inventory-items.suppliers', $item) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                        Manage Suppliers
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description and Notes -->
                    @if($item->description || $item->notes)
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            @if($item->description)
                                <div class="mb-4">
                                    <h4 class="font-medium text-lg text-gray-900 mb-2">Description</h4>
                                    <p class="text-gray-700">{{ $item->description }}</p>
                                </div>
                            @endif

                            @if($item->notes)
                                <div>
                                    <h4 class="font-medium text-lg text-gray-900 mb-2">Notes</h4>
                                    <p class="text-gray-700">{{ $item->notes }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                        <a href="{{ route('accounting.inventory-items.history', $item) }}" class="text-indigo-600 hover:text-indigo-900">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($transaction->transaction_type == 'stock_in') bg-green-100 text-green-800
                                                @elseif($transaction->transaction_type == 'stock_out') bg-red-100 text-red-800
                                                @elseif($transaction->transaction_type == 'adjustment') bg-yellow-100 text-yellow-800
                                                @else bg-blue-100 text-blue-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->reference }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right 
                                            @if($transaction->quantity > 0) text-green-600 
                                            @elseif($transaction->quantity < 0) text-red-600 
                                            @else text-gray-900 
                                            @endif">
                                            {{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            {{ number_format($transaction->unit_cost, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $transaction->source }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No recent transactions</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Open Purchase Orders -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Open Purchase Orders</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($openPurchaseOrders as $po)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                            <a href="{{ route('accounting.purchase-orders.show', $po->purchase_order) }}">{{ $po->purchase_order->po_number }}</a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="{{ route('accounting.suppliers.show', $po->purchase_order->supplier) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $po->purchase_order->supplier->name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $po->purchase_order->date_issued->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($po->quantity, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($po->quantity_received, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($po->purchase_order->status == 'approved' || $po->purchase_order->status == 'issued') bg-green-100 text-green-800
                                                @elseif($po->purchase_order->status == 'partial') bg-yellow-100 text-yellow-800
                                                @elseif($po->purchase_order->status == 'completed') bg-blue-100 text-blue-800
                                                @elseif($po->purchase_order->status == 'cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($po->purchase_order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <a href="{{ route('accounting.purchase-orders.receive', ['purchase_order' => $po->purchase_order, 'item_id' => $item->id]) }}" class="text-green-600 hover:text-green-900">Receive</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No open purchase orders</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>