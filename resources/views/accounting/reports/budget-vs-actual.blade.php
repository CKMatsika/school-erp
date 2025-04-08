{{-- resources/views/accounting/reports/budget-vs-actual.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Budget vs Actual Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filter Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Report Filters') }}</h3>
                    <form method="GET" action="{{ route('accounting.reports.budget-vs-actual') }}">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Start Date --}}
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
                                <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>

                            {{-- End Date --}}
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">{{ __('End Date') }}</label>
                                <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>

                             {{-- Budget Selection (Optional) --}}
                            <div>
                                <label for="budget_id" class="block text-sm font-medium text-gray-700">{{ __('Specific Budget (Optional)') }}</label>
                                <select name="budget_id" id="budget_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">{{ __('All Budgets') }}</option>
                                    {{-- Ensure $availableBudgets is passed from controller --}}
                                    @isset($availableBudgets)
                                        @foreach($availableBudgets as $budget)
                                            <option value="{{ $budget->id }}" {{ (isset($selectedBudgetId) && $selectedBudgetId == $budget->id) ? 'selected' : '' }}>
                                                {{ $budget->name }} {{-- Adjust property if needed --}}
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                             <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Apply Filters') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Report Results --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ __('Report for period:') }} {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        @if($selectedBudgetId && isset($availableBudgets))
                            <span class="text-base font-normal"> (Budget: {{ $availableBudgets->firstWhere('id', $selectedBudgetId)?->name ?? 'Selected' }})</span>
                        @endif
                    </h3>

                    @include('components.flash-messages') {{-- If needed --}}

                    @if(!empty($reportData))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Account Code') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Account Name') }}</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Budgeted') }}</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actual') }}</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Variance') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {{-- Ensure controller passes $reportData as an array --}}
                                    @php
                                        $totalBudgeted = 0;
                                        $totalActual = 0;
                                        $totalVariance = 0;
                                    @endphp
                                    @foreach($reportData as $row)
                                        @php
                                            $totalBudgeted += $row['budgeted'];
                                            $totalActual += $row['actual'];
                                            $totalVariance += $row['variance'];
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $row['account_code'] ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['account_name'] ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($row['budgeted'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($row['actual'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $row['variance'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ number_format($row['variance'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-100">
                                    <tr>
                                         <th scope="row" colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase">Totals</th>
                                         <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900">{{ number_format($totalBudgeted, 2) }}</td>
                                         <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900">{{ number_format($totalActual, 2) }}</td>
                                         <td class="px-6 py-3 text-right text-sm font-semibold {{ $totalVariance < 0 ? 'text-red-700' : 'text-green-700' }}">
                                             {{ number_format($totalVariance, 2) }}
                                         </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                         <div class="bg-yellow-50 p-4 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                     <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                         <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 3.001-1.742 3.001H4.42c-1.53 0-2.493-1.667-1.743-3.001l5.58-9.92zM10 13a1 1 0 11-2 0 1 1 0 012 0zm-1-4a1 1 0 011 1v1.5a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd" />
                                     </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        {{ __('No data found for the selected filters. Please adjust the date range or ensure budget data exists.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>