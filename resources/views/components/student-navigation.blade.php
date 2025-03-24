<!-- resources/views/components/student-navigation.blade.php -->
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
    <x-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>
    
    <x-nav-link :href="route('student.students.index')" :active="request()->routeIs('student.students.*')">
        {{ __('Students') }}
    </x-nav-link>
    
    <x-nav-link :href="route('student.guardians.index')" :active="request()->routeIs('student.guardians.*')">
        {{ __('Guardians') }}
    </x-nav-link>
    
    <x-nav-link :href="route('student.applications.index')" :active="request()->routeIs('student.applications.*')">
        {{ __('Applications') }}
    </x-nav-link>
    
    <div class="hidden sm:flex sm:items-center sm:ml-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                    <div>{{ __('More') }}</div>

                    <div class="ml-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('student.enrollments.index')">
                    {{ __('Enrollments') }}
                </x-dropdown-link>
                
                <x-dropdown-link :href="route('student.application-workflow.index')">
                    {{ __('Application Workflow') }}
                </x-dropdown-link>
                
                <x-dropdown-link :href="route('student.academic-years.index')">
                    {{ __('Academic Years') }}
                </x-dropdown-link>
                
                <x-dropdown-link :href="route('student.promotions.index')">
                    {{ __('Class Promotions') }}
                </x-dropdown-link>
                
                <x-dropdown-link :href="route('student.documents.index')">
                    {{ __('Documents') }}
                </x-dropdown-link>
            </x-slot>
        </x-dropdown>
    </div>
</div>

<!-- Responsive Navigation Menu -->
<div class="sm:hidden" x-show="open">
    <div class="pt-2 pb-3 space-y-1">
        <x-responsive-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
            {{ __('Dashboard') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('student.students.index')" :active="request()->routeIs('student.students.*')">
            {{ __('Students') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('student.guardians.index')" :active="request()->routeIs('student.guardians.*')">
            {{ __('Guardians') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('student.applications.index')" :active="request()->routeIs('student.applications.*')">
            {{ __('Applications') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('student.enrollments.index')" :active="request()->routeIs('student.enrollments.*')">
            {{ __('Enrollments') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('student.application-workflow.index')" :active="request()->routeIs('student.application-workflow.*')">
            {{ __('App Workflow') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('student.academic-years.index')" :active="request()->routeIs('student.academic-years.*')">
            {{ __('Academic Years') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('student.promotions.index')" :active="request()->routeIs('student.promotions.*')">
            {{ __('Class Promotions') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('student.documents.index')" :active="request()->routeIs('student.documents.*')">
            {{ __('Documents') }}
        </x-responsive-nav-link>
    </div>
</div>