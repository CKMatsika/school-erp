<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Account Type') }}: {{ $accountType->name }}
            </h2>
            <a href="{{ route('accounting.account-types.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to Account Types
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if($accountType->is_system)
                        <div class="mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                            <p class="font-bold">System Account Type</p>
                            <p>This is a core system account type and cannot be edited or deleted.</p>
                        </div>
                    @endif

                    <form action="{{ route('accounting.account-types.update', $accountType->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Name --}}
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 required">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $accountType->name) }}" required maxlength="255"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('name') border-red-500 @enderror"
                                   {{ $accountType->is_system ? 'disabled' : '' }}>
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Code --}}
                        <div class="mb-4">
                            <label for="code" class="block text-sm font-medium text-gray-700 required">Code</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $accountType->code) }}" required maxlength="50" pattern="[A-Z_]+" title="Use only uppercase letters and underscores (e.g., CURRENT_ASSET)"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md uppercase @error('code') border-red-500 @enderror"
                                   {{ $accountType->is_system ? 'disabled' : '' }}>
                            <p class="mt-1 text-xs text-gray-500">Unique code (e.g., ASSET, LIABILITY, REVENUE). Use uppercase letters and underscores only.</p>
                            @error('code')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                         {{-- Normal Balance --}}
                        <div class="mb-4">
                            <label for="normal_balance" class="block text-sm font-medium text-gray-700 required">Normal Balance</label>
                            <select id="normal_balance" name="normal_balance" required
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('normal_balance') border-red-500 @enderror"
                                    {{ $accountType->is_system ? 'disabled' : '' }}>
                                <option value="">-- Select Balance --</option>
                                <option value="debit" {{ old('normal_balance', $accountType->normal_balance) == 'debit' ? 'selected' : '' }}>Debit</option>
                                <option value="credit" {{ old('normal_balance', $accountType->normal_balance) == 'credit' ? 'selected' : '' }}>Credit</option>
                            </select>
                             @error('normal_balance')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('description') border-red-500 @enderror"
                                      {{ $accountType->is_system ? 'disabled' : '' }}>{{ old('description', $accountType->description) }}</textarea>
                             @error('description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Is Active --}}
                        <div class="mb-4">
                            <label for="is_active" class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $accountType->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                       {{ $accountType->is_system ? 'disabled' : '' }}>
                                <span class="ml-2 text-sm text-gray-600">Is Active</span>
                            </label>
                             @error('is_active')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('accounting.account-types.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                             @if(!$accountType->is_system)
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Update Account Type
                                </button>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>