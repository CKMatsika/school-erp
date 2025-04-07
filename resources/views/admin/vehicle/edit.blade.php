<x-admin-layout>
    <x-slot name="title">{{ __('Edit Vehicle') }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Vehicle') }} - {{ $vehicle->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-4 flex justify-between">
                        <a href="{{ route('vehicle.index') }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Vehicles') }}
                        </a>
                        
                        <a href="{{ route('vehicle.show', $vehicle) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fa fa-eye mr-1"></i> {{ __('View Vehicle Details') }}
                        </a>
                    </div>
                    
                    @include('admin.vehicle.partials.form')
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>