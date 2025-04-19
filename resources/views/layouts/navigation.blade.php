<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}"> {{-- Assuming 'dashboard' is your main overall dashboard --}}
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Student Management Navigation Links -->
                    <x-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                        {{ __('Student Management') }}
                    </x-nav-link>

                    <!-- Hostel Management Link -->
                    <x-nav-link :href="route('student.hostel.dashboard')" :active="request()->routeIs('student.hostel.*')">
                        {{ __('Hostel Management') }}
                    </x-nav-link>

                    <!-- Teacher Management Link -->
                    <x-nav-link :href="route('teacher.dashboard')" :active="request()->routeIs('teacher.*')">
                        {{ __('Teacher Management') }}
                    </x-nav-link>

                    {{-- *** NEW: Accounting Dropdown *** --}}
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48"> {{-- Changed align to left --}}
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Accounting') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('accounting.dashboard')" :active="request()->routeIs('accounting.dashboard')">
                                    {{ __('Accounting Dashboard') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('accounting.invoices.index')" :active="request()->routeIs('accounting.invoices.*')">
                                    {{ __('Invoices') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('accounting.payments.index')" :active="request()->routeIs('accounting.payments.*')">
                                    {{ __('Payments') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('accounting.contacts.index')" :active="request()->routeIs('accounting.contacts.*')">
                                    {{ __('Contacts') }}
                                </x-dropdown-link>
                                <div class="border-t border-gray-200"></div>
                                <x-dropdown-link :href="route('accounting.sms.send')" :active="request()->routeIs('accounting.sms.send')">
                                    {{ __('Send SMS') }}
                                </x-dropdown-link>
                                {{-- *** ADDED SMS GATEWAY LINK *** --}}
                                <x-dropdown-link :href="route('accounting.sms-gateways.index')" :active="request()->routeIs('accounting.sms-gateways.*')">
                                    {{ __('SMS Gateways') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('accounting.sms.templates')" :active="request()->routeIs('accounting.sms.templates*')">
                                    {{ __('SMS Templates') }}
                                </x-dropdown-link>
                                <div class="border-t border-gray-200"></div>
                                 <x-dropdown-link :href="route('accounting.accounts.index')" :active="request()->routeIs('accounting.accounts.*')">
                                    {{ __('Chart of Accounts') }}
                                 </x-dropdown-link>
                                {{-- Add other accounting links as needed --}}
                            </x-slot>
                        </x-dropdown>
                    </div>
                    {{-- *** END: Accounting Dropdown *** --}}


                    <!-- Administration Management Link -->
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                        {{ __('Administration') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                 {{-- ... User profile dropdown (Keep as is) ... --}}
                 <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <!-- Student Management Responsive Link -->
            <x-responsive-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                {{ __('Student Management') }}
            </x-responsive-nav-link>

            <!-- Hostel Management Responsive Link -->
            <x-responsive-nav-link :href="route('student.hostel.dashboard')" :active="request()->routeIs('student.hostel.*')">
                {{ __('Hostel Management') }}
            </x-responsive-nav-link>

            <!-- Teacher Management Responsive Link -->
            <x-responsive-nav-link :href="route('teacher.dashboard')" :active="request()->routeIs('teacher.*')">
                {{ __('Teacher Management') }}
            </x-responsive-nav-link>

            {{-- *** NEW: Accounting Section (Mobile) *** --}}
            <div class="border-t border-gray-200 pt-2 mt-2">
                 <div class="px-4 font-medium text-base text-gray-800">{{ __('Accounting') }}</div>
                 <div class="mt-1 space-y-1">
                     <x-responsive-nav-link :href="route('accounting.dashboard')" :active="request()->routeIs('accounting.dashboard')">
                         {{ __('Accounting Dashboard') }}
                     </x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('accounting.invoices.index')" :active="request()->routeIs('accounting.invoices.*')">
                         {{ __('Invoices') }}
                     </x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('accounting.payments.index')" :active="request()->routeIs('accounting.payments.*')">
                         {{ __('Payments') }}
                     </x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('accounting.contacts.index')" :active="request()->routeIs('accounting.contacts.*')">
                         {{ __('Contacts') }}
                     </x-responsive-nav-link>
                      <x-responsive-nav-link :href="route('accounting.sms.send')" :active="request()->routeIs('accounting.sms.send')">
                        {{ __('Send SMS') }}
                     </x-responsive-nav-link>
                     {{-- *** ADDED SMS GATEWAY LINK (Mobile) *** --}}
                     <x-responsive-nav-link :href="route('accounting.sms-gateways.index')" :active="request()->routeIs('accounting.sms-gateways.*')">
                         {{ __('SMS Gateways') }}
                     </x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('accounting.sms.templates')" :active="request()->routeIs('accounting.sms.templates*')">
                         {{ __('SMS Templates') }}
                     </x-responsive-nav-link>
                      <x-responsive-nav-link :href="route('accounting.accounts.index')" :active="request()->routeIs('accounting.accounts.*')">
                        {{ __('Chart of Accounts') }}
                     </x-responsive-nav-link>
                     {{-- Add other accounting links --}}
                 </div>
             </div>
             {{-- *** END: Accounting Section (Mobile) *** --}}


            <!-- Administration Management Responsive Link -->
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                {{ __('Administration') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            {{-- ... User profile section (Keep as is) ... --}}
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>