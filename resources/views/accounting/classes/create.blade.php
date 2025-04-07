<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Class') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     @if ($errors->any()) {{-- Error Display --}} @endif

                    <form method="POST" action="{{ route('accounting.classes.store') }}">
                        @csrf
                         <div class="grid grid-cols-1 gap-6">
                            {{-- Name --}}
                            <div>
                                <x-input-label for="name" :value="__('Class Name * (e.g., Grade 1A)')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                             {{-- Level --}}
                             <div>
                                <x-input-label for="level" :value="__('Level * (e.g., Primary, Secondary, Grade 1)')" />
                                <x-text-input id="level" name="level" type="text" class="mt-1 block w-full" :value="old('level')" required />
                                <x-input-error :messages="$errors->get('level')" class="mt-2" />
                            </div>
                             {{-- Capacity --}}
                             <div>
                                <x-input-label for="capacity" :value="__('Capacity (Optional)')" />
                                <x-text-input id="capacity" name="capacity" type="number" min="0" class="mt-1 block w-full" :value="old('capacity')" />
                                <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                            </div>
                             {{-- Home Room --}}
                             <div>
                                <x-input-label for="home_room" :value="__('Home Room Teacher/Location (Optional)')" />
                                <x-text-input id="home_room" name="home_room" type="text" class="mt-1 block w-full" :value="old('home_room')" />
                                <x-input-error :messages="$errors->get('home_room')" class="mt-2" />
                            </div>
                             {{-- Notes --}}
                            <div>
                                <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                             {{-- Active Checkbox --}}
                             <div>
                                <label for="is_active" class="inline-flex items-center">
                                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded ..." {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                                </label>
                            </div>
                         </div>
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('accounting.classes.index') }}" class="text-sm text-gray-600 mr-4">Cancel</a>
                            <x-primary-button>Create Class</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>