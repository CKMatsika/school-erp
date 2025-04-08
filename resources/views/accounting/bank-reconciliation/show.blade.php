<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Bank Reconciliation Details') }}
            </h2>
            <div>
                @if($bankReconciliation->status != 'completed')
                    <a href="{{ route('accounting.bank-reconciliation.match', $bankReconciliation->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Continue Matching
                    </a>
                @endif
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

            <!-- Reconciliation Summary Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Reconciliation Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Bank Account</p>
                            <p class="text-md font-semibold">{{ $bankReconciliation->bankAccount->account_name ?? 'Unknown Account' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Statement Date</p>
                            <p class="text-md font-semibold">{{ $bankReconciliation->statement_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="text-md">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($bankReconciliation->status == 'completed')
                                        bg-green-100 text-green-800
                                    @elseif($bankReconciliation->status == 'in_progress')
                                        bg-yellow-100 text-yellow-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $bankReconciliation->status_label }}
                                </span>
                            </p>
                        </div>
                        @if($bankReconciliation->completed_at)
                            <div>
                                <p class="text-sm font-medium text-gray-500">Completed On</p>
                                <p class="text-md font-semibold">{{ $bankReconciliation->completed_at->format('M d, Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-800">Statement Balance</h4>
                            <p class="text-xl font-bold text-blue-600">{{ number_format($bankReconciliation->statement_balance, 2) }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <h4 class="text-sm font-medium text-green-800">System Balance</h4>
                            <p class="text-xl font-bold text-green-600">{{ number_format($bankReconciliation->system_balance, 2) }}</p>
                        </div>
                        <div class="bg-{{ abs($bankReconciliation->difference) > 0.01 ? 'red' : 'green' }}-50 p-4 rounded-lg border border-{{ abs($bankReconciliation->difference) > 0.01 ? 'red' : 'green' }}-200">
                            <h4 class="text-sm font-medium text-{{ abs($bankReconciliation->difference) > 0.01 ? 'red' : 'green' }}-800">Difference</h4>
                            <p class="text-xl font-bold text-{{ abs($bankReconciliation->difference) > 0.01 ? 'red' : 'green' }}-600">
                                {{ number_format($bankReconciliation->difference, 2) }}
                            </p>
                        </div>
                    </div>
                    
                    @if($bankReconciliation->status != 'completed')
                        <div class="mt-6">
                            <form method="POST" action="{{ route('accounting.bank-reconciliation.import-statement', $bankReconciliation->id) }}" enctype="multipart/form-data" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                @csrf
                                <h4 class="text-md font-medium text-gray-800 mb-2">Import Bank Statement</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="col-span-2">
                                        <input type="file" name="statement_file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                                    </div>
                                    <div>
                                        <select name="date_format" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-full">
                                            <option value="d/m/Y">DD/MM/YYYY</option>
                                            <option value="m/d/Y">MM/DD/YYYY</option>
                                            <option value="Y-m-d">YYYY-MM-DD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Import Statement
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                    
                    @if($bankReconciliation->status == 'in_progress' && abs($bankReconciliation->difference) <= 0.01)
                        <div class="mt-6">
                            <form method="POST" action="{{ route('accounting.bank-reconciliation.confirm', $bankReconciliation->id) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Complete Reconciliation
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Matched Items -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Matched Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($matchedItems ?? [] as $item)
                                    <tr class="bg-green-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->cashbookEntry->transaction_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->cashbookEntry->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->cashbookEntry->reference_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $item->cashbookEntry->is_debit ? 'text-red-600' : 'text-green-600' }} text-right">
                                            {{ $item->cashbookEntry->is_debit ? '-' : '+' }}{{ number_format($item->cashbookEntry->amount, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->statementEntry->transaction_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->statementEntry->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->statementEntry->reference }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $item->statementEntry->is_debit ? 'text-red-600' : 'text-green-600' }} text-right">
                                            {{ $item->statementEntry->is_debit ? '-' : '+' }}{{ number_format($item->statementEntry->amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No matched items</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Unmatched Items -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Unmatched System Entries -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Unmatched System Entries</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($unmatchedSystemEntries ?? [] as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->cashbookEntry->transaction_date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->cashbookEntry->description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $item->cashbookEntry->is_debit ? 'text-red-600' : 'text-green-600' }} text-right">
                                                {{ $item->cashbookEntry->is_debit ? '-' : '+' }}{{ number_format($item->cashbookEntry->amount, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No unmatched system entries</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Unmatched Statement Entries -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Unmatched Statement Entries</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($unmatchedStatementEntries ?? [] as $entry)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entry->transaction_date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $entry->is_debit ? 'text-red-600' : 'text-green-600' }} text-right">
                                                {{ $entry->is_debit ? '-' : '+' }}{{ number_format($entry->amount, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No unmatched statement entries</td>
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
            </div>
        </div>
    </div>
</x-app-layout>