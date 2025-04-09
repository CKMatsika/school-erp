<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Start Cashier Session') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.cashier.start-session') }}">
                        @csrf
                        
                        <div class="mb-6">
                            <label for="terminal_id" class="block text-sm font-medium text-gray-700">Select Terminal</label>
                            <select id="terminal_id" name="terminal_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select Terminal</option>
                                @foreach($terminals as $terminal)
                                    <option value="{{ $terminal->id }}">{{ $terminal->terminal_name }} ({{ $terminal->location }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-6">
                            <label for="opening_balance" class="block text-sm font-medium text-gray-700">Opening Cash Balance</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">KES</span>
                                </div>
                                <input type="number" name="opening_balance" id="opening_balance" step="0.01" min="0" required
                                    class="pl-12 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="0.00">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Enter the initial cash amount in your drawer</p>
                        </div>
                        
                        <div class="flex justify-between">
                            <a href="{{ route('accounting.cashier.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Start Session
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>