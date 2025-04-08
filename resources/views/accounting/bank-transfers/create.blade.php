<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Bank Transfer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.bank-transfers.store') }}">
                        @csrf

                        <div class="mb-6">
                            <x-input-label for="transfer_date" :value="__('Transfer Date')" />
                            <x-text-input id="transfer_date" class="block mt-1 w-full" type="date" name="transfer_date" :value="old('transfer_date', date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('transfer_date')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="from_account_id" :value="__('From Account')" />
                                <select id="from_account_id" name="from_account_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">Select Source Account</option>
                                    @foreach($bankAccounts ?? [] as $account)
                                        <option value="{{ $account->id }}" {{ old('from_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} - {{ $account->bank_name }} ({{ number_format($account->current_balance, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('from_account_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="to_account_id" :value="__('To Account')" />
                                <select id="to_account_id" name="to_account_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">Select Destination Account</option>
                                    @foreach($bankAccounts ?? [] as $account)
                                        <option value="{{ $account->id }}" {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} - {{ $account->bank_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('to_account_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="amount" :value="__('Transfer Amount')" />
                                <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" :value="old('amount', '0.00')" step="0.01" min="0.01" required />
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="reference" :value="__('Reference')" />
                                <x-text-input id="reference" class="block mt-1 w-full" type="text" name="reference" :value="old('reference')" required />
                                <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('accounting.bank-transfers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Create Transfer') }}
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
            // Prevent selecting the same account for source and destination
            const fromAccount = document.getElementById('from_account_id');
            const toAccount = document.getElementById('to_account_id');
            
            fromAccount.addEventListener('change', function() {
                // Re-enable all options in to_account first
                for (let i = 0; i < toAccount.options.length; i++) {
                    toAccount.options[i].disabled = false;
                }
                
                // Disable the selected option in from_account from being selected in to_account
                if (this.value) {
                    const selectedOption = toAccount.querySelector(`option[value="${this.value}"]`);
                    if (selectedOption) {
                        selectedOption.disabled = true;
                        
                        // If the disabled option was previously selected, reset to_account selection
                        if (toAccount.value === this.value) {
                            toAccount.value = "";
                        }
                    }
                }
            });
            
            toAccount.addEventListener('change', function() {
                // Re-enable all options in from_account first
                for (let i = 0; i < fromAccount.options.length; i++) {
                    fromAccount.options[i].disabled = false;
                }
                
                // Disable the selected option in to_account from being selected in from_account
                if (this.value) {
                    const selectedOption = fromAccount.querySelector(`option[value="${this.value}"]`);
                    if (selectedOption) {
                        selectedOption.disabled = true;
                        
                        // If the disabled option was previously selected, reset from_account selection
                        if (fromAccount.value === this.value) {
                            fromAccount.value = "";
                        }
                    }
                }
            });
            
            // Initial setup
            if (fromAccount.value) {
                const selectedOption = toAccount.querySelector(`option[value="${fromAccount.value}"]`);
                if (selectedOption) {
                    selectedOption.disabled = true;
                }
            }
            
            if (toAccount.value) {
                const selectedOption = fromAccount.querySelector(`option[value="${toAccount.value}"]`);
                if (selectedOption) {
                    selectedOption.disabled = true;
                }
            }
        });
    </script>
    @endpush
</x-app-layout>