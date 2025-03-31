<div class="admin-sidebar bg-white shadow-md h-full overflow-y-auto w-64 fixed left-0 top-0 bottom-0 pt-16 z-10 transform transition-transform duration-300 md:translate-x-0">
    <div class="px-4 py-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">{{ __('Administration') }}</h2>
        <p class="text-sm text-gray-500">{{ __('School ERP System') }}</p>
    </div>

    <nav class="mt-6 px-4 pb-16">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                    <span>{{ __('Dashboard') }}</span>
                </a>
            </li>

            <!-- Facility Management Section -->
            <li class="mt-6">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Facilities') }}
                </h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('rooms.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('rooms.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-door-open w-5 h-5 mr-3"></i>
                            <span>{{ __('Rooms') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('maintenance-requests.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('maintenance-requests.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-tools w-5 h-5 mr-3"></i>
                            <span>{{ __('Maintenance') }}</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- IT Management Section -->
            <li class="mt-6">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('IT') }}
                </h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('it-equipment.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('it-equipment.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-laptop w-5 h-5 mr-3"></i>
                            <span>{{ __('Equipment') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('software.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('software.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-code w-5 h-5 mr-3"></i>
                            <span>{{ __('Software') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('support-tickets.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('support-tickets.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-ticket-alt w-5 h-5 mr-3"></i>
                            <span>{{ __('Support Tickets') }}</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Event Management Section -->
            <li class="mt-6">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Events') }}
                </h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('events.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('events.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-calendar-alt w-5 h-5 mr-3"></i>
                            <span>{{ __('Events') }}</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Teaching Management Section -->
            <li class="mt-6">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Teaching') }}
                </h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('substitutions.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('substitutions.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-exchange-alt w-5 h-5 mr-3"></i>
                            <span>{{ __('Substitutions') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teacher-attendance.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('teacher-attendance.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-clipboard-check w-5 h-5 mr-3"></i>
                            <span>{{ __('Attendance') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</div>

            <!-- Asset Management Section -->
            <li class="mt-6">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Asset Management') }}
                </h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('assets.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('assets.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-desktop w-5 h-5 mr-3"></i>
                            <span>{{ __('Assets') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('asset-allocations.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('asset-allocations.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-exchange-alt w-5 h-5 mr-3"></i>
                            <span>{{ __('Allocations') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('asset-maintenance.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('asset-maintenance.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-tools w-5 h-5 mr-3"></i>
                            <span>{{ __('Maintenance') }}</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Library Management Section -->
            <li class="mt-6">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Library') }}
                </h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('books.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('books.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-book w-5 h-5 mr-3"></i>
                            <span>{{ __('Books') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('book-circulations.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('book-circulations.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-exchange-alt w-5 h-5 mr-3"></i>
                            <span>{{ __('Circulation') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('library-members.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('library-members.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-users w-5 h-5 mr-3"></i>
                            <span>{{ __('Members') }}</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Transport Management Section -->
            <li class="mt-6">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Transport') }}
                </h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('vehicles.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('vehicles.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-bus w-5 h-5 mr-3"></i>
                            <span>{{ __('Vehicles') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('drivers.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('drivers.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-id-card w-5 h-5 mr-3"></i>
                            <span>{{ __('Drivers') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('routes.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('routes.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-route w-5 h-5 mr-3"></i>
                            <span>{{ __('Routes') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('trips.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('trips.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-map-marked-alt w-5 h-5 mr-3"></i>
                            <span>{{ __('Trips') }}</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- HR Management Section -->
            <li class="mt-6">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('HR') }}
                </h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('staff.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('staff.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-user-tie w-5 h-5 mr-3"></i>
                            <span>{{ __('Staff') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff-attendance.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('staff-attendance.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-calendar-check w-5 h-5 mr-3"></i>
                            <span>{{ __('Attendance') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('performance.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('performance.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-chart-line w-5 h-5 mr-3"></i>
                            <span>{{ __('Performance') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('recruitment.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('recruitment.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-user-plus w-5 h-5 mr-3"></i>
                            <span>{{ __('Recruitment') }}</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Leave Management Section -->
            <li class="mt-6">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Leave') }}
                </h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('leave-applications.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('leave-applications.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-file-alt w-5 h-5 mr-3"></i>
                            <span>{{ __('Applications') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('leave-balances.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('leave-balances.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-calculator w-5 h-5 mr-3"></i>
                            <span>{{ __('Balances') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('leave-policies.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('leave-policies.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-clipboard-list w-5 h-5 mr-3"></i>
                            <span>{{ __('Policies') }}</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Finance Management Section -->
            <li class="mt-6">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Finance') }}
                </h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('expenses.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('expenses.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-money-bill-alt w-5 h-5 mr-3"></i>
                            <span>{{ __('Expenses') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('payroll.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('payroll.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-credit-card w-5 h-5 mr-3"></i>
                            <span>{{ __('Payroll') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('salaries.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('salaries.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-dollar-sign w-5 h-5 mr-3"></i>
                            <span>{{ __('Salaries') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('loans.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('loans.*') ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100' }}">
                            <i class="fas fa-hand-holding-usd w-5 h-5 mr-3"></i>
                            <span>{{ __('Loans') }}</span>
                        </a>
                    </li>
                </ul>
            </li>