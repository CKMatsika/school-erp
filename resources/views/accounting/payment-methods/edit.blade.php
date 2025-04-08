<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Payment Method') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('components.flash-messages')
                    @include('components.form-errors')

                    <form method="POST" action="{{ route('accounting.payment-methods.update', $paymentMethod) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Method Name')" />
                                <x-text-input id="name" type="text" name="name" :value="old('name', $paymentMethod->name)" class="mt-1 block w-full" required autofocus />
                                <p class="mt-1 text-sm text-gray-500">{{ __('E.g., Cash, Bank Transfer, M-Pesa, Credit Card') }}</p>
                            </div>

                            <!-- Code -->
                            <div>
                                <x-input-label for="code" :value="__('Method Code')" />
                                <x-text-input id="code" type="text" name="code" :value="old('code', $paymentMethod->code)" class="mt-1 block w-full" required />
                                <p class="mt-1 text-sm text-gray-500">{{ __('E.g., CASH, BANK, MPESA, CC') }}</p>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $paymentMethod->description) }}</textarea>
                            </div>

                            <!-- Account ID -->
                            <div>
                                <x-input-label for="account_id" :value="__('Associated Account')" />
                                <select id="account_id" name="account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">{{ __('-- Select Account --') }}</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id', $paymentMethod->account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Link to a bank or cash account for automatic reconciliation') }}</p>
                            </div>

                            <!-- Display Order -->
                            <div>
                                <x-input-label for="display_order" :value="__('Display Order')" />
                                <x-text-input id="display_order" type="number" name="display_order" :value="old('display_order', $paymentMethod->display_order)" min="1" class="mt-1 block w-full" />
                                <p class="mt-1 text-sm text-gray-500">{{ __('Order in which the method appears in lists') }}</p>
                            </div>

                            <!-- Requires Reference -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="requires_reference" name="requires_reference" type="checkbox" value="1" {{ old('requires_reference', $paymentMethod->requires_reference) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="requires_reference" class="font-medium text-gray-700">{{ __('Require Reference') }}</label>
                                    <p class="text-gray-500">{{ __('Does this payment method require a reference number?') }}</p>
                                </div>
                            </div>

                            <!-- Is Active -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $paymentMethod->is_active) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-gray-700">{{ __('Active') }}</label>
                                    <p class="text-gray-500">{{ __('Is this payment method currently active and available for use?') }}</p>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div class="md:col-span-2">
                                <x-input-label for="instructions" :value="__('Payment Instructions')" />
                                <textarea id="instructions" name="instructions" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('instructions', $paymentMethod->instructions) }}</textarea>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Instructions for users on how to use this payment method (optional)') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('accounting.payment-methods.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Payment Method') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>