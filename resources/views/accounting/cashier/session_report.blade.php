<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Session Report') }} #{{ $session->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ $report['z_reading_number'] }}</h3>
                        <div class="text-right">
                            <button onclick="window.print()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print
                            </button>
                        </div>
                    </div>
                    
                    <div class="border-t border-b border-gray-200 py-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Terminal ID</p>
                                <p class="font-medium">{{ $report['terminal_id'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Cashier</p>
                                <p class="font-medium">{{ $report['cashier'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Session Period</p>
                                <p class="font-medium">{{ $report['open_time'] }} - {{ $report['close_time'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Duration</p>
                                <p class="font-medium">{{ $report['duration'] }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Financial Summary</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                <p class="text-xs text-gray-500">Opening Balance</p>
                                <p class="text-lg font-medium">{{ number_format($report['opening_balance'], 2) }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                <p class="text-xs text-gray-500">Closing Balance</p>
                                <p class="text-lg font-medium">{{ number_format($report['closing_balance'], 2) }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                <p class="text-xs text-gray-500">Expected Balance</p>
                                <p class="text-lg font-medium">{{ number_format($report['expected_balance'], 2) }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200 {{ $report['difference'] != 0 ? ($report['difference'] > 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200') : '' }}">
                                <p class="text-xs text-gray-500">Difference</p>
                                <p class="text-lg font-medium {{ $report['difference'] > 0 ? 'text-green-600' : ($report['difference'] < 0 ? 'text-red-600' : '') }}">
                                    {{ number_format($report['difference'], 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Transactions Summary</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div class="bg-indigo-50 p-3 rounded-md border border-indigo-200">
                                <p class="text-xs text-gray-500">Total Transactions</p>
                                <p class="text-lg font-medium">{{ $report['total_transactions'] }}</p>
                                <p class="text-xs text-gray-500">KES {{ number_format($report['total_amount'], 2) }}</p>
                            </div>
                            <div class="bg-yellow-50 p-3 rounded-md border border-yellow-200">
                                <p class="text-xs text-gray-500">Cash Transactions</p>
                                <p class="text-lg font-medium">{{ $report['cash_count'] }}</p>
                                <p class="text-xs text-gray-500">KES {{ number_format($report['cash_total'], 2) }}</p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-md border border-blue-200">
                                <p class="text-xs text-gray-500">Card Transactions</p>
                                <p class="text-lg font-medium">{{ $report['card_count'] }}</p>
                                <p class="text-xs text-gray-500">KES {{ number_format($report['card_total'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($report['transactions'] && count($report['transactions']) > 0)
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Transaction Details</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($report['transactions'] as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction['time'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $transaction['reference'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction['method'] === 'card' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($transaction['method']) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($transaction['amount'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-4 flex justify-between">
                <a href="{{ route('accounting.cashier.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Back to Dashboard
                </a>
                
                @if($session->status === 'closed' && !$session->approval_status && Auth::user()->hasRole('admin'))
                <form method="POST" action="{{ route('accounting.pos.sessions.approve', $session) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        Approve Session
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>