<x-admin-layout>
    <x-slot name="title">{{ __('Issue Book') }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Issue Book') }}
            </h2>
            
            <x-secondary-button onclick="window.location.href='{{ route('book-circulations.index') }}'">
                <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Circulations') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            @include('administration.book-circulations.partials.form')
        </div>
    </div>
</x-admin-layout>