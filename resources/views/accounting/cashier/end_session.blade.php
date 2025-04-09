<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('End Cashier Session') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Session Summary</h3>
                    
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Session Started</p>
                            <p class="font-medium">{{ $session->opening_time->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Duration</p>
                            <p class="font-medium">{{ $session->getDurationInMinutes() }} minutes</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Terminal</p>
                            <p class="font-medium">{{ $session->terminal->terminal_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Opening Balance</p>
                            <p class="font-medium">KES {{ number_format($session->opening_balance, 2) }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <h4 class="font-medium text-gray-900 mb-2">Transaction Summary</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Total Transactions</p>
                                    <p class="font-medium">{{ $session->transactions->count() }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Total Amount</p>
                                    <p class="font-medium text-green-600">KES {{ number_format($session->transactions->sum('amount'), 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Cash Transactions</p>
                                    <p class="font-medium">{{ $session->transactions->where('payment_method', 'cash')->count() }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Card Transactions</p>
                                    <p class="font-medium">{{ $session->transactions->where('payment_method', 'card')->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('accounting.cashier.close-session', $session) }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="closing_balance" class="block text-sm font-medium text-gray-700">Closing Balance (Cash in Drawer)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">KES</span>
                                </div>
                                <input type="number" name="closing_balance" id="closing_balance" step="0.01" required
                                    class="pl-12 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    value="{{ old('closing_balance', $session->opening_balance + $session->transactions->where('payment_method', 'cash')->sum('amount')) }}">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Enter the actual amount of cash in your drawer</p>
                        </div>
                        
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Session Notes</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Any discrepancies or notes about this session...">{{ old('notes') }}</textarea>
                        </div>
                        
                        <div class="flex justify-between">
                            <a href="{{ route('accounting.cashier.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Close Session & Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>