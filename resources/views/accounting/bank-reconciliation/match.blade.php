<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Match Bank Reconciliation Items') }}
            </h2>
            <div>
                <a href="{{ route('accounting.bank-reconciliation.show', $reconciliation) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back to Summary
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

            <!-- Reconciliation Summary Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Reconciliation Status</h3>
                        @if(abs($reconciliation->difference) <= 0.01)
                            <form method="POST" action="{{ route('accounting.bank-reconciliation.confirm', $reconciliation->id) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Complete Reconciliation
                                </button>
                            </form>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-800">Statement Balance</h4>
                            <p class="text-xl font-bold text-blue-600">{{ number_format($reconciliation->statement_balance, 2) }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <h4 class="text-sm font-medium text-green-800">System Balance</h4>
                            <p class="text-xl font-bold text-green-600">{{ number_format($reconciliation->system_balance, 2) }}</p>
                        </div>
                        <div class="bg-{{ abs($reconciliation->difference) > 0.01 ? 'red' : 'green' }}-50 p-4 rounded-lg border border-{{ abs($reconciliation->difference) > 0.01 ? 'red' : 'green' }}-200">
                            <h4 class="text-sm font-medium text-{{ abs($reconciliation->difference) > 0.01 ? 'red' : 'green' }}-800">Difference</h4>
                            <p class="text-xl font-bold text-{{ abs($reconciliation->difference) > 0.01 ? 'red' : 'green' }}-600" id="difference-amount">
                                {{ number_format($reconciliation->difference, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matching Interface -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- System Entries -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">System Entries</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="system-entries">
                                    @forelse($unmatchedSystemEntries ?? [] as $item)
                                        <tr data-id="{{ $item->id }}" data-amount="{{ $item->cashbookEntry->signed_amount }}" class="hover:bg-gray-50 cursor-pointer system-entry">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->cashbookEntry->transaction_date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->cashbookEntry->description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $item->cashbookEntry->is_debit ? 'text-red-600' : 'text-green-600' }} text-right">
                                                {{ $item->cashbookEntry->is_debit ? '-' : '+' }}{{ number_format($item->cashbookEntry->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <button type="button" class="select-system-entry text-blue-600 hover:text-blue-900">Select</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No unmatched system entries</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Statement Entries -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Statement Entries</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="statement-entries">
                                    @forelse($unmatchedStatementEntries ?? [] as $entry)
                                        <tr data-id="{{ $entry->id }}" data-amount="{{ $entry->signed_amount }}" class="hover:bg-gray-50 cursor-pointer statement-entry">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entry->transaction_date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $entry->is_debit ? 'text-red-600' : 'text-green-600' }} text-right">
                                                {{ $entry->is_debit ? '-' : '+' }}{{ number_format($entry->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <button type="button" class="select-statement-entry text-blue-600 hover:text-blue-900">Select</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No unmatched statement entries</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Match Actions Panel -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 bg-white border-b border-gray-200" id="match-panel">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Match Items</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div class="p-4 bg-blue-50 rounded-lg border border-blue-200" id="selected-system-entry">
                            <h4 class="text-sm font-medium text-blue-800 mb-2">Selected System Entry</h4>
                            <p class="text-gray-500 text-sm">No item selected</p>
                        </div>
                        
                        <div class="p-4 bg-green-50 rounded-lg border border-green-200" id="selected-statement-entry">
                            <h4 class="text-sm font-medium text-green-800 mb-2">Selected Statement Entry</h4>
                            <p class="text-gray-500 text-sm">No item selected</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-center">
                        <button type="button" id="match-items-btn" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 opacity-50 cursor-not-allowed" disabled>
                            Match Items
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedSystemEntry = null;
            let selectedStatementEntry = null;
            
            // System entry selection
            document.querySelectorAll('.select-system-entry').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const id = row.dataset.id;
                    const amount = row.dataset.amount;
                    const date = row.cells[0].textContent;
                    const description = row.cells[1].textContent;
                    const amountText = row.cells[2].textContent;
                    
                    // Clear previous selection
                    document.querySelectorAll('.system-entry').forEach(r => r.classList.remove('bg-blue-50'));
                    
                    // Highlight new selection
                    row.classList.add('bg-blue-50');
                    
                    // Update selected entry display
                    document.getElementById('selected-system-entry').innerHTML = `
                        <h4 class="text-sm font-medium text-blue-800 mb-2">Selected System Entry</h4>
                        <div class="text-sm">
                            <p><span class="font-medium">Date:</span> ${date}</p>
                            <p><span class="font-medium">Description:</span> ${description}</p>
                            <p><span class="font-medium">Amount:</span> ${amountText}</p>
                        </div>
                        <input type="hidden" id="system-entry-id" value="${id}">
                    `;
                    
                    selectedSystemEntry = { id, amount: parseFloat(amount) };
                    checkMatchButtonState();
                });
            });
            
            // Statement entry selection
            document.querySelectorAll('.select-statement-entry').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const id = row.dataset.id;
                    const amount = row.dataset.amount;
                    const date = row.cells[0].textContent;
                    const description = row.cells[1].textContent;
                    const amountText = row.cells[2].textContent;
                    
                    // Clear previous selection
                    document.querySelectorAll('.statement-entry').forEach(r => r.classList.remove('bg-green-50'));
                    
                    // Highlight new selection
                    row.classList.add('bg-green-50');
                    
                    // Update selected entry display
                    document.getElementById('selected-statement-entry').innerHTML = `
                        <h4 class="text-sm font-medium text-green-800 mb-2">Selected Statement Entry</h4>
                        <div class="text-sm">
                            <p><span class="font-medium">Date:</span> ${date}</p>
                            <p><span class="font-medium">Description:</span> ${description}</p>
                            <p><span class="font-medium">Amount:</span> ${amountText}</p>
                        </div>
                        <input type="hidden" id="statement-entry-id" value="${id}">
                    `;
                    
                    selectedStatementEntry = { id, amount: parseFloat(amount) };
                    checkMatchButtonState();
                });
            });
            
            // Check if match button should be enabled
            function checkMatchButtonState() {
                const matchBtn = document.getElementById('match-items-btn');
                
                if (selectedSystemEntry && selectedStatementEntry) {
                    matchBtn.disabled = false;
                    matchBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    matchBtn.disabled = true;
                    matchBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
            
            // Match button click handler
            document.getElementById('match-items-btn').addEventListener('click', function() {
                if (!selectedSystemEntry || !selectedStatementEntry) return;
                
                // Send match request via AJAX
                const data = {
                    system_entry_id: selectedSystemEntry.id,
                    statement_entry_id: selectedStatementEntry.id,
                    action: 'match',
                    _token: '{{ csrf_token() }}'
                };
                
                fetch('{{ route("accounting.bank-reconciliation.updateMatch", $reconciliation->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the difference amount
                        document.getElementById('difference-amount').textContent = data.difference;
                        
                        // Remove the matched rows
                        document.querySelector(`.system-entry[data-id="${selectedSystemEntry.id}"]`).remove();
                        document.querySelector(`.statement-entry[data-id="${selectedStatementEntry.id}"]`).remove();
                        
                        // Clear selections
                        document.getElementById('selected-system-entry').innerHTML = `
                            <h4 class="text-sm font-medium text-blue-800 mb-2">Selected System Entry</h4>
                            <p class="text-gray-500 text-sm">No item selected</p>
                        `;
                        
                        document.getElementById('selected-statement-entry').innerHTML = `
                            <h4 class="text-sm font-medium text-green-800 mb-2">Selected Statement Entry</h4>
                            <p class="text-gray-500 text-sm">No item selected</p>
                        `;
                        
                        selectedSystemEntry = null;
                        selectedStatementEntry = null;
                        
                        checkMatchButtonState();
                        
                        // Show success message
                        alert('Items matched successfully!');
                        
                        // Refresh if the difference is zero
                        if (parseFloat(data.difference) === 0) {
                            window.location.reload();
                        }
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to match items. Please try again.');
                });
            });
        });
    </script>
    @endpush
</x-app-layout>