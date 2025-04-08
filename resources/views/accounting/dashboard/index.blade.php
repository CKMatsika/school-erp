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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <!-- New Invoice -->
                        <a href="{{ route('accounting.invoices.create') }}" class="bg-indigo-50 p-4 rounded-lg border border-indigo-200 text-center hover:bg-indigo-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-sm font-medium text-indigo-700">New Invoice</span>
                        </a>
                        <!-- Record Payment -->
                        <a href="{{ route('accounting.payments.create') }}" class="bg-green-50 p-4 rounded-lg border border-green-200 text-center hover:bg-green-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="text-sm font-medium text-green-700">Record Payment</span>
                        </a>
                        <!-- Journal Entry -->
                        <a href="{{ route('accounting.journals.create') }}" class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 text-center hover:bg-yellow-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span class="text-sm font-medium text-yellow-700">Journal Entry</span>
                        </a>
                         <!-- Send SMS -->
                        <a href="{{ route('accounting.sms.send') }}" class="bg-orange-50 p-4 rounded-lg border border-orange-200 text-center hover:bg-orange-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            <span class="text-sm font-medium text-orange-700">Send SMS</span>
                        </a>
                        <!-- Reports -->
                        <a href="{{ route('accounting.reports.index') }}" class="bg-blue-50 p-4 rounded-lg border border-blue-200 text-center hover:bg-blue-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="text-sm font-medium text-blue-700">Reports</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Procurement & Inventory Management -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Procurement & Inventory</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <!-- Procurement Dashboard -->
                        <a href="{{ route('accounting.procurement.index') }}" class="bg-amber-50 p-4 rounded-lg border border-amber-200 text-center hover:bg-amber-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span class="text-sm font-medium text-amber-700">Procurement</span>
                        </a>
                        <!-- Purchase Requests -->
                        <a href="{{ route('accounting.purchase-requests.index') }}" class="bg-pink-50 p-4 rounded-lg border border-pink-200 text-center hover:bg-pink-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-pink-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-sm font-medium text-pink-700">Purchase Requests</span>
                        </a>
                        <!-- Purchase Orders -->
                        <a href="{{ route('accounting.purchase-orders.index') }}" class="bg-purple-50 p-4 rounded-lg border border-purple-200 text-center hover:bg-purple-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-sm font-medium text-purple-700">Purchase Orders</span>
                        </a>
                        <!-- Goods Receipt -->
                        <a href="{{ route('accounting.goods-receipts.index') }}" class="bg-green-50 p-4 rounded-lg border border-green-200 text-center hover:bg-green-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke
                            <svg class="w-6 h-6 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <span class="text-sm font-medium text-green-700">Goods Receipt</span>
                        </a>
                        <!-- Inventory Items -->
                        <a href="{{ route('accounting.inventory-items.index') }}" class="bg-blue-50 p-4 rounded-lg border border-blue-200 text-center hover:bg-blue-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            <span class="text-sm font-medium text-blue-700">Inventory</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Banking & Cash Management -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Banking & Cash Management</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <!-- Cashbook -->
                        <a href="{{ route('accounting.cashbook.index') }}" class="bg-emerald-50 p-4 rounded-lg border border-emerald-200 text-center hover:bg-emerald-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="text-sm font-medium text-emerald-700">Cashbook</span>
                        </a>
                        <!-- Bank Reconciliation -->
                        <a href="{{ route('accounting.bank-reconciliation.index') }}" class="bg-blue-50 p-4 rounded-lg border border-blue-200 text-center hover:bg-blue-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            <span class="text-sm font-medium text-blue-700">Bank Reconciliation</span>
                        </a>
                        <!-- Bank Transfers -->
                        <a href="{{ route('accounting.bank-transfers.index') }}" class="bg-indigo-50 p-4 rounded-lg border border-indigo-200 text-center hover:bg-indigo-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            <span class="text-sm font-medium text-indigo-700">Bank Transfers</span>
                        </a>
                        <!-- Bank Accounts -->
                        <a href="{{ route('accounting.bank-accounts.index') }}" class="bg-violet-50 p-4 rounded-lg border border-violet-200 text-center hover:bg-violet-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-violet-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span class="text-sm font-medium text-violet-700">Bank Accounts</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- ===== CONFIGURATION & SETUP SECTION ===== --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configuration & Setup</h3>
                    {{-- Adjust grid columns based on final number of items --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        {{-- Link to Academic Years --}}
                        <a href="{{ route('accounting.academic-years.index') }}" {{-- Using accounting prefix --}}
                           class="bg-purple-50 p-4 rounded-lg border border-purple-200 text-center hover:bg-purple-100 transition flex flex-col items-center justify-center">
                            {{-- Calendar Icon --}}
                            <svg class="w-6 h-6 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-sm font-medium text-purple-700">Academic Years</span>
                        </a>

                       {{-- Link to Classes --}}
                        <a href="{{ route('accounting.classes.index') }}" {{-- Using accounting prefix --}}
                           class="bg-orange-50 p-4 rounded-lg border border-orange-200 text-center hover:bg-orange-100 transition flex flex-col items-center justify-center">
                           {{-- Building/School Icon --}}
                           <svg class="w-6 h-6 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                           <span class="text-sm font-medium text-orange-700">Manage Classes</span>
                        </a>

                        {{-- Link to Fee Structures --}}
                        <a href="{{ route('accounting.fee-structures.index') }}"
                           class="bg-lime-50 p-4 rounded-lg border border-lime-200 text-center hover:bg-lime-100 transition flex flex-col items-center justify-center">
                           {{-- List/Document Icon --}}
                           <svg class="w-6 h-6 text-lime-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                           <span class="text-sm font-medium text-lime-700">Fee Structures</span>
                        </a>

                        {{-- Link to Chart of Accounts --}}
                         <a href="{{ route('accounting.accounts.index') }}"
                            class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-center hover:bg-gray-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            <span class="text-sm font-medium text-gray-700">Chart of Accounts</span>
                        </a>

                        {{-- Link to Account Types --}}
                        <a href="{{ route('accounting.account-types.index') }}"
                           class="bg-gray-100 p-4 rounded-lg border border-gray-200 text-center hover:bg-gray-200 transition flex flex-col items-center justify-center">
                            {{-- Settings/Cog Icon --}}
                            <svg class="w-6 h-6 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="text-sm font-medium text-gray-700">Account Types</span>
                        </a>

                        {{-- Link to Payment Methods --}}
                        <a href="{{ route('accounting.payment-methods.index') }}" {{-- Assuming route name --}}
                           class="bg-teal-50 p-4 rounded-lg border border-teal-200 text-center hover:bg-teal-100 transition flex flex-col items-center justify-center">
                           {{-- Credit Card Icon --}}
                           <svg class="w-6 h-6 text-teal-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                           <span class="text-sm font-medium text-teal-700">Payment Methods</span>
                        </a>

                        {{-- Link to Suppliers --}}
                        <a href="{{ route('accounting.suppliers.index') }}"
                           class="bg-amber-50 p-4 rounded-lg border border-amber-200 text-center hover:bg-amber-100 transition flex flex-col items-center justify-center">
                           {{-- Building Icon --}}
                           <svg class="w-6 h-6 text-amber-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                           <span class="text-sm font-medium text-amber-700">Suppliers</span>
                        </a>

                        {{-- Link to Item Categories --}}
                        <a href="{{ route('accounting.item-categories.index') }}"
                           class="bg-fuchsia-50 p-4 rounded-lg border border-fuchsia-200 text-center hover:bg-fuchsia-100 transition flex flex-col items-center justify-center">
                           {{-- Tag Icon --}}
                           <svg class="w-6 h-6 text-fuchsia-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                           <span class="text-sm font-medium text-fuchsia-700">Item Categories</span>
                        </a>
                    </div>
                </div>
            </div>
            {{-- ===== END OF CONFIGURATION SECTION ===== --}}

            <!-- Student Financial Management -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Student Financial Management</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                         {{-- Manage Contacts Link --}}
                        <a href="{{ route('accounting.contacts.index') }}" class="bg-sky-50 p-4 rounded-lg border border-sky-200 text-center hover:bg-sky-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-sky-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.08-1.28-.23-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.08-1.28.23-1.857m0 0a5.002 5.002 0 019.542 0M12 6a3 3 0 110-6 3 3 0 010 6z"></path></svg>
                            <span class="text-sm font-medium text-sky-700">Manage Contacts</span>
                        </a>
                        {{-- Bulk Invoice --}}
                        <a href="{{ route('accounting.bulk-invoice.index') }}" class="bg-blue-50 p-4 rounded-lg border border-blue-200 text-center hover:bg-blue-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span class="text-sm font-medium text-blue-700">Bulk Invoice</span>
                        </a>
                        {{-- Student Ledger --}}
                        <a href="{{ route('accounting.student-ledger.index') }}" class="bg-indigo-50 p-4 rounded-lg border border-indigo-200 text-center hover:bg-indigo-100 transition flex flex-col items-center justify-center">
                           <svg class="w-6 h-6 text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            <span class="text-sm font-medium text-indigo-700">Student Ledger</span>
                        </a>
                        {{-- Payment Plans --}}
                        <a href="{{ route('accounting.payment-plans.index') }}" class="bg-purple-50 p-4 rounded-lg border border-purple-200 text-center hover:bg-purple-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="text-sm font-medium text-purple-700">Payment Plans</span>
                        </a>
                        {{-- Age Analysis --}}
                        <a href="{{ route('accounting.student-debts.age-analysis') }}" class="bg-pink-50 p-4 rounded-lg border border-pink-200 text-center hover:bg-pink-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-pink-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-sm font-medium text-pink-700">Age Analysis</span>
                        </a>
                    </div>
                </div>
            </div>

             <!-- Budget & Management -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Budget & Management</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <!-- Budgets -->
                        <a href="{{ route('accounting.budgets.index') }}" class="bg-green-50 p-4 rounded-lg border border-green-200 text-center hover:bg-green-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="text-sm font-medium text-green-700">Budgets</span>
                        </a>
                        <!-- CapEx Projects -->
                        <a href="{{ route('accounting.capex.index') }}" class="bg-teal-50 p-4 rounded-lg border border-teal-200 text-center hover:bg-teal-100 transition flex flex-col items-center justify-center">
                           <svg class="w-6 h-6 text-teal-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span class="text-sm font-medium text-teal-700">CapEx Projects</span>
                        </a>
                        <!-- Budget Reports -->
                        <a href="{{ route('accounting.reports.budget-vs-actual') }}" class="bg-cyan-50 p-4 rounded-lg border border-cyan-200 text-center hover:bg-cyan-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-cyan-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                            <span class="text-sm font-medium text-cyan-700">Budget Reports</span>
                        </a>
                        <!-- Tenders -->
                        <a href="{{ route('accounting.tenders.index') }}" class="bg-rose-50 p-4 rounded-lg border border-rose-200 text-center hover:bg-rose-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-rose-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-sm font-medium text-rose-700">Tenders</span>
                        </a>
                        <!-- Contracts -->
                        <a href="{{ route('accounting.procurement-contracts.index') }}" class="bg-amber-50 p-4 rounded-lg border border-amber-200 text-center hover:bg-amber-100 transition flex flex-col items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-sm font-medium text-amber-700">Contracts</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                        {{-- <a href="#" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a> --}}
                    </div>

                    @if((isset($recentInvoices) && $recentInvoices->count() > 0) || (isset($recentPayments) && $recentPayments->count() > 0))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                {{-- Table Head --}}
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                {{-- Table Body --}}
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {{-- Invoices Loop --}}
                                    @isset($recentInvoices)
                                        @foreach($recentInvoices as $invoice)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($invoice->issue_date)->format('M d, Y') ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Invoice</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                    <a href="{{ route('accounting.invoices.show', $invoice) }}">{{ $invoice->invoice_number ?? $invoice->id }}</a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->contact->name ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($invoice->total ?? 0, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                     <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
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

                                     {{-- Payments Loop --}}
                                    @isset($recentPayments)
                                        @foreach($recentPayments as $payment)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($payment->payment_date)->format('M d, Y') ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Payment</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                    <a href="{{ route('accounting.payments.show', $payment) }}">{{ $payment->payment_number ?? $payment->id }}</a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->contact->name ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">{{ number_format($payment->amount ?? 0, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
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
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <p class="mt-2 text-sm text-gray-500">No recent transactions to display</p>
                        </div>
                    @endif
                </div>
            </div>

        </div> {{-- End max-w-7xl --}}
    </div> {{-- End py-12 --}}
</x-app-layout>