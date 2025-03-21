<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome, {{ $user->name }}!</h3>
                    @if($school)
                        <p class="text-gray-600">You are logged in to {{ $school->name }}</p>
                    @else
                        <p class="text-gray-600">You are logged in as a system administrator</p>
                    @endif
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Schools Card (Super Admin Only) -->
                @if($user->hasRole('super-admin'))
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-indigo-100 text-indigo-500">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 truncate">Total Schools</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $totalSchools ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Total Users Card -->
                @if(isset($user) && ($user->hasRole('super-admin') || $user->hasRole('school-admin')))
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 truncate">Total Users</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $totalUsers ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Active Modules Card -->
                @if($school)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-500">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 truncate">Active Modules</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ isset($activeModules) ? count($activeModules) : 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Subscription Status Card -->
                @if($school)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-{{ $school->isSubscriptionActive() ? 'green' : 'red' }}-100 text-{{ $school->isSubscriptionActive() ? 'green' : 'red' }}-500">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 truncate">Subscription Status</p>
                                    <p class="text-xl font-semibold text-{{ $school->isSubscriptionActive() ? 'green' : 'red' }}-600">
                                        {{ $school->isSubscriptionActive() ? 'Active' : 'Expired' }}
                                    </p>
                                    @if($school->subscription_end)
                                        <p class="text-xs text-gray-500">
                                            {{ $school->isSubscriptionActive() ? 'Expires: ' : 'Expired: ' }} 
                                            {{ $school->subscription_end->format('M d, Y') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- School Modules Section -->
            @if($school && isset($activeModules) && count($activeModules) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Available Modules</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($activeModules as $module)
                                <div class="bg-white p-4 rounded border border-gray-200 shadow-sm hover:shadow transition duration-150">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ $module->name }}</h4>
                                    <p class="text-sm text-gray-600 mb-3">{{ $module->description }}</p>
                                    <div class="mt-auto">
                                        @if($module->key === 'core')
                                            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <span>Access Module</span>
                                                <svg class="ml-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </a>
                                        @elseif($module->key === 'timetable')
                                            <a href="{{ route('timetables.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <span>Access Module</span>
                                                <svg class="ml-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </a>
                                        @elseif($module->key === 'accounting')
                                            <a href="{{ route('accounting.dashboard') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <span>Access Module</span>
                                                <svg class="ml-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </a>
                                        @else
                                            <a href="#" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                <span>Access Module</span>
                                                <svg class="ml-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Available Modules Section (Without School Condition) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">System Modules</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Timetable Module Card -->
                        <div class="bg-white p-4 rounded border border-gray-200 shadow-sm hover:shadow transition duration-150">
                            <h4 class="font-medium text-gray-900 mb-2">School Timetable</h4>
                            <p class="text-sm text-gray-600 mb-3">Manage school timetables, periods, classes and schedules</p>
                            <div class="mt-auto">
                                <a href="{{ route('timetables.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    <span>Access Module</span>
                                    <svg class="ml-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Accounting & Finance Module Card -->
                        <div class="bg-white p-4 rounded border border-gray-200 shadow-sm hover:shadow transition duration-150">
                            <h4 class="font-medium text-gray-900 mb-2">Accounting & Finance</h4>
                            <p class="text-sm text-gray-600 mb-3">Manage school finances, fees, expenses and financial reports</p>
                            <div class="mt-auto">
                                <a href="{{ route('accounting.dashboard') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    <span>Access Module</span>
                                    <svg class="ml-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Placeholder for future modules -->
                        <!--
                        <div class="bg-white p-4 rounded border border-gray-200 shadow-sm hover:shadow transition duration-150">
                            <h4 class="font-medium text-gray-900 mb-2">Student Management</h4>
                            <p class="text-sm text-gray-600 mb-3">Student records, attendance, performance tracking</p>
                            <div class="mt-auto">
                                <a href="#" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    <span>Access Module</span>
                                    <svg class="ml-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        -->
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="{{ route('schools.index') }}" class="flex items-center p-4 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50">
                            <div class="flex-shrink-0 bg-indigo-100 p-3 rounded-full">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Schools</p>
                                <p class="text-xs text-gray-500">Manage schools</p>
                            </div>
                        </a>

                        <a href="{{ route('users.index') }}" class="flex items-center p-4 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50">
                            <div class="flex-shrink-0 bg-blue-100 p-3 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Users</p>
                                <p class="text-xs text-gray-500">Manage users</p>
                            </div>
                        </a>

                        <a href="{{ route('roles.index') }}" class="flex items-center p-4 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50">
                            <div class="flex-shrink-0 bg-yellow-100 p-3 rounded-full">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Roles</p>
                                <p class="text-xs text-gray-500">Manage roles</p>
                            </div>
                        </a>

                        <a href="{{ route('modules.index') }}" class="flex items-center p-4 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50">
                            <div class="flex-shrink-0 bg-green-100 p-3 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Modules</p>
                                <p class="text-xs text-gray-500">Manage modules</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>