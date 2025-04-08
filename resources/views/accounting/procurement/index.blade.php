<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Procurement Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Total Purchases -->
                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                            <h4 class="text-sm font-medium text-indigo-800">Total Purchases</h4>
                            <p class="text-2xl font-bold text-indigo-600">{{ number_format($totalPurchases ?? 0, 2) }}</p>
                            <p class="text-xs text-indigo-600">Year-to-date</p>
                        </div>
                        <!-- Inventory Value -->
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <h4 class="text-sm font-medium text-green-800">Inventory Value</h4>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($inventoryValue ?? 0, 2) }}</p>
                            <p class="text-xs text-green-600">Current valuation</p>
                        </div>
                        <!-- Item Count -->
                        <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                            <h4 class="text-sm font-medium text-amber-800">Inventory Items</h4>
                            <p class="text-2xl font-bold text-amber-600">{{ $inventoryItemCount ?? 0 }}</p>
                            <p class="text-xs text-amber-600">Total items</p>
                        </div>
                        <!-- Supplier Count -->
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-800">Suppliers</h4>
                            <p class="text-2xl font-bold text-blue-600">{{ $supplierCount ?? 0 }}</p>
                            <p class="text-xs text-blue-600">Active suppliers</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Purchase Requests -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 h-full">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Purchase Requests</h3>
                            <a href="{{ route('accounting.purchase-requests.create') }}" class="text-sm text-indigo-600 hover:text-indigo-900">New Request</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($pendingPRs ?? [] as $pr)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <a href="{{ route('accounting.purchase-requests.show', $pr) }}">{{ $pr->pr_number }}</a>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $pr->date_requested->format('M d, Y') }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ ucfirst($pr->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">No pending requests</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 text-right">
                            <a href="{{ route('accounting.purchase-requests.index') }}" class="text-sm text-gray-600 hover:text-gray-900">View all</a>
                        </div>
                    </div>
                </div>

                <!-- Recent Purchase Orders -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 h-full">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Purchase Orders</h3>
                            <a href="{{ route('accounting.purchase-orders.create') }}" class="text-sm text-indigo-600 hover:text-indigo-900">New Order</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                        <th scope="col" class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($recentPOs ?? [] as $po)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <a href="{{ route('accounting.purchase-orders.show', $po) }}">{{ $po->po_number }}</a>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $po->supplier->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($po->status == 'approved' || $po->status == 'issued') bg-green-100 text-green-800
                                                    @elseif($po->status == 'partial') bg-yellow-100 text-yellow-800
                                                    @elseif($po->status == 'completed') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($po->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">No recent orders</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 text-right">
                            <a href="{{ route('accounting.purchase-orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">View all</a>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Items -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 h-full">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Low Stock Items</h3>
                            <a href="{{ route('accounting.inventory-items.index', ['low_stock' => 1]) }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                        <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Current</th>
                                        <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($lowStockItems ?? [] as $item)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <a href="{{ route('accounting.inventory-items.show', $item) }}">{{ $item->name }}</a>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-red-600 text-right">{{ number_format($item->current_stock, 2) }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($item->reorder_level, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">No low stock items</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Active Tenders -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Active Tenders</h3>
                            <a href="{{ route('accounting.tenders.create') }}" class="text-sm text-indigo-600 hover:text-indigo-900">New Tender</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Closing</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($activeTenders ?? [] as $tender)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <a href="{{ route('accounting.tenders.show', $tender) }}">{{ $tender->title }}</a>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $tender->closing_date->format('M d, Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">No active tenders</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 text-right">
                            <a href="{{ route('accounting.tenders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">View all</a>
                        </div>
                    </div>
                </div>

                <!-- Expiring Contracts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Expiring Contracts</h3>
                            <a href="{{ route('accounting.procurement-contracts.create') }}" class="text-sm text-indigo-600 hover:text-indigo-900">New Contract</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($expiringContracts ?? [] as $contract)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <a href="{{ route('accounting.procurement-contracts.show', $contract) }}">{{ $contract->title }}</a>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $contract->supplier->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $contract->end_date->format('M d, Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">No expiring contracts</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 text-right">
                            <a href="{{ route('accounting.procurement-contracts.index') }}" class="text-sm text-gray-600 hover:text-gray-900">View all</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>