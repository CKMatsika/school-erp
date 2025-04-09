<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Procurement Contract') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Ensure controller passes necessary variables --}}
                <form method="POST" action="{{ route('accounting.procurement-contracts.store') }}" enctype="multipart/form-data"> {{-- Added enctype --}}
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        @include('accounting.procurement-contracts._form') {{-- Include the partial form --}}
                    </div>

                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('accounting.procurement-contracts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __('Cancel') }}
                        </a>

                        <x-primary-button type="submit">
                            {{ __('Save Contract') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>