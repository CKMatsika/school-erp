<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cashier Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ Auth::user()->name }}</h3>

                    @if($needsSchoolAssignment)
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">School Assignment Required!</strong>
                            <span class="block sm:inline">You need to be assigned to a school before you can start a session.</span>
                        </div>

                        <form method="POST" action="{{ route('accounting.cashier.start-session') }}" class="mt-4">
                            @csrf
                            <div class="mb-4">
                                <label for="school_id" class="block text-sm font-medium text-gray-700">Select School</label>
                                <select id="school_id" name="school_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a School</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('school_id')" class="mt-2" />
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-800 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Assign School
                                </button>
                            </div>
                        </form>
                    @elseif($activeSession)
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Active Session!</strong>
                            <span class="block sm:inline">Session #{{ $activeSession->id }} started at {{ $activeSession->started_at }}</span>
                        </div>

                        <div class="mt-4 flex space-x-4">
                            <a href="{{ route('accounting.pos.sale') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                New Sale
                            </a>
                            <a href="{{ route('accounting.pos.payment.form') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Record Payment
                            </a>
                            <a href="{{ route('accounting.cashier.end-session', $activeSession) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:border-yellow-800 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                                End Session
                            </a>
                        </div>
                    @else
                        <div class="bg-gray-100 border border-gray-300 text-gray-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">No Active Session</strong>
                            <span class="block sm:inline">Start a new session to begin processing payments</span>
                        </div>

                        @if(isset($terminals) && $terminals->count() > 0)
                            <form method="POST" action="{{ route('accounting.cashier.start-session') }}" class="mt-4">
                                @csrf
                                <div class="mb-4">
                                    <label for="pos_terminal_id" class="block text-sm font-medium text-gray-700">Select Terminal</label>
                                    <select id="pos_terminal_id" name="pos_terminal_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Select a Terminal</option>
                                        @foreach($terminals as $terminal)
                                            <option value="{{ $terminal->id }}">{{ $terminal->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('pos_terminal_id')" class="mt-2" />
                                </div>

                                <div class="mb-4">
                                    <label for="opening_balance" class="block text-sm font-medium text-gray-700">Opening Balance</label>
                                    <input type="number" step="0.01" id="opening_balance" name="opening_balance" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="0.00">
                                    <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-800 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        Start New Session
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mt-4" role="alert">
                                <strong class="font-bold">No terminals available!</strong>
                                <span class="block sm:inline">Please contact an administrator to set up POS terminals.</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>