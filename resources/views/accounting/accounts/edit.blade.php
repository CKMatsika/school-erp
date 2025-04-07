<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Account') }}: {{ $account->name }}
            </h2>
            <div>
                <a href="{{ route('accounting.accounts.show', $account) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    View Account
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
                    <form method="POST" action="{{ route('accounting.accounts.update', $account) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Type -->
                            <div>
                                <label for="account_type_id" class="block text-sm font-medium text-gray-700">Account Type</label>
                                <select id="account_type_id" name="account_type_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required {{ $account->is_system ? 'disabled' : '' }}>
                                    <option value="">Select Account Type</option>
                                    @foreach($accountTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('account_type_id', $account->account_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }} ({{ $type->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($account->is_system)
                                    <input type="hidden" name="account_type_id" value="{{ $account->account_type_id }}">
                                @endif
                                @error('account_type_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account Code -->
                            <div>
                                <label for="account_code" class="block text-sm font-medium text-gray-700">Account Code</label>
                                <input type="text" name="account_code" id="account_code" value="{{ old('account_code', $account->account_code) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required {{ $account->is_system ? 'readonly' : '' }}>
                                @error('account_code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Account Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $account->name) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('description', $account->description) }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Opening Balance -->
                            <div>
                                <label for="opening_balance" class="block text-sm font-medium text-gray-700">Opening Balance</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="opening_balance" id="opening_balance" value="{{ old('opening_balance', $account->opening_balance) }}" step="0.01" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" {{ $account->journalEntries()->exists() ? 'readonly' : '' }}>
                                </div>
                                @if($account->journalEntries()->exists())
                                    <p class="text-xs text-gray-500 mt-1">Cannot modify opening balance because this account has journal entries.</p>
                                @endif
                                @error('opening_balance')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Balance Date -->
                            <div>
                                <label for="balance_date" class="block text-sm font-medium text-gray-700">Balance Date</label>
                                <input type="date" name="balance_date" id="balance_date" value="{{ old('balance_date', $account->balance_date ? $account->balance_date->format('Y-m-d') : date('Y-m-d')) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" {{ $account->journalEntries()->exists() ? 'readonly' : '' }}>
                                @error('balance_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_bank_account" name="is_bank_account" type="checkbox" {{ old('is_bank_account', $account->is_bank_account) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_bank_account" class="font-medium text-gray-700">Is Bank Account</label>
                                    <p class="text-gray-500">Check if this account represents a bank account.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_active" name="is_active" type="checkbox" {{ old('is_active', $account->is_active) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ $account->is_system ? 'disabled' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-gray-700">Active</label>
                                    <p class="text-gray-500">Inactive accounts are hidden from dropdowns but preserved for reporting.</p>
                                </div>
                            </div>
                            @if($account->is_system)
                                <input type="hidden" name="is_active" value="1">
                            @endif
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Update Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>