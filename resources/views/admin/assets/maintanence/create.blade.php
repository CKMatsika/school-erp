<x-admin-layout>
    <x-slot name="title">{{ __('Create Maintenance Record') }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Maintenance Record') }}
            </h2>
            
            <x-secondary-button onclick="window.location.href='{{ route('asset-maintenance.index') }}'">
                <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Maintenance Records') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            @include('administration.asset-maintenance.partials.form')
        </div>
    </div>
</x-admin-layout>