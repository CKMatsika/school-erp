<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Point of Sale') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    <!-- Terminals and Active Sessions -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Available Terminals</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @forelse($terminals as $terminal)
                                <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                    <h4 class="text-lg font-medium text-gray-900 mb-2">{{ $terminal->terminal_name }}</h4>
                                    <p class="text-sm text-gray-600 mb-2">ID: {{ $terminal->terminal_id }}</p>
                                    <p class="text-sm text-gray-600 mb-2">Location: {{ $terminal->location ?? 'Not specified' }}</p>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Status:
                                        @if($terminal->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                        @endif
                                    </p>
                                    
                                    @php
                                        $hasActiveSession = false;
                                        $userHasActiveSession = false;
                                        
                                        foreach ($activeSessions as $session) {
                                            if ($session->terminal_id === $terminal->id) {
                                                $hasActiveSession = true;
                                                if ($session->user_id === auth()->id()) {
                                                    $userHasActiveSession = true;
                                                }
                                                break;
                                            }
                                        }
                                    @endphp
                                    
                                    @if($userHasActiveSession)
                                        <div class="mt-4 flex">
                                            <a href="{{ route('accounting.pos.sale') }}" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                                                Make Sale
                                            </a>
                                            <a href="{{ route('accounting.pos.z-reading') }}" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                Z-Reading
                                            </a>
                                        </div>
                                    @elseif($hasActiveSession)
                                        <p class="text-sm text-red-600 mb-4">Terminal is currently in use</p>
                                    @else
                                        <form action="{{ route('accounting.pos.open-session') }}" method="POST" class="mt-4">
                                            @csrf
                                            <input type="hidden" name="terminal_id" value="{{ $terminal->id }}">
                                            
                                            <div class="mb-4">
                                                <label for="opening_balance_{{ $terminal->id }}" class="block text-sm font-medium text-gray-700">Opening Balance</label>
                                                <input type="number" step="0.01" name="opening_balance" id="opening_balance_{{ $terminal->id }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                            </div>
                                            
                                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                Start Session
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="col-span-3 bg-gray-50 p-6 rounded-lg border border-gray-200 text-center">
                                    <p class="text-gray-500">No POS terminals available. Please contact the administrator.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Active Sessions -->
                    @if(count($activeSessions) > 0)
                        <div>
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Active Sessions</h3>
                            
                            <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terminal</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opening Time</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opening Balance</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($activeSessions as $session)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->terminal->terminal_name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->user->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->opening_time->format('M d, Y H:i') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($session->opening_balance, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    @if($session->user_id === auth()->id())
                                                        <a href="{{ route('accounting.pos.sale') }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Make Sale</a>
                                                        <a href="{{ route('accounting.pos.z-reading') }}" class="text-yellow-600 hover:text-yellow-900">Z-Reading</a>
                                                    @else
                                                        <span class="text-gray-400">N/A</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>