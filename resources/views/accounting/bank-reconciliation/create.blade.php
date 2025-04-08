<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Start Bank Reconciliation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.bank-reconciliation.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="bank_account_id" :value="__('Bank Account')" />
                                <select id="bank_account_id" name="bank_account_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">Select Bank Account</option>
                                    @foreach($bankAccounts ?? [] as $account)
                                        <option value="{{ $account->id }}" {{ old('bank_account_id', request('account')) == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} - {{ $account->bank_name }} ({{ $account->account_number }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('bank_account_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="statement_date" :value="__('Statement Date')" />
                                <x-text-input id="statement_date" class="block mt-1 w-full" type="date" name="statement_date" :value="old('statement_date', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('statement_date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="statement_balance" :value="__('Ending Balance (as shown on statement)')" />
                            <x-text-input id="statement_balance" class="block mt-1 w-full" type="number" name="statement_balance" :value="old('statement_balance', '0.00')" step="0.01" required />
                            <x-input-error :messages="$errors->get('statement_balance')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('accounting.bank-reconciliation.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Start Reconciliation') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>