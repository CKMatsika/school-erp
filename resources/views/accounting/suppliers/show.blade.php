<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Supplier Details') }}
            </h2>
            <div>
                <a href="{{ route('accounting.suppliers.edit', $supplier) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                </a>
                <a href="{{ route('accounting.suppliers.performance', $supplier) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Performance
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

            <!-- Supplier Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-semibold text-gray-800 mb-2">{{ $supplier->name }}</h3>
                            <div class="flex items-center mb-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($supplier->tax_number)
                                    <span class="ml-4 text-sm text-gray-600">Tax Number: {{ $supplier->tax_number }}</span>
                                @endif
                                @if($supplier->registration_number)
                                    <span class="ml-4 text-sm text-gray-600">Reg. Number: {{ $supplier->registration_number }}</span>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('accounting.suppliers.destroy', $supplier) }}" class="flex" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 focus:outline-none">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                        <!-- Contact Information -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h4 class="font-medium text-lg text-gray-900 mb-2">Contact Information</h4>
                            <div class="space-y-2">
                                @if($supplier->contact_person)
                                    <p class="text-sm"><span class="font-medium">Contact Person:</span> {{ $supplier->contact_person }}</p>
                                @endif
                                @if($supplier->email)
                                    <p class="text-sm"><span class="font-medium">Email:</span> {{ $supplier->email }}</p>
                                @endif
                                @if($supplier->phone)
                                    <p class="text-sm"><span class="font-medium">Phone:</span> {{ $supplier->phone }}</p>
                                @endif
                                @if($supplier->website)
                                    <p class="text-sm"><span class="font-medium">Website:</span> <a href="{{ $supplier->website }}" target="_blank" class="text-blue-600 hover:text-blue-900">{{ $supplier->website }}</a></p>
                                @endif
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h4 class="font-medium text-lg text-gray-900 mb-2">Address</h4>
                            <div class="space-y-2">
                                @if($supplier->address || $supplier->city || $supplier->state || $supplier->postal_code || $supplier->country)
                                    <p class="text-sm">{{ $supplier->full_address }}</p>
                                @else
                                    <p class="text-sm text-gray-500">No address information provided</p>
                                @endif
                            </div>
                        </div>

                        <!-- Banking Information -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h4 class="font-medium text-lg text-gray-900 mb-2">Banking Information</h4>
                            <div class="space-y-2">
                                @if($supplier->bank_name || $supplier->account_number || $supplier->bank_branch_code || $supplier->bank_swift_code)
                                    @if($supplier->bank_name)
                                        <p class="text-sm"><span class="font-medium">Bank:</span> {{ $supplier->bank_name }}</p>
                                    @endif
                                    @if($supplier->account_number)
                                        <p class="text-sm"><span class="font-medium">Account Number:</span> {{ $supplier->account_number }}</p>
                                    @endif
                                    @if($supplier->bank_branch_code)
                                        <p class="text-sm"><span class="font-medium">Branch Code:</span> {{ $supplier->bank_branch_code }}</p>
                                    @endif
                                    @if($supplier->bank_swift_code)
                                        <p class="text-sm"><span class="font-medium">SWIFT/BIC:</span> {{ $supplier->bank_swift_code }}</p>
                                    @endif
                                @else
                                    <p class="text-sm text-gray-500">No banking information provided</p>
                                @endif
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 md:col-span-2 lg:col-span-3">
                            <h4 class="font-medium text-lg text-gray-900 mb-2">Additional Information</h4>
                            <div class="space-y-2">
                                @if($supplier->payment_terms)
                                    <p class="text-sm"><span class="font-medium">Payment Terms:</span> {{ $supplier->payment_terms }}</p>
                                @endif
                                @if($supplier->notes)
                                    <p class="text-sm"><span class="font-medium">Notes:</span></p>
                                    <p class="text-sm mt-1">{{ $supplier->notes }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Orders -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Purchase Orders</h3>
                        <a href="{{ route('accounting.purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="text-indigo-600 hover:text-indigo-900">Create New Order</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Number</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Issued</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($purchaseOrders as $order)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                            <a href="{{ route('accounting.purchase-orders.show', $order) }}">{{ $order->po_number }}</a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->date_issued->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($order->total_amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($order->status == 'approved' || $order->status == 'issued') bg-green-100 text-green-800
                                                @elseif($order->status == 'partial') bg-yellow-100 text-yellow-800
                                                @elseif($order->status == 'completed') bg-blue-100 text-blue-800
                                                @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <a href="{{ route('accounting.purchase-orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No purchase orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Contracts -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Contracts</h3>
                        <a href="{{ route('accounting.procurement-contracts.create', ['supplier_id' => $supplier->id]) }}" class="text-indigo-600 hover:text-indigo-900">Create New Contract</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contract Number</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($contracts as $contract)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                            <a href="{{ route('accounting.procurement-contracts.show', $contract) }}">{{ $contract->contract_number }}</a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $contract->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contract->start_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contract->end_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($contract->contract_value, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($contract->status == 'active') bg-green-100 text-green-800
                                                @elseif($contract->status == 'expired') bg-gray-100 text-gray-800
                                                @elseif($contract->status == 'terminated') bg-red-100 text-red-800
                                                @else bg-blue-100 text-blue-800
                                                @endif">
                                                {{ ucfirst($contract->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <a href="{{ route('accounting.procurement-contracts.show', $contract) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No contracts found</td>
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