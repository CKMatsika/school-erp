<div class="bg-white shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                {{-- Desktop Menu --}}
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    {{-- Dashboard --}}
                    <a href="{{ route('accounting.dashboard') }}" class="{{ request()->routeIs('accounting.dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Dashboard
                    </a>

                    {{-- Invoices --}}
                    <a href="{{ route('accounting.invoices.index') }}" class="{{ request()->routeIs('accounting.invoices*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Invoices
                    </a>

                    {{-- Payments --}}
                    <a href="{{ route('accounting.payments.index') }}" class="{{ request()->routeIs('accounting.payments*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Payments
                    </a>

                    {{-- ===== ADDED CONTACTS LINK HERE ===== --}}
                    <a href="{{ route('accounting.contacts.index') }}" class="{{ request()->routeIs('accounting.contacts*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Contacts
                    </a>
                    {{-- ===== END OF ADDED LINK ===== --}}

                    {{-- Chart of Accounts --}}
                    <a href="{{ route('accounting.accounts.index') }}" class="{{ request()->routeIs('accounting.accounts*') || request()->routeIs('accounting.account-types*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Chart of Accounts {{-- Kept CoA main link, Account Types might be a sub-menu or link elsewhere --}}
                    </a>

                    {{-- Reports --}}
                    <a href="{{ route('accounting.reports.index') }}" class="{{ request()->routeIs('accounting.reports*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Reports
                    </a>
                </div>
            </div>
            {{-- Hamburger menu button for mobile (if applicable, often controlled by main layout) --}}
            {{-- <div class="-mr-2 flex items-center sm:hidden">
                <button @click="mobileMenuOpen = ! mobileMenuOpen" class="...">
                    ... SVG icon ...
                </button>
            </div> --}}
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state -->
    {{-- Assuming mobileMenuOpen is handled in the main layout or via AlpineJS --}}
    <div class="sm:hidden" id="mobile-menu" x-data="{ mobileMenuOpen: false }" x-show="mobileMenuOpen">
        <div class="pt-2 pb-3 space-y-1">
             {{-- Dashboard --}}
            <a href="{{ route('accounting.dashboard') }}" class="{{ request()->routeIs('accounting.dashboard') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Dashboard
            </a>

            {{-- Invoices --}}
            <a href="{{ route('accounting.invoices.index') }}" class="{{ request()->routeIs('accounting.invoices*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Invoices
            </a>

            {{-- Payments --}}
            <a href="{{ route('accounting.payments.index') }}" class="{{ request()->routeIs('accounting.payments*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Payments
            </a>

            {{-- ===== ADDED CONTACTS LINK HERE (MOBILE) ===== --}}
            <a href="{{ route('accounting.contacts.index') }}" class="{{ request()->routeIs('accounting.contacts*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Contacts
            </a>
            {{-- ===== END OF ADDED LINK (MOBILE) ===== --}}

             {{-- Chart of Accounts --}}
            <a href="{{ route('accounting.accounts.index') }}" class="{{ request()->routeIs('accounting.accounts*') || request()->routeIs('accounting.account-types*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Chart of Accounts
            </a>

            {{-- Reports --}}
            <a href="{{ route('accounting.reports.index') }}" class="{{ request()->routeIs('accounting.reports*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Reports
            </a>
        </div>
    </div>
</div>