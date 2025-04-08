<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Bank Transfer Details') }}
            </h2>
            <div>
                <a href="{{ route('accounting.bank-transfers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back to Transfers
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

            <!-- Transfer Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Transfer Information</h3>
                        <div>
                            @if($bankTransfer->transfer_date->isToday())
                                <a href="{{ route('accounting.bank-transfers.edit', $bankTransfer) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('accounting.bank-transfers.destroy', $bankTransfer) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to void this transfer? This will reverse all related transactions.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Void
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Transfer Date</p>
                            <p class="text-md font-semibold">{{ $bankTransfer->transfer_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Reference</p>
                            <p class="text-md font-semibold">{{ $bankTransfer->reference }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="text-md">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $bankTransfer->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $bankTransfer->status_label }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">From Account</p>
                            <p class="text-md font-semibold">{{ $bankTransfer->fromAccount->account_name }} - {{ $bankTransfer->fromAccount->bank_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">To Account</p>
                            <p class="text-md font-semibold">{{ $bankTransfer->toAccount->account_name }} - {{ $bankTransfer->toAccount->bank_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Amount</p>
                            <p class="text-md font-semibold text-green-600">{{ number_format($bankTransfer->amount, 2) }}</p>
                        </div>
                        <div class="col-span-1 md:col-span-2 lg:col-span-3">
                            <p class="text-sm font-medium text-gray-500">Notes</p>
                            <p class="text-md">{{ $bankTransfer->notes ?? 'No notes' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Created By</p>
                            <p class="text-md">{{ $bankTransfer->createdBy->name ?? 'System' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Created On</p>
                            <p class="text-md">{{ $bankTransfer->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Associated Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Associated Transactions</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Reconciled</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($bankTransfer->cashbookEntries ?? [] as $entry)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entry->transaction_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->bankAccount->account_name ?? 'Unknown' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entry->reference_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $entry->is_debit ? 'text-red-600' : 'text-green-600' }} text-right">
                                            {{ $entry->is_debit ? '-' : '+' }}{{ number_format($entry->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $entry->is_reconciled ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $entry->is_reconciled ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No transactions found</td>
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