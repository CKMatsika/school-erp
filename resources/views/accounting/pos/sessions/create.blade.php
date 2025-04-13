<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Start POS Session') }}
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

                    <form method="POST" action="{{ route('accounting.pos.sessions.start') }}">
                        @csrf

                        <div class="mb-4">
                            <x-label for="pos_terminal_id" :value="__('POS Terminal')" />
                            <select id="pos_terminal_id" name="pos_terminal_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">Select Terminal</option>
                                @foreach($terminals as $terminal)
                                    <option value="{{ $terminal->id }}">{{ $terminal->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('pos_terminal_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-label for="cashier_id" :value="__('Cashier')" />
                            <select id="cashier_id" name="cashier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">Select Cashier</option>
                                @foreach($cashiers as $cashier)
                                    <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('cashier_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-label for="opening_balance" :value="__('Opening Balance')" />
                            <x-input id="opening_balance" class="block mt-1 w-full" type="number" name="opening_balance" step="0.01" :value="old('opening_balance', 0)" required />
                            <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('accounting.pos.sessions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                Cancel
                            </a>
                            <x-button>
                                {{ __('Start Session') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>