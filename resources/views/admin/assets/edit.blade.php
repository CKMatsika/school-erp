<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Asset') }}: {{ $asset->name }}
            </h2>
            
            <div class="flex space-x-3">
                <x-secondary-button onclick="window.location.href='{{ route('assets.show', $asset) }}'">
                    <i class="fa fa-eye mr-1"></i> {{ __('View Details') }}
                </x-secondary-button>
                
                <x-secondary-button onclick="window.location.href='{{ route('assets.index') }}'">
                    <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Assets') }}
                </x-secondary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('administration.assets.partials.form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>