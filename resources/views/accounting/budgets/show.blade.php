<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Budget Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ $budget->name }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('accounting.budgets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                {{ __('Back to List') }}
                            </a>
                            <a href="{{ route('accounting.budgets.edit', $budget->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                {{ __('Edit Budget') }}
                            </a>
                        </div>
                    </div>

                    @include('components.flash-messages')

                    <!-- Budget Summary -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-4">{{ __('Budget Overview') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Fiscal Year') }}</p>
                                <p class="text-md font-medium">{{ $budget->fiscal_year }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Budget Period') }}</p>
                                <p class="text-md font-medium">
                                    {{ \Carbon\Carbon::parse($budget->start_date)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($budget->end_date)->format('M d, Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Status') }}</p>
                                <p class="text-md font-medium">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ ucfirst($budget->status ?? 'Active') }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Total Budget') }}</p>
                                <p class="text-xl font-bold text-gray-900">${{ number_format($budget->total_amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Total Spent') }}</p>
                                <p class="text-xl font-bold text-gray-900">${{ number_format($budget->spent_amount ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Remaining') }}</p>
                                <p class="text-xl font-bold {{ ($budget->total_amount - ($budget->spent_amount ?? 0)) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format($budget->total_amount - ($budget->spent_amount ?? 0), 2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if(!empty($budget->description))
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-2">{{ __('Description') }}</h4>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-600">{{ $budget->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Budget Categories -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-4">{{ __('Budget Categories') }}</h4>
                        <div class="overflow-x-auto">
                            @if(isset($budget->categories) && count($budget->categories) > 0)
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Category') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Allocated') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Spent') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Remaining') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('% Used') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($budget->categories as $category)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($category->amount, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($category->spent ?? 0, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ ($category->amount - ($category->spent ?? 0)) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    ${{ number_format($category->amount - ($category->spent ?? 0), 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @php 
                                                        $percentUsed = $category->amount > 0 ? round((($category->spent ?? 0) / $category->amount) * 100) : 0;
                                                    @endphp
                                                    <div class="flex items-center">
                                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min($percentUsed, 100) }}%"></div>
                                                        </div>
                                                        <span class="ml-2">{{ $percentUsed }}%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="bg-blue-50 p-4 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700">
                                                {{ __('No budget categories defined yet.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Transactions -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-4">{{ __('Recent Transactions') }}</h4>
                        <div class="overflow-x-auto">
                            @if(isset($budget->transactions) && count($budget->transactions) > 0)
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Description') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Category') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($budget->transactions as $transaction)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction->date)->format('M d, Y') }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->description }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->category }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $transaction->amount < 0 ? 'text-red-600' : 'text-green-600' }}">
                                                    ${{ number_format(abs($transaction->amount), 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="bg-blue-50 p-4 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700">
                                                {{ __('No transactions recorded for this budget yet.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-4">
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ __('Add Transaction') }}
                        </a>
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            {{ __('Export Budget Report') }}
                        </a>
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('View Detailed Report') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>