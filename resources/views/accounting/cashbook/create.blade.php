<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Cashbook Entry') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.cashbook.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="bank_account_id" :value="__('Bank Account')" />
                                <select id="bank_account_id" name="bank_account_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">Select Bank Account</option>
                                    @foreach($bankAccounts ?? [] as $account)
                                        <option value="{{ $account->id }}" {{ old('bank_account_id', request('account')) == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} - {{ $account->bank_name }} ({{ number_format($account->current_balance, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('bank_account_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="transaction_date" :value="__('Transaction Date')" />
                                <x-text-input id="transaction_date" class="block mt-1 w-full" type="date" name="transaction_date" :value="old('transaction_date', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="type" :value="__('Transaction Type')" />
                                <select id="type" name="type" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="deposit" {{ old('type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                                    <option value="withdrawal" {{ old('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                                    <option value="payment" {{ old('type') == 'payment' ? 'selected' : '' }}>Payment</option>
                                    <option value="receipt" {{ old('type') == 'receipt' ? 'selected' : '' }}>Receipt</option>
                                    <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="amount" :value="__('Amount')" />
                                <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" :value="old('amount', '0.00')" step="0.01" min="0.01" required />
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="description" :value="__('Description')" />
                                <x-text-input id="description" class="block mt-1 w-full" type="text" name="description" :value="old('description')" required />
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="reference_number" :value="__('Reference Number')" />
                                <x-text-input id="reference_number" class="block mt-1 w-full" type="text" name="reference_number" :value="old('reference_number')" required />
                                <x-input-error :messages="$errors->get('reference_number')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('accounting.cashbook.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Add Transaction') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-set transaction direction based on type
            const typeSelect = document.getElementById('type');
            typeSelect.addEventListener('change', function() {
                const debitTypes = ['withdrawal', 'payment'];
                const isDebit = debitTypes.includes(this.value);
                document.getElementById('is_debit').checked = isDebit;
            });
        });
    </script>
    @endpush
</x-app-layout>