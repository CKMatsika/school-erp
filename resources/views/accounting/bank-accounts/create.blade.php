<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Bank Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.bank-accounts.store') }}">
                        @csrf

                        <!-- Account Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="account_name" :value="__('Account Name')" />
                                <x-text-input id="account_name" class="block mt-1 w-full" type="text" name="account_name" :value="old('account_name')" required />
                                <x-input-error :messages="$errors->get('account_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="account_type" :value="__('Account Type')" />
                                <select id="account_type" name="account_type" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="checking" {{ old('account_type') == 'checking' ? 'selected' : '' }}>Checking</option>
                                    <option value="savings" {{ old('account_type') == 'savings' ? 'selected' : '' }}>Savings</option>
                                    <option value="credit" {{ old('account_type') == 'credit' ? 'selected' : '' }}>Credit</option>
                                    <option value="cash" {{ old('account_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                                <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="bank_name" :value="__('Bank Name')" />
                                <x-text-input id="bank_name" class="block mt-1 w-full" type="text" name="bank_name" :value="old('bank_name')" required />
                                <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="account_number" :value="__('Account Number')" />
                                <x-text-input id="account_number" class="block mt-1 w-full" type="text" name="account_number" :value="old('account_number')" required />
                                <x-input-error :messages="$errors->get('account_number')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <x-input-label for="branch_code" :value="__('Branch Code')" />
                                <x-text-input id="branch_code" class="block mt-1 w-full" type="text" name="branch_code" :value="old('branch_code')" />
                                <x-input-error :messages="$errors->get('branch_code')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="swift_code" :value="__('SWIFT/BIC Code')" />
                                <x-text-input id="swift_code" class="block mt-1 w-full" type="text" name="swift_code" :value="old('swift_code')" />
                                <x-input-error :messages="$errors->get('swift_code')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="currency" :value="__('Currency')" />
                                <x-text-input id="currency" class="block mt-1 w-full" type="text" name="currency" :value="old('currency', 'USD')" required maxlength="3" />
                                <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500">3-letter currency code (e.g., USD, EUR, GBP)</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="opening_balance" :value="__('Opening Balance')" />
                                <x-text-input id="opening_balance" class="block mt-1 w-full" type="number" name="opening_balance" :value="old('opening_balance', '0.00')" step="0.01" min="0" required />
                                <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="chart_of_account_id" :value="__('Chart of Account')" />
                                <select id="chart_of_account_id" name="chart_of_account_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">Select Chart of Account</option>
                                    @foreach($chartOfAccounts ?? [] as $account)
                                        <option value="{{ $account->id }}" {{ old('chart_of_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('chart_of_account_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <label for="is_active" class="inline-flex items-center">
                                <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('accounting.bank-accounts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Add Bank Account') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>