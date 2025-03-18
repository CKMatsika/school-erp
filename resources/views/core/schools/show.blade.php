<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('School Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ $school->name }}</h3>
                        <div>
                            <a href="{{ route('schools.edit', $school->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                <i class="fas fa-edit mr-2"></i> {{ __('Edit') }}
                            </a>
                            <a href="{{ route('schools.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <i class="fas fa-arrow-left mr-2"></i> {{ __('Back') }}
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- School Information -->
                        <div class="col-span-1 bg-white p-4 rounded-md shadow">
                            <h4 class="text-md font-medium text-gray-900 mb-4 border-b pb-2">School Information</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">School Name</span>
                                    <span class="block mt-1 text-md text-gray-900">{{ $school->name }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">School Code</span>
                                    <span class="block mt-1 text-md text-gray-900">{{ $school->code }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Status</span>
                                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $school->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $school->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="col-span-1 bg-white p-4 rounded-md shadow">
                            <h4 class="text-md font-medium text-gray-900 mb-4 border-b pb-2">Contact Information</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Phone</span>
                                    <span class="block mt-1 text-md text-gray-900">{{ $school->phone ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Email</span>
                                    <span class="block mt-1 text-md text-gray-900">{{ $school->email ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Website</span>
                                    <span class="block mt-1 text-md text-gray-900">
                                        @if($school->website)
                                            <a href="{{ $school->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $school->website }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="col-span-1 bg-white p-4 rounded-md shadow">
                            <h4 class="text-md font-medium text-gray-900 mb-4 border-b pb-2">Location Information</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Address</span>
                                    <span class="block mt-1 text-md text-gray-900">{{ $school->address ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">City, State, Country</span>
                                    <span class="block mt-1 text-md text-gray-900">
                                        {{ $school->city ?? '' }}
                                        {{ $school->state ? ', ' . $school->state : '' }}
                                        {{ $school->country ? ', ' . $school->country : '' }}
                                        {{ !$school->city && !$school->state && !$school->country ? 'N/A' : '' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Postal Code</span>
                                    <span class="block mt-1 text-md text-gray-900">{{ $school->postal_code ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Subscription Information -->
                        <div class="col-span-1 bg-white p-4 rounded-md shadow">
                            <h4 class="text-md font-medium text-gray-900 mb-4 border-b pb-2">Subscription Information</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Subscription Start</span>
                                    <span class="block mt-1 text-md text-gray-900">
                                        {{ $school->subscription_start ? $school->subscription_start->format('F d, Y') : 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Subscription End</span>
                                    <span class="block mt-1 text-md text-gray-900">
                                        {{ $school->subscription_end ? $school->subscription_end->format('F d, Y') : 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Subscription Status</span>
                                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $school->isSubscriptionActive() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $school->isSubscriptionActive() ? 'Active' : 'Expired' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module Management -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Enabled Modules</h3>
                        <div class="bg-white p-4 rounded-md shadow">
                            @if($school->modules->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach($school->modules as $module)
                                        <div class="p-4 border rounded-md {{ $module->pivot->is_active ? 'border-green-500 bg-green-50' : 'border-gray-300' }}">
                                            <h4 class="font-medium text-gray-900">{{ $module->name }}</h4>
                                            <p class="text-sm text-gray-500 mt-1">{{ $module->description }}</p>
                                            <div class="mt-2 flex justify-between items-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $module->pivot->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $module->pivot->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                <form action="{{ route('modules.toggle', $module->key) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-900">
                                                        {{ $module->pivot->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">No modules associated with this school.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Users Section -->
                    <div class="mt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">School Users</h3>
                            <a href="{{ route('users.create', ['school_id' => $school->id]) }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <i class="fas fa-plus mr-1"></i> Add User
                            </a>
                        </div>
                        <div class="bg-white p-4 rounded-md shadow overflow-x-auto">
                            @if($school->users->count() > 0)
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($school->users as $user)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ ucfirst($user->user_type) }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-gray-500 italic">No users associated with this school.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>