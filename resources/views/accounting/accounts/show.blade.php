<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Account Details') }}: {{ $account->name }} ({{ $account->account_code }})
            </h2>
            <div>
                <a href="{{ route('accounting.accounts.edit', $account) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    Edit Account
                </a>
                <a href="{{ route('accounting.accounts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to Accounts
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Account Overview -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Account Overview</h3>
                        <div class="bg-gray-50 p-4 rounded-lg grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Account Type</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $account->accountType->name }} ({{ $account->accountType->code }})</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Account Code</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $account->account_code }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status</p>
                                <p class="mt-1 text-sm">
                                    @if($account->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Current Balance</p>
                                <p class="mt-1 text-sm text-gray-900 font-semibold">{{ number_format($account->getCurrentBalance(), 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Opening Balance</p>
                                <p class="mt-1 text-sm text-gray-900">{{ number_format($account->opening_balance, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Balance Date</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $account->balance_date ? $account->balance_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Bank Account</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $account->is_bank_account ? 'Yes' : 'No' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">System Account</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $account->is_system ? 'Yes' : 'No' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Account Description -->
                    @if($account->description)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Description</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-900">{{ $account->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Transaction History -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Transaction History</h3>
                            <a href="{{ route('accounting.journals.create', ['account_id' => $account->id]) }}" class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Journal Entry
                            </a>
                        </div>
                        
                        <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Opening Balance Row -->
                                    <tr class="bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $account->balance_date ? $account->balance_date->format('M d, Y') : 'Initial' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Opening Balance</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if(in_array($account->accountType->code, ['ASSET', 'EXPENSE']) && $account->opening_balance > 0)
                                                {{ number_format($account->opening_balance, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if(!in_array($account->accountType->code, ['ASSET', 'EXPENSE']) && $account->opening_balance > 0)
                                                {{ number_format($account->opening_balance, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ number_format($account->opening_balance, 2) }}
                                        </td>
                                    </tr>
                                    
                                    @php
                                        $runningBalance = $account->opening_balance;
                                    @endphp
                                    
                                    @forelse($account->journalEntries as $entry)
                                        @php
                                            if(in_array($account->accountType->code, ['ASSET', 'EXPENSE'])) {
                                                $runningBalance += $entry->debit - $entry->credit;
                                            } else {
                                                $runningBalance += $entry->credit - $entry->debit;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $entry->journal->journal_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <a href="{{ route('accounting.journals.show', $entry->journal) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $entry->journal->reference }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $entry->journal->description }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ number_format($runningBalance, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                No transactions found for this account.
                                            </td>
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