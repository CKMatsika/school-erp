<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Accounting & Finance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Financial Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Income -->
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <h4 class="text-sm font-medium text-green-800">Income</h4>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($income ?? 0, 2) }}</p>
                            <p class="text-xs text-green-600">This Month</p>
                        </div>
                        
                        <!-- Expenses -->
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <h4 class="text-sm font-medium text-red-800">Expenses</h4>
                            <p class="text-2xl font-bold text-red-600">{{ number_format($expenses ?? 0, 2) }}</p>
                            <p class="text-xs text-red-600">This Month</p>
                        </div>
                        
                        <!-- Outstanding Invoices -->
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-800">Outstanding</h4>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($outstandingInvoices ?? 0, 2) }}</p>
                            <p class="text-xs text-blue-600">Unpaid Invoices</p>
                        </div>
                        
                        <!-- Bank Balance -->
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <h4 class="text-sm font-medium text-purple-800">Bank Balance</h4>
                            <p class="text-2xl font-bold text-purple-600">{{ number_format($bankBalance ?? 0, 2) }}</p>
                            <p class="text-xs text-purple-600">Current Balance</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- Core Functions -->
                        <a href="{{ route('accounting.invoices.create') }}" class="bg-indigo-50 p-4 rounded-lg border border-indigo-200 text-center hover:bg-indigo-100 transition">
                            <svg class="w-6 h-6 mx-auto text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-indigo-700">New Invoice</span>
                        </a>
                        
                        <a href="{{ route('accounting.payments.create') }}" class="bg-green-50 p-4 rounded-lg border border-green-200 text-center hover:bg-green-100 transition">
                            <svg class="w-6 h-6 mx-auto text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-green-700">Record Payment</span>
                        </a>
                        
                        <a href="{{ route('accounting.journals.create') }}" class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 text-center hover:bg-yellow-100 transition">
                            <svg class="w-6 h-6 mx-auto text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-yellow-700">Journal Entry</span>
                        </a>
                        
                        <a href="{{ route('accounting.reports.index') }}" class="bg-blue-50 p-4 rounded-lg border border-blue-200 text-center hover:bg-blue-100 transition">
                            <svg class="w-6 h-6 mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-blue-700">Reports</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student Financial Management -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Student Financial Management</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- Temporary fix until route is properly defined -->
                        <a href="{{ url('accounting/bulk-invoice') }}" class="...">
                            <svg class="w-6 h-6 mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-blue-700">Bulk Invoice</span>
                        </a>
                        
                        <a href="{{ route('accounting.student-ledger.index') }}" class="bg-indigo-50 p-4 rounded-lg border border-indigo-200 text-center hover:bg-indigo-100 transition">
                            <svg class="w-6 h-6 mx-auto text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-indigo-700">Student Ledger</span>
                        </a>
                        
                        <a href="{{ route('accounting.payment-plans.index') }}" class="bg-purple-50 p-4 rounded-lg border border-purple-200 text-center hover:bg-purple-100 transition">
                            <svg class="w-6 h-6 mx-auto text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-purple-700">Payment Plans</span>
                        </a>
                        
                        <a href="{{ route('accounting.student-debts.age-analysis') }}" class="bg-pink-50 p-4 rounded-lg border border-pink-200 text-center hover:bg-pink-100 transition">
                            <svg class="w-6 h-6 mx-auto text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-pink-700">Age Analysis</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Advanced Features -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Budget & Management</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('accounting.budgets.index') }}" class="bg-green-50 p-4 rounded-lg border border-green-200 text-center hover:bg-green-100 transition">
                            <svg class="w-6 h-6 mx-auto text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-green-700">Budgets</span>
                        </a>
                        
                        <a href="{{ route('accounting.capex.index') }}" class="bg-teal-50 p-4 rounded-lg border border-teal-200 text-center hover:bg-teal-100 transition">
                            <svg class="w-6 h-6 mx-auto text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-teal-700">CapEx Projects</span>
                        </a>
                        
                        <a href="{{ route('accounting.sms.send') }}" class="bg-orange-50 p-4 rounded-lg border border-orange-200 text-center hover:bg-orange-100 transition">
                            <svg class="w-6 h-6 mx-auto text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-orange-700">Send SMS</span>
                        </a>
                        
                        <a href="{{ route('accounting.reports.budget-vs-actual') }}" class="bg-cyan-50 p-4 rounded-lg border border-cyan-200 text-center hover:bg-cyan-100 transition">
                            <svg class="w-6 h-6 mx-auto text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-cyan-700">Budget Reports</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Student Management (Temporary) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Student Management</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('students.import') }}" class="bg-emerald-50 p-4 rounded-lg border border-emerald-200 text-center hover:bg-emerald-100 transition">
                            <svg class="w-6 h-6 mx-auto text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-emerald-700">Import Students</span>
                        </a>
                        
                        <a href="{{ route('students.promotion') }}" class="bg-amber-50 p-4 rounded-lg border border-amber-200 text-center hover:bg-amber-100 transition">
                            <svg class="w-6 h-6 mx-auto text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                            <span class="block mt-2 text-sm font-medium text-amber-700">Student Promotion</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                    </div>
                    
                    @if(isset($recentInvoices) && $recentInvoices->count() > 0 || isset($recentPayments) && $recentPayments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if(isset($recentInvoices))
                                        @foreach($recentInvoices as $invoice)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->issue_date->format('M d, Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Invoice</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                    <a href="{{ route('accounting.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->contact->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($invoice->total, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($invoice->status == 'paid')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                                    @elseif($invoice->status == 'partial')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                                                    @elseif($invoice->status == 'sent')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Sent</span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($invoice->status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    
                                    @if(isset($recentPayments))
                                        @foreach($recentPayments as $payment)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_date->format('M d, Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Payment</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                    <a href="{{ route('accounting.payments.show', $payment) }}">{{ $payment->payment_number }}</a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->contact->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($payment->amount, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($payment->status == 'completed')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                                    @elseif($payment->status == 'pending')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($payment->status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-gray-50 p-8 rounded-lg border border-gray-200 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <p class="mt-2 text-gray-500">No recent transactions to display</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>