<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New School') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
             {{-- Display Validation Errors --}}
             <x-auth-validation-errors class="mb-4" :errors="$errors" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 {{-- Ensure enctype is present for file uploads --}}
                <form method="POST" action="{{ route('schools.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">

                        <h3 class="text-lg font-medium text-gray-900">School Details</h3>

                        {{-- Include the form partial --}}
                        @include('schools._form')

                    </div>

                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                         <a href="{{ route('schools.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Create School') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>