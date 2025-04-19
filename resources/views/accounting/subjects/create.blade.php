{{-- resources/views/accounting/subjects/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New Subject') }}
            </h2>
             <a href="{{ route('accounting.subjects.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Back to List') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
             {{-- Display Validation Errors --}}
             @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Whoops!</strong>
                    <span class="block sm:inline">Please correct the errors below.</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('accounting.subjects.store') }}">
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        {{-- Include the form partial --}}
                        @include('accounting.subjects._form')
                    </div>
                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('accounting.subjects.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Create Subject') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>