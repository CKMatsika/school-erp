<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financial Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Income Statement -->
                        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition duration-150">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-100 text-indigo-600 mb-4">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Income Statement</h3>
                            <p class="text-sm text-gray-600 mb-4">View income, expenses, and net profit for a specific period.</p>
                            
                            <form action="{{ route('accounting.reports.income-statement') }}" method="GET" class="mb-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label for="income_start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                                        <input type="date" name="start_date" id="income_start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <div>
                                        <label for="income_end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                                        <input type="date" name="end_date" id="income_end_date" value="{{ now()->endOfMonth()->format('Y-m-d') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                
                                <button type="submit" class="mt-4 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Generate Report
                                </button>
                            </form>
                        </div>
                        
                        <!-- Balance Sheet -->
                        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition duration-150">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-100 text-green-600 mb-4">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Balance Sheet</h3>
                            <p class="text-sm text-gray-600 mb-4">View assets, liabilities, and equity as of a specific date.</p>
                            
                            <form action="{{ route('accounting.reports.balance-sheet') }}" method="GET" class="mb-4">
                                <div>
                                    <label for="balance_date" class="block text-xs font-medium text-gray-700">As of Date</label>
                                    <input type="date" name="as_of_date" id="balance_date" value="{{ now()->format('Y-m-d') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                
                                <button type="submit" class="mt-4 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Generate Report
                                </button>
                            </form>
                        </div>
                        
                        <!-- Tax Report -->
                        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition duration-150">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-yellow-100 text-yellow-600 mb-4">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tax Report</h3>
                            <p class="text-sm text-gray-600 mb-4">View tax collected and paid for a specific period.</p>
                            
                            <form action="{{ route('accounting.reports.tax') }}" method="GET" class="mb-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label for="tax_start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                                        <input type="date" name="start_date" id="tax_start_date" value="{{ now()->startOfYear()->format('Y-m-d') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <div>
                                        <label for="tax_end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                                        <input type="date" name="end_date" id="tax_end_date" value="{{ now()->endOfYear()->format('Y-m-d') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                
                                <button type="submit" class="mt-4 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    Generate Report
                                </button>
                            </form>
                        </div>
                        
                        <!-- Student Balances -->
                        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition duration-150">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-100 text-blue-600 mb-4">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Student Balances</h3>
                            <p class="text-sm text-gray-600 mb-4">View outstanding balances for students as of a specific date.</p>
                            
                            <form action="{{ route('accounting.reports.student-balances') }}" method="GET" class="mb-4">
                                <div>
                                    <label for="student_balance_date" class="block text-xs font-medium text-gray-700">As of Date</label>
                                    <input type="date" name="as_of_date" id="student_balance_date" value="{{ now()->format('Y-m-d') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                
                                <button type="submit" class="mt-4 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Generate Report
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>