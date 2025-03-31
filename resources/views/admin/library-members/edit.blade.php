<x-admin-layout>
    <x-slot name="title">{{ __('Edit Library Member') }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Library Member') }}: {{ $member->name }}
            </h2>
            
            <div class="flex space-x-3">
                <x-secondary-button onclick="window.location.href='{{ route('library-members.show', $member) }}'">
                    <i class="fa fa-eye mr-1"></i> {{ __('View Details') }}
                </x-secondary-button>
                
                <x-secondary-button onclick="window.location.href='{{ route('library-members.index') }}'">
                    <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Members') }}
                </x-secondary-button>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            @include('administration.library-members.partials.form')
        </div>
    </div>
</x-admin-layout>