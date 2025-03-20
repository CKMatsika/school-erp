<!-- resources/views/core/modules/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Module Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-medium mb-4">Available Modules</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @forelse ($modules as $module)
                            <div class="bg-white border rounded-lg shadow-sm">
                                <div class="flex justify-between items-center p-4 border-b">
                                    <span class="font-medium">{{ $module->name }}</span>
                                    
                                    <form action="{{ route('modules.toggle', $module->key) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded text-sm {{ isset($module->is_active_for_school) && $module->is_active_for_school ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                            {{ isset($module->is_active_for_school) && $module->is_active_for_school ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </div>
                                <div class="p-4">
                                    <p class="text-gray-700">{{ $module->description }}</p>
                                    
                                    @if(isset($module->dependencies) && is_array($module->dependencies) && count($module->dependencies) > 0)
                                        <p class="mt-2 text-sm"><span class="font-semibold">Dependencies:</span> 
                                            {{ implode(', ', $module->dependencies) }}
                                        </p>
                                    @endif
                                    
                                    <p class="mt-2 text-xs text-gray-500">Version: {{ $module->version }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center text-gray-500 py-8">
                                No modules available.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>