<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.accounts.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Type -->
                            <div>
                                <label for="account_type_id" class="block text-sm font-medium text-gray-700">Account Type</label>
                                <select id="account_type_id" name="account_type_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach($accountTypes as $accountType)
                                        <option value="{{ $accountType->id }}">{{ $accountType->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_type_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account Code -->
                            <div>
                                <label for="account_code" class="block text-sm font-medium text-gray-700">Account Code</label>
                                <input type="text" name="account_code" id="account_code" value="{{ old('account_code') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('account_code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Account Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <input type="text" name="description" id="description" value="{{ old('description') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Opening Balance -->
                            <div>
                                <label for="opening_balance" class="block text-sm font-medium text-gray-700">Opening Balance</label>
                                <input type="number" step="0.01" name="opening_balance" id="opening_balance" value="{{ old('opening_balance', '0.00') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('opening_balance')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Balance Date -->
                            <div>
                                <label for="balance_date" class="block text-sm font-medium text-gray-700">Balance Date</label>
                                <input type="date" name="balance_date" id="balance_date" value="{{ old('balance_date', date('Y-m-d')) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('balance_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Is Bank Account -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_bank_account" name="is_bank_account" type="checkbox" value="1" {{ old('is_bank_account') ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_bank_account" class="font-medium text-gray-700">Is Bank Account</label>
                                    <p class="text-gray-500">Check this if the account is a bank account</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Create Account
                            </button>
                            <a href="{{ route('accounting.accounts.index') }}" class="ml-2 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>