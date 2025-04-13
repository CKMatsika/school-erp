<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('End POS Session') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded relative">
                        <h5 class="font-bold text-lg mb-2">Session Summary</h5>
                        <p class="mb-1"><strong>Terminal:</strong> {{ $session->terminal->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Cashier:</strong> {{ $session->cashier->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Opening Balance:</strong> {{ number_format($session->opening_balance, 2) }}</p>
                        <p class="mb-1"><strong>Transaction Total:</strong> {{ number_format($session->transactions->sum('amount'), 2) }}</p>
                        <p class="mb-1"><strong>Expected Closing Balance:</strong> {{ number_format($session->opening_balance + $session->transactions->sum('amount'), 2) }}</p>
                    </div>

                    <form method="POST" action="{{ route('accounting.pos.sessions.close', $session) }}">
                        @csrf

                        <div class="mb-4">
                            <x-label for="cash_counted" :value="__('Cash Counted')" />
                            <x-input id="cash_counted" class="block mt-1 w-full" type="number" step="0.01" name="cash_counted" :value="old('cash_counted', $session->opening_balance + $session->transactions->sum('amount'))" required />
                            <x-input-error :messages="$errors->get('cash_counted')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-label for="closing_balance" :value="__('Closing Balance')" />
                            <x-input id="closing_balance" class="block mt-1 w-full" type="number" step="0.01" name="closing_balance" :value="old('closing_balance', $session->opening_balance + $session->transactions->sum('amount'))" required />
                            <x-input-error :messages="$errors->get('closing_balance')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('accounting.pos.sessions.show', $session) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                Cancel
                            </a>
                            <x-button onclick="return confirm('Are you sure you want to close this session? This cannot be undone.')">
                                {{ __('Close Session') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>