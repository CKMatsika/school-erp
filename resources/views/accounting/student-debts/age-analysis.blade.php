<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Debt Age Analysis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Debt Age Analysis Summary') }}</h3>
                        <a href="{{ route('accounting.student-debts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            {{ __('View All Debts') }}
                        </a>
                    </div>
                    
                    @include('components.flash-messages')
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Current') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('1-30 Days') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('31-60 Days') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('61-90 Days') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Over 90 Days') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($debtAnalysis->current_amount ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($debtAnalysis->thirty_days ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($debtAnalysis->sixty_days ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($debtAnalysis->ninety_days ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($debtAnalysis->older_than_ninety ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format(($debtAnalysis->current_amount ?? 0) + ($debtAnalysis->thirty_days ?? 0) + ($debtAnalysis->sixty_days ?? 0) + ($debtAnalysis->ninety_days ?? 0) + ($debtAnalysis->older_than_ninety ?? 0), 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Visualization Section -->
                    <div class="mt-8">
                        <h4 class="text-md font-medium text-gray-700 mb-4">{{ __('Debt Age Distribution') }}</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <!-- Bar Chart Visualization (you can add a proper chart library integration here) -->
                            <div class="h-64 flex items-end space-x-4 pt-8">
                                @php
                                    $total = ($debtAnalysis->current_amount ?? 0) + 
                                            ($debtAnalysis->thirty_days ?? 0) + 
                                            ($debtAnalysis->sixty_days ?? 0) + 
                                            ($debtAnalysis->ninety_days ?? 0) + 
                                            ($debtAnalysis->older_than_ninety ?? 0);
                                    
                                    $currentHeight = $total > 0 ? max(5, (($debtAnalysis->current_amount ?? 0) / $total) * 100) : 0;
                                    $thirtyHeight = $total > 0 ? max(5, (($debtAnalysis->thirty_days ?? 0) / $total) * 100) : 0;
                                    $sixtyHeight = $total > 0 ? max(5, (($debtAnalysis->sixty_days ?? 0) / $total) * 100) : 0;
                                    $ninetyHeight = $total > 0 ? max(5, (($debtAnalysis->ninety_days ?? 0) / $total) * 100) : 0;
                                    $olderHeight = $total > 0 ? max(5, (($debtAnalysis->older_than_ninety ?? 0) / $total) * 100) : 0;
                                @endphp
                                
                                <div class="flex flex-col items-center w-1/5">
                                    <div class="bg-green-500 w-full rounded-t" style="height: {{ $currentHeight }}%"></div>
                                    <span class="text-xs mt-2">{{ __('Current') }}</span>
                                </div>
                                <div class="flex flex-col items-center w-1/5">
                                    <div class="bg-blue-500 w-full rounded-t" style="height: {{ $thirtyHeight }}%"></div>
                                    <span class="text-xs mt-2">{{ __('1-30') }}</span>
                                </div>
                                <div class="flex flex-col items-center w-1/5">
                                    <div class="bg-yellow-500 w-full rounded-t" style="height: {{ $sixtyHeight }}%"></div>
                                    <span class="text-xs mt-2">{{ __('31-60') }}</span>
                                </div>
                                <div class="flex flex-col items-center w-1/5">
                                    <div class="bg-orange-500 w-full rounded-t" style="height: {{ $ninetyHeight }}%"></div>
                                    <span class="text-xs mt-2">{{ __('61-90') }}</span>
                                </div>
                                <div class="flex flex-col items-center w-1/5">
                                    <div class="bg-red-500 w-full rounded-t" style="height: {{ $olderHeight }}%"></div>
                                    <span class="text-xs mt-2">{{ __('90+') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary Cards -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <h4 class="text-sm font-medium text-green-800">{{ __('Current') }}</h4>
                            <p class="text-xl font-bold text-green-700 mt-2">{{ number_format($debtAnalysis->current_amount ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-800">{{ __('1-30 Days') }}</h4>
                            <p class="text-xl font-bold text-blue-700 mt-2">{{ number_format($debtAnalysis->thirty_days ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <h4 class="text-sm font-medium text-yellow-800">{{ __('31-60 Days') }}</h4>
                            <p class="text-xl font-bold text-yellow-700 mt-2">{{ number_format($debtAnalysis->sixty_days ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                            <h4 class="text-sm font-medium text-orange-800">{{ __('61-90 Days') }}</h4>
                            <p class="text-xl font-bold text-orange-700 mt-2">{{ number_format($debtAnalysis->ninety_days ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <h4 class="text-sm font-medium text-red-800">{{ __('Over 90 Days') }}</h4>
                            <p class="text-xl font-bold text-red-700 mt-2">{{ number_format($debtAnalysis->older_than_ninety ?? 0, 2) }}</p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            {{ __('Export Report') }}
                        </a>
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            {{ __('Print Report') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>