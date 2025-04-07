<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Academic Year') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <strong class="font-bold">Please correct the errors:</strong>
                            <ul> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('accounting.academic-years.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 gap-6">
                            {{-- Name --}}
                            <div>
                                <x-input-label for="name" :value="__('Year Name * (e.g., 2024-2025)')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            {{-- Start Date --}}
                             <div>
                                <x-input-label for="start_date" :value="__('Start Date *')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>

                            {{-- End Date --}}
                            <div>
                                <x-input-label for="end_date" :value="__('End Date *')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" required />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>

                            {{-- Is Current Checkbox --}}
                            <div class="block mt-4">
                                <label for="is_current" class="inline-flex items-center">
                                    <input id="is_current" type="checkbox" name="is_current" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ old('is_current') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Set as Current Academic Year') }}</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1">Note: Setting this will automatically unset any other year marked as current.</p>
                                <x-input-error :messages="$errors->get('is_current')" class="mt-2" />
                            </div>
                        </div>

                         <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('accounting.academic-years.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Create Academic Year') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>