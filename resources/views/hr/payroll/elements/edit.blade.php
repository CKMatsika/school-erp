```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Payroll Element:') }} {{ $element->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Controller must pass $element --}}
                <form method="POST" action="{{ route('hr.payroll.elements.update', $element) }}">
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        @include('hr.payroll.elements._form')
                    </div>
                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                         <a href="{{ route('hr.payroll.elements.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                        <x-primary-button type="submit">Update Element</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>