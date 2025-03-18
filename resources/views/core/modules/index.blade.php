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
                        <div class="alert alert-success mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-medium mb-4">Available Modules</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($modules as $module)
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>{{ $module->name }}</span>
                                    
                                    <div class="form-check form-switch">
                                        <form action="{{ route('modules.toggle', $module->key) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn {{ $module->is_active_for_school ? 'btn-success' : 'btn-secondary' }}">
                                                {{ $module->is_active_for_school ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p>{{ $module->description }}</p>
                                    
                                    @if ($module->dependencies)
                                        <p class="mt-2"><strong>Dependencies:</strong> 
                                            {{ implode(', ', $module->dependencies) }}
                                        </p>
                                    @endif
                                    
                                    <p class="mt-2"><small>Version: {{ $module->version }}</small></p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>