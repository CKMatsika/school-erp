<x-app-layout>
    <x-slot name="header">
         {{-- Keep the header simple or add buttons here if preferred over a separate section --}}
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Accounting & Finance Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8"> {{-- More vertical padding --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8"> {{-- Increased spacing between sections --}}

            {{-- Flash Messages --}}
            @include('components.flash-messages')

            <!-- Financial Summary -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl"> {{-- Enhanced card style --}}
                <div class="p-6">
                    <div class="flex items-center space-x-2 mb-4">
                         <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        <h3 class="text-xl font-semibold text-gray-800">Financial Summary (This Month)</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <!-- Income -->
                        <div class="bg-gradient-to-br from-green-50 to-green-100 p-5 rounded-lg border border-green-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                            <h4 class="text-sm font-medium text-green-800 uppercase tracking-wider">Income</h4>
                            <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($income ?? 0, 2) }}</p>
                            <div class="flex items-center mt-2 text-xs text-green-600 opacity-75">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                <span>vs Last Month (requires backend data)</span>
                            </div>
                        </div>
                        <!-- Expenses -->
                        <div class="bg-gradient-to-br from-red-50 to-red-100 p-5 rounded-lg border border-red-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                            <h4 class="text-sm font-medium text-red-800 uppercase tracking-wider">Expenses</h4>
                            <p class="text-3xl font-bold text-red-600 mt-1">{{ number_format($expenses ?? 0, 2) }}</p>
                             <div class="flex items-center mt-2 text-xs text-red-600 opacity-75">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                <span>vs Last Month (requires backend data)</span>
                            </div>
                        </div>
                        <!-- Outstanding Invoices -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-5 rounded-lg border border-blue-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                            <h4 class="text-sm font-medium text-blue-800 uppercase tracking-wider">Receivables</h4>
                            <p class="text-3xl font-bold text-blue-600 mt-1">{{ number_format($outstandingInvoices ?? 0, 2) }}</p>
                            <p class="mt-2 text-xs text-blue-600 opacity-75">Outstanding Invoices</p>
                        </div>
                        <!-- Bank Balance -->
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-5 rounded-lg border border-purple-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                            <h4 class="text-sm font-medium text-purple-800 uppercase tracking-wider">Bank Balance</h4>
                            <p class="text-3xl font-bold text-purple-600 mt-1">{{ number_format($bankBalance ?? 0, 2) }}</p>
                             <p class="mt-2 text-xs text-purple-600 opacity-75">Aggregated</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================== --}}
            {{-- RE-ORGANIZED SECTIONS INTO A TWO-COLUMN LAYOUT FOR WIDER SCREENS --}}
            {{-- ============================================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

                {{-- LEFT COLUMN (MAIN ACTIONS) --}}
                <div class="lg:col-span-2 space-y-6 lg:space-y-8">

                     <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                        <div class="p-6">
                             <div class="flex items-center space-x-2 mb-4">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <h3 class="text-xl font-semibold text-gray-800">Quick Actions</h3>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4"> {{-- Fewer columns work better here --}}
                                <!-- New Invoice -->
                                <a href="{{ route('accounting.invoices.create') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-indigo-300 aspect-square">
                                    <div class="bg-indigo-100 rounded-full p-3 mb-3 group-hover:bg-indigo-200 transition-colors">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700 transition-colors">New Invoice</span>
                                </a>
                                <!-- Record Payment -->
                                <a href="{{ route('accounting.payments.create') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-green-300 aspect-square">
                                    <div class="bg-green-100 rounded-full p-3 mb-3 group-hover:bg-green-200 transition-colors">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-green-700 transition-colors">Record Payment</span>
                                </a>
                                <!-- New Purchase Request -->
                                <a href="{{ route('accounting.purchase-requests.create') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-pink-300 aspect-square">
                                    <div class="bg-pink-100 rounded-full p-3 mb-3 group-hover:bg-pink-200 transition-colors">
                                        <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-pink-700 transition-colors">New PR</span>
                                </a>
                                <!-- Journal Entry -->
                                <a href="{{ route('accounting.journals.create') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-yellow-300 aspect-square">
                                    <div class="bg-yellow-100 rounded-full p-3 mb-3 group-hover:bg-yellow-200 transition-colors">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-yellow-700 transition-colors">Journal Entry</span>
                                </a>
                                <!-- Send SMS -->
                                <a href="{{ route('accounting.sms.send') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-orange-300 aspect-square">
                                    <div class="bg-orange-100 rounded-full p-3 mb-3 group-hover:bg-orange-200 transition-colors">
                                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-orange-700 transition-colors">Send SMS</span>
                                </a>
                                <!-- Reports -->
                                <a href="{{ route('accounting.reports.index') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-blue-300 aspect-square">
                                    <div class="bg-blue-100 rounded-full p-3 mb-3 group-hover:bg-blue-200 transition-colors">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">Reports</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                       <div class="p-6">
                           <div class="flex items-center space-x-2 mb-4">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                <h3 class="text-xl font-semibold text-gray-800">Recent Transactions</h3>
                           </div>
                            @if((isset($recentInvoices) && $recentInvoices->count() > 0) || (isset($recentPayments) && $recentPayments->count() > 0))
                                <div class="overflow-x-auto -mx-6 sm:mx-0">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        {{-- Table Head/Body as before --}}
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @isset($recentInvoices) @foreach($recentInvoices as $invoice) <tr class="odd:bg-white even:bg-gray-50 hover:bg-indigo-50 transition-colors duration-150"> <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($invoice->issue_date)->format('d M Y') ?? 'N/A' }}</td> <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"> <svg class="w-4 h-4 inline mr-1 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Invoice </td> <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"> <a href="{{ route('accounting.invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">{{ $invoice->invoice_number ?? $invoice->id }}</a> </td> <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->contact->name ?? 'N/A' }}</td> <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($invoice->total ?? 0, 2) }}</td> <td class="px-6 py-4 whitespace-nowrap text-center"> <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full @switch($invoice->status) @case('paid') bg-green-100 text-green-800 @break @case('partial') bg-yellow-100 text-yellow-800 @break @case('sent') bg-blue-100 text-blue-800 @break @case('approved') bg-cyan-100 text-cyan-800 @break @case('overdue') bg-red-100 text-red-800 @break @case('void') bg-gray-100 text-gray-500 @break @default bg-gray-100 text-gray-800 @endswitch"> {{ ucfirst($invoice->status) }} </span> </td> </tr> @endforeach @endisset
                                            @isset($recentPayments) @foreach($recentPayments as $payment) <tr class="odd:bg-white even:bg-gray-50 hover:bg-green-50 transition-colors duration-150"> <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($payment->payment_date)->format('d M Y') ?? 'N/A' }}</td> <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"> <svg class="w-4 h-4 inline mr-1 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg> Payment </td> <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"> <a href="{{ route('accounting.payments.show', $payment) }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">{{ $payment->payment_number ?? $payment->id }}</a> </td> <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->contact->name ?? 'N/A' }}</td> <td class="px-6 py-4 whitespace-nowrap text-sm text-green-700 text-right">{{ number_format($payment->amount ?? 0, 2) }}</td> <td class="px-6 py-4 whitespace-nowrap text-center"> <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full @switch($payment->status) @case('completed') bg-green-100 text-green-800 @break @case('pending') bg-yellow-100 text-yellow-800 @break @case('failed') bg-red-100 text-red-800 @break @default bg-gray-100 text-gray-800 @endswitch"> {{ ucfirst($payment->status ?? 'Unknown') }} </span> </td> </tr> @endforeach @endisset
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                {{-- No Recent Transactions Message --}}
                                <div class="bg-gray-50 p-8 rounded-xl border border-dashed border-gray-300 text-center shadow-sm">
                                    <div class="bg-gray-100 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <p class="text-sm text-gray-500">No recent transactions recorded.</p>
                                </div>
                            @endif
                       </div>
                    </div>

                </div>{{-- End Main Content Area --}}


                {{-- Sidebar Area --}}
                <div class="lg:col-span-1 space-y-6 lg:space-y-8">

                    <!-- HR Section -->
                     <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                        <div class="p-6">
                            <div class="flex items-center space-x-2 mb-4">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.08-1.28-.23-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.08-1.28.23-1.857m0 0a5.002 5.002 0 019.542 0M12 6a3 3 0 110-6 3 3 0 010 6z"></path></svg>
                                <h3 class="text-xl font-semibold text-gray-800">Human Resources</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <a href="{{ route('hr.staff.index') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-sky-300 aspect-square">
                                    <div class="bg-sky-100 rounded-full p-3 mb-3 group-hover:bg-sky-200 transition-colors"> <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.08-1.28-.23-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.08-1.28.23-1.857m0 0a5.002 5.002 0 019.542 0M12 6a3 3 0 110-6 3 3 0 010 6z"></path></svg> </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-sky-700 transition-colors">Manage Staff</span>
                                </a>
                                <a href="{{ route('hr.attendance.index') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-cyan-300 aspect-square">
                                    <div class="bg-cyan-100 rounded-full p-3 mb-3 group-hover:bg-cyan-200 transition-colors"> <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-cyan-700 transition-colors">View Attendance</span>
                                </a>
                                 <a href="{{ route('hr.payroll.process.form') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-rose-300 aspect-square">
                                    <div class="bg-rose-100 rounded-full p-3 mb-3 group-hover:bg-rose-200 transition-colors"> <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-rose-700 transition-colors">Process Payroll</span>
                                </a>
                                 <a href="{{ route('hr.payroll.payslips.index') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-orange-300 aspect-square">
                                    <div class="bg-orange-100 rounded-full p-3 mb-3 group-hover:bg-orange-200 transition-colors"> <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg> </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-orange-700 transition-colors">View Payslips</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Procurement & Inventory Links -->
                     <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                         <div class="p-6">
                            <div class="flex items-center space-x-2 mb-4">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <h3 class="text-xl font-semibold text-gray-800">Procurement & Inventory</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <a href="{{ route('accounting.procurement.index') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-amber-300 aspect-square">
                                    <div class="bg-amber-100 rounded-full p-3 mb-3 group-hover:bg-amber-200 transition-colors"> <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg> </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-amber-700 transition-colors">Procurement</span>
                                </a>
                                <a href="{{ route('accounting.inventory-items.index') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-blue-300 aspect-square">
                                    <div class="bg-blue-100 rounded-full p-3 mb-3 group-hover:bg-blue-200 transition-colors"> <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg> </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">Inventory</span>
                                </a>
                                 {{-- Add other key links like Suppliers, POs etc. if desired --}}
                            </div>
                         </div>
                     </div>

                     <!-- Banking & Cash Management Links -->
                     <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                        <div class="p-6">
                           <div class="flex items-center space-x-2 mb-4">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                <h3 class="text-xl font-semibold text-gray-800">Banking & Cash</h3>
                           </div>
                           <div class="grid grid-cols-2 gap-4">
                                <a href="{{ route('accounting.cashbook.index') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-emerald-300 aspect-square">
                                    <div class="bg-emerald-100 rounded-full p-3 mb-3 group-hover:bg-emerald-200 transition-colors"> <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg> </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-emerald-700 transition-colors">Cashbook</span>
                                </a>
                                <a href="{{ route('accounting.bank-reconciliation.index') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-blue-300 aspect-square">
                                    <div class="bg-blue-100 rounded-full p-3 mb-3 group-hover:bg-blue-200 transition-colors"> <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg> </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">Bank Reconciliation</span>
                                </a>
                                <a href="{{ route('accounting.bank-transfers.index') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-indigo-300 aspect-square">
                                    <div class="bg-indigo-100 rounded-full p-3 mb-3 group-hover:bg-indigo-200 transition-colors"> <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg> </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700 transition-colors">Bank Transfers</span>
                                </a>
                                <a href="{{ route('accounting.bank-accounts.index') }}" class="group bg-white p-4 rounded-lg border border-gray-200 text-center shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center hover:border-violet-300 aspect-square">
                                    <div class="bg-violet-100 rounded-full p-3 mb-3 group-hover:bg-violet-200 transition-colors"> <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg> </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-violet-700 transition-colors">Bank Accounts</span>
                                </a>
                           </div>
                        </div>
                    </div>

                    <!-- Configuration Links (Simplified List) -->
                     <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                        <div class="p-6">
                            <div class="flex items-center space-x-2 mb-4">
                                 <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <h3 class="text-xl font-semibold text-gray-800">Configuration</h3>
                            </div>
                             <div class="space-y-1 text-sm">
                                 <a href="{{ route('accounting.academic-years.index') }}" class="block text-indigo-600 hover:underline">Academic Years & Terms</a>
                                 <a href="{{ route('accounting.classes.index') }}" class="block text-indigo-600 hover:underline">Manage Classes</a>
                                 <a href="{{ route('accounting.fee-structures.index') }}" class="block text-indigo-600 hover:underline">Fee Structures</a>
                                <a href="{{ route('accounting.accounts.index') }}" class="block text-indigo-600 hover:underline">Chart of Accounts</a>
                                <a href="{{ route('accounting.account-types.index') }}" class="block text-indigo-600 hover:underline">Account Types</a>
                                <a href="{{ route('accounting.payment-methods.index') }}" class="block text-indigo-600 hover:underline">Payment Methods</a>
                                <a href="{{ route('accounting.suppliers.index') }}" class="block text-indigo-600 hover:underline">Suppliers</a>
                                <a href="{{ route('accounting.item-categories.index') }}" class="block text-indigo-600 hover:underline">Item Categories</a>
                                <a href="{{ route('accounting.inventory-items.index') }}" class="block text-indigo-600 hover:underline">Inventory Items</a>
                                <a href="{{ route('hr.payroll.elements.index') }}" class="block text-indigo-600 hover:underline">Payroll Elements</a>
                                 <a href="{{ route('accounting.sms.gateways') }}" class="block text-indigo-600 hover:underline">SMS Gateways</a>
                                 <a href="{{ route('accounting.sms.templates') }}" class="block text-indigo-600 hover:underline">SMS Templates</a>
                            </div>
                        </div>
                    </div>

                </div>{{-- End Sidebar Area --}}

            </div>{{-- End Main Grid --}}

        </div> {{-- End max-w-7xl --}}
    </div> {{-- End py-8 --}}

</x-app-layout>