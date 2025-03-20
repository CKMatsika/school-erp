<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Role') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Role Information</h3>
                            
                            <!-- Name -->
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Is System -->
                            <div class="mb-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="is_system" name="is_system" type="checkbox" value="1" {{ old('is_system') ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_system" class="font-medium text-gray-700">System Role</label>
                                        <p class="text-gray-500">System roles are available to all schools</p>
                                    </div>
                                </div>
                                @error('is_system')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
                            
                            @if($permissions->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($permissions as $moduleKey => $modulePermissions)
                                        <div class="border rounded-lg p-4">
                                            <h4 class="font-medium mb-2 capitalize">{{ $moduleKey }} Module</h4>
                                            
                                            @foreach($modulePermissions as $permission)
                                                <div class="mb-2">
                                                    <div class="flex items-center">
                                                        <input id="permission_{{ $permission->id }}" name="permissions[]" type="checkbox" value="{{ $permission->id }}"
                                                            {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                        <label for="permission_{{ $permission->id }}" class="ml-2 text-sm text-gray-700">
                                                            {{ $permission->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">No permissions found. Please create permissions first.</p>
                            @endif
                            
                            @error('permissions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('roles.index') }}" class="bg-gray-200 py-2 px-4 border border-gray-200 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Create Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>