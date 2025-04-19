{{-- resources/views/accounting/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Accounting & Finance Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Messages (Keep as is) --}}
            @include('components.flash-messages')

            <!-- Financial Summary -->
            {{-- First card: Add bottom margin, no negative top margin. Use shadow-md --}}
            <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-10">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Financial Summary (This Month)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                        {{-- Inner cards retain their specific styling --}}
                        <div class="bg-gradient-to-br from-green-50 to-green-100 p-5 rounded-xl border border-green-200 shadow-sm hover:shadow-md transition-shadow duration-300 transform hover:-translate-y-1">
                            <h4 class="text-sm font-medium text-green-800 uppercase tracking-wider">Income</h4>
                            <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($income ?? 0, 2) }}</p>
                            <div class="flex items-center mt-2 text-xs text-green-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                <span>This Month</span>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-red-50 to-red-100 p-5 rounded-xl border border-red-200 shadow-sm hover:shadow-md transition-shadow duration-300 transform hover:-translate-y-1">
                            <h4 class="text-sm font-medium text-red-800 uppercase tracking-wider">Expenses</h4>
                            <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($expenses ?? 0, 2) }}</p>
                            <div class="flex items-center mt-2 text-xs text-red-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                <span>This Month</span>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-5 rounded-xl border border-blue-200 shadow-sm hover:shadow-md transition-shadow duration-300 transform hover:-translate-y-1">
                            <h4 class="text-sm font-medium text-blue-800 uppercase tracking-wider">Receivables</h4>
                            <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($outstandingInvoices ?? 0, 2) }}</p>
                            <div class="flex items-center mt-2 text-xs text-blue-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Outstanding Invoices</span>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-5 rounded-xl border border-purple-200 shadow-sm hover:shadow-md transition-shadow duration-300 transform hover:-translate-y-1">
                            <h4 class="text-sm font-medium text-purple-800 uppercase tracking-wider">Bank Balance</h4>
                            <p class="text-3xl font-bold text-purple-600 mt-2">{{ number_format($bankBalance ?? 0, 2) }}</p>
                            <div class="flex items-center mt-2 text-xs text-purple-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                <span>Aggregated</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            {{-- Subsequent cards: Add bottom margin AND negative top margin (-mt-6 pulls it up) --}}
            <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-10 -mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Quick Actions</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        {{-- Action items retain their styling --}}
                        <a href="{{ route('accounting.invoices.create') }}" class="group bg-white p-4 rounded-xl border border-indigo-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-indigo-50 aspect-square">
                            <div class="bg-indigo-100 rounded-full p-3 mb-3 group-hover:bg-indigo-200 transition-colors">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700 transition-colors">New Invoice</span>
                        </a>
                        <a href="{{ route('accounting.payments.create') }}" class="group bg-white p-4 rounded-xl border border-green-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-green-50 aspect-square">
                            <div class="bg-green-100 rounded-full p-3 mb-3 group-hover:bg-green-200 transition-colors">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-green-700 transition-colors">Record Payment</span>
                        </a>
                         <a href="{{ route('accounting.journals.create') }}" class="group bg-white p-4 rounded-xl border border-yellow-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-yellow-50 aspect-square">
                            <div class="bg-yellow-100 rounded-full p-3 mb-3 group-hover:bg-yellow-200 transition-colors">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-yellow-700 transition-colors">Journal Entry</span>
                        </a>
                        <a href="{{ route('accounting.sms.send') }}" class="group bg-white p-4 rounded-xl border border-orange-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-orange-50 aspect-square">
                            <div class="bg-orange-100 rounded-full p-3 mb-3 group-hover:bg-orange-200 transition-colors">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-orange-700 transition-colors">Send SMS</span>
                        </a>
                        <a href="{{ route('accounting.reports.index') }}" class="group bg-white p-4 rounded-xl border border-blue-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-blue-50 aspect-square">
                            <div class="bg-blue-100 rounded-full p-3 mb-3 group-hover:bg-blue-200 transition-colors">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">Reports</span>
                        </a>
                        <a href="{{ route('accounting.purchase-requests.create') }}" class="group bg-white p-4 rounded-xl border border-pink-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-pink-50 aspect-square">
                             <div class="bg-pink-100 rounded-full p-3 mb-3 group-hover:bg-pink-200 transition-colors">
                                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-pink-700 transition-colors">New PR</span>
                        </a>
                    </div>
                </div>
            </div>

             {{-- HR Section --}}
             {{-- Apply mb-10 and -mt-6 --}}
            <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-10 -mt-6">
                 <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Human Resources</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        {{-- Links retain their styling --}}
                         <a href="{{ route('hr.staff.index') }}" class="group bg-white p-4 rounded-xl border border-sky-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-sky-50 aspect-square">
                            <div class="bg-sky-100 rounded-full p-3 mb-3 group-hover:bg-sky-200 transition-colors">
                                <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.08-1.28-.23-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.08-1.28.23-1.857m0 0a5.002 5.002 0 019.542 0M12 6a3 3 0 110-6 3 3 0 010 6z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-sky-700 transition-colors">Manage Staff</span>
                        </a>
                        <a href="{{ route('hr.attendance.create') }}" class="group bg-white p-4 rounded-xl border border-cyan-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-cyan-50 aspect-square">
                            <div class="bg-cyan-100 rounded-full p-3 mb-3 group-hover:bg-cyan-200 transition-colors">
                                <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-cyan-700 transition-colors">Record Attendance</span>
                        </a>
                         <a href="{{ route('hr.attendance.reports') }}" class="group bg-white p-4 rounded-xl border border-blue-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-blue-50 aspect-square">
                            <div class="bg-blue-100 rounded-full p-3 mb-3 group-hover:bg-blue-200 transition-colors">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">Attendance Reports</span>
                        </a>
                         <a href="{{ route('hr.payroll.process.form') }}" class="group bg-white p-4 rounded-xl border border-rose-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-rose-50 aspect-square">
                            <div class="bg-rose-100 rounded-full p-3 mb-3 group-hover:bg-rose-200 transition-colors">
                                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-rose-700 transition-colors">Process Payroll</span>
                        </a>
                         <a href="{{ route('hr.payroll.payslips.index') }}" class="group bg-white p-4 rounded-xl border border-orange-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-orange-50 aspect-square">
                            <div class="bg-orange-100 rounded-full p-3 mb-3 group-hover:bg-orange-200 transition-colors">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-orange-700 transition-colors">View Payslips</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Procurement & Inventory Management -->
            {{-- Apply mb-10 and -mt-6 --}}
            <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-10 -mt-6">
                 <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Procurement & Inventory</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                       {{-- Links retain their styling --}}
                        <a href="{{ route('accounting.procurement.index') }}" class="group bg-white p-4 rounded-xl border border-amber-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-amber-50 aspect-square">
                             <div class="bg-amber-100 rounded-full p-3 mb-3 group-hover:bg-amber-200 transition-colors">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-amber-700 transition-colors">Procurement</span>
                        </a>
                        <a href="{{ route('accounting.suppliers.index') }}" class="group bg-white p-4 rounded-xl border border-yellow-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-yellow-50 aspect-square">
                             <div class="bg-yellow-100 rounded-full p-3 mb-3 group-hover:bg-yellow-200 transition-colors">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-yellow-700 transition-colors">Suppliers</span>
                        </a>
                        <a href="{{ route('accounting.purchase-requests.index') }}" class="group bg-white p-4 rounded-xl border border-pink-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-pink-50 aspect-square">
                             <div class="bg-pink-100 rounded-full p-3 mb-3 group-hover:bg-pink-200 transition-colors">
                                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-pink-700 transition-colors">Purchase Requests</span>
                        </a>
                         <a href="{{ route('accounting.tenders.index') }}" class="group bg-white p-4 rounded-xl border border-rose-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-rose-50 aspect-square">
                             <div class="bg-rose-100 rounded-full p-3 mb-3 group-hover:bg-rose-200 transition-colors">
                                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-rose-700 transition-colors">Tenders</span>
                        </a>
                        <a href="{{ route('accounting.purchase-orders.index') }}" class="group bg-white p-4 rounded-xl border border-purple-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-purple-50 aspect-square">
                             <div class="bg-purple-100 rounded-full p-3 mb-3 group-hover:bg-purple-200 transition-colors">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700 transition-colors">Purchase Orders</span>
                        </a>
                         <a href="{{ route('accounting.procurement-contracts.index') }}" class="group bg-white p-4 rounded-xl border border-lime-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-lime-50 aspect-square">
                             <div class="bg-lime-100 rounded-full p-3 mb-3 group-hover:bg-lime-200 transition-colors">
                                <svg class="w-6 h-6 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-lime-700 transition-colors">Contracts</span>
                        </a>
                        <a href="{{ route('accounting.goods-receipts.index') }}" class="group bg-white p-4 rounded-xl border border-green-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-green-50 aspect-square">
                             <div class="bg-green-100 rounded-full p-3 mb-3 group-hover:bg-green-200 transition-colors">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-green-700 transition-colors">Goods Receipt</span>
                        </a>
                        <a href="{{ route('accounting.inventory-items.index') }}" class="group bg-white p-4 rounded-xl border border-blue-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-blue-50 aspect-square">
                            <div class="bg-blue-100 rounded-full p-3 mb-3 group-hover:bg-blue-200 transition-colors">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">Inventory</span>
                        </a>
                         <a href="{{ route('accounting.item-categories.index') }}" class="group bg-white p-4 rounded-xl border border-fuchsia-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-fuchsia-50 aspect-square">
                           <div class="bg-fuchsia-100 rounded-full p-3 mb-3 group-hover:bg-fuchsia-200 transition-colors">
                                <svg class="w-6 h-6 text-fuchsia-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                           </div>
                           <span class="text-sm font-medium text-gray-700 group-hover:text-fuchsia-700 transition-colors">Item Categories</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Banking & Cash Management -->
             <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-10 -mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Banking & Cash Management</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                       <a href="{{ route('accounting.cashbook.index') }}" class="group bg-white p-4 rounded-xl border border-emerald-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-emerald-50 aspect-square">
                            {{-- ... Cashbook Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-emerald-700 transition-colors">Cashbook</span>
                        </a>
                        <a href="{{ route('accounting.bank-reconciliation.index') }}" class="group bg-white p-4 rounded-xl border border-blue-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-blue-50 aspect-square">
                            {{-- ... Bank Rec Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">Bank Reconciliation</span>
                        </a>
                        <a href="{{ route('accounting.bank-transfers.index') }}" class="group bg-white p-4 rounded-xl border border-indigo-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-indigo-50 aspect-square">
                             {{-- ... Transfer Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700 transition-colors">Bank Transfers</span>
                        </a>
                        <a href="{{ route('accounting.bank-accounts.index') }}" class="group bg-white p-4 rounded-xl border border-violet-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-violet-50 aspect-square">
                            {{-- ... Bank Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-violet-700 transition-colors">Bank Accounts</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student Financial Management -->
            <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-10 -mt-6">
                 <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Student Financial Management</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <a href="{{ route('accounting.contacts.index') }}" class="group bg-white p-4 rounded-xl border border-sky-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-sky-50 aspect-square">
                             {{-- ... Contact Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-sky-700 transition-colors">Manage Contacts</span>
                        </a>
                        <a href="{{ route('accounting.bulk-invoice.index') }}" class="group bg-white p-4 rounded-xl border border-blue-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-blue-50 aspect-square">
                             {{-- ... Bulk Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">Bulk Invoice</span>
                        </a>
                        <a href="{{ route('accounting.student-ledger.index') }}" class="group bg-white p-4 rounded-xl border border-indigo-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-indigo-50 aspect-square">
                            {{-- ... Ledger Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700 transition-colors">Student Ledger</span>
                        </a>
                        <a href="{{ route('accounting.payment-plans.index') }}" class="group bg-white p-4 rounded-xl border border-purple-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-purple-50 aspect-square">
                             {{-- ... Plan Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700 transition-colors">Payment Plans</span>
                        </a>
                        <a href="{{ route('accounting.student-debts.age-analysis') }}" class="group bg-white p-4 rounded-xl border border-pink-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-pink-50 aspect-square">
                           {{-- ... Age Analysis Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-pink-700 transition-colors">Age Analysis</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Configuration & Setup -->
            <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-10 -mt-6">
                 <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Configuration & Setup</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <a href="{{ route('accounting.academic-years.index') }}" class="group bg-white p-4 rounded-xl border border-purple-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-purple-50 aspect-square">
                             {{-- ... Academic Year Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700 transition-colors">Academic Years</span>
                        </a>
                       <a href="{{ route('accounting.classes.index') }}" class="group bg-white p-4 rounded-xl border border-orange-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-orange-50 aspect-square">
                            {{-- ... Classes Icon ... --}}
                           <span class="text-sm font-medium text-gray-700 group-hover:text-orange-700 transition-colors">Manage Classes</span>
                        </a>
                        <a href="{{ route('accounting.fee-structures.index') }}" class="group bg-white p-4 rounded-xl border border-lime-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-lime-50 aspect-square">
                            {{-- ... Fees Icon ... --}}
                           <span class="text-sm font-medium text-gray-700 group-hover:text-lime-700 transition-colors">Fee Structures</span>
                        </a>
                        {{-- *** MANAGE SUBJECTS LINK (Copied from previous answer) *** --}}
                        <a href="{{ route('accounting.subjects.index') }}" class="group bg-white p-4 rounded-xl border border-teal-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-teal-50 aspect-square">
                            <div class="bg-teal-100 rounded-full p-3 mb-3 group-hover:bg-teal-200 transition-colors">
                                {{-- Icon for Books/Subjects --}}
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-teal-700 transition-colors">Manage Subjects</span>
                        </a>
                        {{-- *** END SUBJECTS LINK *** --}}
                         <a href="{{ route('accounting.accounts.index') }}" class="group bg-white p-4 rounded-xl border border-gray-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-gray-50 aspect-square">
                             {{-- ... CoA Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">Chart of Accounts</span>
                        </a>
                        <a href="{{ route('accounting.account-types.index') }}" class="group bg-white p-4 rounded-xl border border-gray-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-gray-100 aspect-square">
                             {{-- ... Acc Types Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">Account Types</span>
                        </a>
                        <a href="{{ route('accounting.payment-methods.index') }}" class="group bg-white p-4 rounded-xl border border-teal-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-teal-50 aspect-square">
                             {{-- ... Pay Methods Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-teal-700 transition-colors">Payment Methods</span>
                        </a>
                         <a href="{{ route('accounting.sms-gateways.index') }}" class="group bg-white p-4 rounded-xl border border-blue-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-blue-50 aspect-square">
                             {{-- ... SMS Gateway Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">SMS Gateways</span>
                        </a>
                         <a href="{{ route('accounting.sms.templates') }}" class="group bg-white p-4 rounded-xl border border-purple-200 text-center shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center hover:bg-purple-50 aspect-square">
                             {{-- ... SMS Template Icon ... --}}
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700 transition-colors">SMS Templates</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
             <div class="bg-white overflow-hidden shadow-md sm:rounded-lg -mt-6">
                 <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Recent Transactions</h3>
                        {{-- <a href="#" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a> --}}
                    </div>

                    @if((isset($recentInvoices) && $recentInvoices->count() > 0) || (isset($recentPayments) && $recentPayments->count() > 0))
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
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
                                    @isset($recentInvoices)
                                        @foreach($recentInvoices as $invoice)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($invoice->issue_date)->format('M d, Y') ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Invoice</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                    <a href="{{ route('accounting.invoices.show', $invoice) }}">{{ $invoice->invoice_number ?? $invoice->id }}</a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->contact->name ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($invoice->total ?? 0, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @switch($invoice->status)
                                                            @case('paid') bg-green-100 text-green-800 @break
                                                            @case('partial') bg-yellow-100 text-yellow-800 @break
                                                            @case('sent') bg-blue-100 text-blue-800 @break
                                                            @case('approved') bg-cyan-100 text-cyan-800 @break
                                                            @case('overdue') bg-red-100 text-red-800 @break
                                                            @case('void') bg-gray-100 text-gray-500 @break
                                                            @default bg-gray-100 text-gray-800
                                                        @endswitch">
                                                        {{ ucfirst($invoice->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endisset

                                    @isset($recentPayments)
                                        @foreach($recentPayments as $payment)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($payment->payment_date)->format('M d, Y') ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Payment</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                    <a href="{{ route('accounting.payments.show', $payment) }}">{{ $payment->payment_number ?? $payment->id }}</a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->contact->name ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">{{ number_format($payment->amount ?? 0, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @switch($payment->status)
                                                            @case('completed') bg-green-100 text-green-800 @break
                                                            @case('pending') bg-yellow-100 text-yellow-800 @break
                                                            @case('failed') bg-red-100 text-red-800 @break
                                                            @default bg-gray-100 text-gray-800
                                                        @endswitch">
                                                        {{ ucfirst($payment->status ?? 'Unknown') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endisset
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{-- No Recent Transactions Message --}}
                        <div class="bg-gray-50 p-8 rounded-lg border border-gray-200 text-center">
                            <div class="bg-gray-100 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <p class="text-sm text-gray-500">No recent transactions to display</p>
                            <a href="{{ route('accounting.invoices.create') }}" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">Create Invoice</a>
                        </div>
                    @endif
                </div>
            </div>

        </div> {{-- End max-w-7xl --}}
    </div> {{-- End py-12 --}}

</x-app-layout>