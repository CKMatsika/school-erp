<x-admin-layout>
    <x-slot name="title">{{ __('Dashboard') }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Administration Dashboard') }}
        </h2>
    </x-slot>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Assets Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                        <i class="fas fa-desktop text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Total Assets') }}</p>
                        <p class="text-2xl font-semibold">{{ $totalAssets }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                        <i class="fas fa-user-tie text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Total Staff') }}</p>
                        <p class="text-2xl font-semibold">{{ $totalStaff }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                        <i class="fas fa-book text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Total Books') }}</p>
                        <p class="text-2xl font-semibold">{{ $totalBooks }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicles Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                        <i class="fas fa-bus text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Total Vehicles') }}</p>
                        <p class="text-2xl font-semibold">{{ $totalVehicles }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Module Access Cards -->
    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Quick Access') }}</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <!-- Asset Management -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <a href="{{ route('admin.assets.index') }}" class="block">
                <div class="p-6 bg-white">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                            <i class="fas fa-desktop text-xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">{{ __('Asset Management') }}</h4>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('Manage school assets, allocations, and maintenance records.') }}</p>
                </div>
            </a>
        </div>

        <!-- Library Management -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <a href="{{ route('admin.books.index') }}" class="block">
                <div class="p-6 bg-white">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                            <i class="fas fa-book text-xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">{{ __('Library Management') }}</h4>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('Manage books, circulation, and library members.') }}</p>
                </div>
            </a>
        </div>

        <!-- Transport Management -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <a href="{{ route('admin.vehicles.index') }}" class="block">
                <div class="p-6 bg-white">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                            <i class="fas fa-bus text-xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">{{ __('Transport Management') }}</h4>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('Manage vehicles, drivers, routes, and trips.') }}</p>
                </div>
            </a>
        </div>

        <!-- HR Management -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <a href="{{ route('admin.staff.index') }}" class="block">
                <div class="p-6 bg-white">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                            <i class="fas fa-user-tie text-xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">{{ __('HR Management') }}</h4>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('Manage staff, attendance, performance and recruitment.') }}</p>
                </div>
            </a>
        </div>

        <!-- Leave Management -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <a href="{{ route('admin.leave-applications.index') }}" class="block">
                <div class="p-6 bg-white">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                            <i class="fas fa-calendar-alt text-xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">{{ __('Leave Management') }}</h4>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('Manage leave applications, balances, and policies.') }}</p>
                </div>
            </a>
        </div>

        <!-- Finance Management -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <a href="{{ route('admin.expenses.index') }}" class="block">
                <div class="p-6 bg-white">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-indigo-100 text-indigo-500 mr-4">
                            <i class="fas fa-money-bill-alt text-xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">{{ __('Finance Management') }}</h4>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('Manage expenses, payroll, salaries, and loans.') }}</p>
                </div>
            </a>
        </div>

        <!-- Facility Management -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <a href="{{ route('admin.rooms.index') }}" class="block">
                <div class="p-6 bg-white">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-gray-100 text-gray-500 mr-4">
                            <i class="fas fa-building text-xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">{{ __('Facility Management') }}</h4>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('Manage rooms and maintenance requests.') }}</p>
                </div>
            </a>
        </div>

        <!-- IT Management -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
            <a href="{{ route('admin.it-equipment.index') }}" class="block">
                <div class="p-6 bg-white">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                            <i class="fas fa-laptop text-xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">{{ __('IT Management') }}</h4>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('Manage IT equipment, software, and support tickets.') }}</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activities & Tasks Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <!-- Recent Activities -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Recent Activities') }}</h3>
                
                <div class="space-y-4">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                <i class="fas {{ $activity->icon }} text-gray-500"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">{{ $activity->description }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $activity->user->name }} &bull; {{ $activity->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">{{ __('No recent activities') }}</p>
                    @endforelse
                </div>
                
                @if($recentActivities->count() > 0)
                    <div class="mt-4 text-center">
                        <a href="{{ route('activity-logs.index') }}" class="text-sm text-blue-600 hover:text-blue-900">
                            {{ __('View All Activities') }} &rarr;
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pending Tasks -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Pending Tasks') }}</h3>
                
                <div class="space-y-4">
                    @forelse($pendingTasks as $task)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                <i class="fas fa-tasks text-yellow-500"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ __('Due') }}: {{ $task->due_date->format('M d, Y') }} &bull; 
                                    {{ __('Priority') }}: <span class="font-medium {{ $task->getPriorityClass() }}">{{ $task->priority }}</span>
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">{{ __('No pending tasks') }}</p>
                    @endforelse
                </div>
                
                @if($pendingTasks->count() > 0)
                    <div class="mt-4 text-center">
                        <a href="{{ route('tasks.index') }}" class="text-sm text-blue-600 hover:text-blue-900">
                            {{ __('View All Tasks') }} &rarr;
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>