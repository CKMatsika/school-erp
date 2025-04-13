<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('POS Session Details') }}
            </h2>
            <div>
                @if($session->status == 'active')
                    <a href="{{ route('accounting.pos.sessions.end', $session) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:border-yellow-800 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                        End Session
                    </a>
                @endif
                <a href="{{ route('accounting.pos.sessions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 ml-2">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h5 class="font-bold text-lg mb-2">Session Information</h5>
                            <table class="min-w-full border border-gray-200">
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $session->id }}</td>
                                </tr>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terminal</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $session->terminal->name ?? 'N/A' }}</td>
                                </tr>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $session->cashier->name ?? 'N/A' }}</td>
                                </tr>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">
                                        @if($session->status == 'active')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started At</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $session->started_at }}</td>
                                </tr>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started By</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $session->startedBy->name ?? 'N/A' }}</td>
                                </tr>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ended At</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $session->ended_at ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ended By</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $session->endedBy->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div>
                            <h5 class="font-bold text-lg mb-2">Financial Information</h5>
                            <table class="min-w-full border border-gray-200">
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opening Balance</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($session->opening_balance, 2) }}</td>
                                </tr>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction Total</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($transactions->sum('amount'), 2) }}</td>
                                </tr>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Closing Balance</th>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $session->expected_closing_balance ? number_format($session->expected_closing_balance, 2) : number_format($session->opening_balance + $transactions->sum('amount'), 2) }}</td>
                                </tr>
                                @if($session->status == 'closed')
                                    <tr class="border-b border-gray-200">
                                        <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Closing Balance</th>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($session->closing_balance, 2) }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cash Counted</th>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($session->cash_counted, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="px-4 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discrepancy</th>
                                        <td class="px-4 py-2 text-sm {{ $session->discrepancy < 0 ? 'text-red-500' : ($session->discrepancy > 0 ? 'text-green-500' : 'text-gray-500') }}">
                                            {{ number_format($session->discrepancy, 2) }}
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($session->notes)
                        <div class="mt-6">
                            <h5 class="font-bold text-lg mb-2">Notes</h5>
                            <div class="p-4 bg-gray-50 rounded">
                                {{ $session->notes }}
                            </div>
                        </div>
                    @endif

                    <div class="mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h5 class="font-bold text-lg">Transactions</h5>
                            
                            @if($session->status == 'active')
                                <a href="{{ route('accounting.pos.transactions.create', ['session_id' => $session->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-800 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    New Transaction
                                </a>
                            @endif
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($transactions as $transaction)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->created_at }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($transaction->type) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $transaction->amount < 0 ? 'text-red-500' : 'text-green-500' }}">
                                                {{ number_format($transaction->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($transaction->payment_method) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('accounting.pos.transactions.show', $transaction) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No transactions found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>