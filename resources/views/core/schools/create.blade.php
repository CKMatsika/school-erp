<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New School') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('schools.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- School Information -->
                            <div class="col-span-1 md:col-span-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">School Information</h3>
                            </div>

                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('School Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Code -->
                            <div>
                                <x-input-label for="code" :value="__('School Code')" />
                                <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                            <!-- Location Information -->
                            <div class="col-span-1 md:col-span-2 mt-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Location Information</h3>
                            </div>

                            <!-- Address -->
                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="address" :value="__('Address')" />
                                <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" />
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>

                            <!-- City -->
                            <div>
                                <x-input-label for="city" :value="__('City')" />
                                <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" />
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>

                            <!-- State/Province -->
                            <div>
                                <x-input-label for="state" :value="__('State/Province')" />
                                <x-text-input id="state" class="block mt-1 w-full" type="text" name="state" :value="old('state')" />
                                <x-input-error :messages="$errors->get('state')" class="mt-2" />
                            </div>

                            <!-- Country -->
                            <div>
                                <x-input-label for="country" :value="__('Country')" />
                                <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country')" />
                                <x-input-error :messages="$errors->get('country')" class="mt-2" />
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <x-input-label for="postal_code" :value="__('Postal Code')" />
                                <x-text-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code')" />
                                <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                            </div>

                            <!-- Contact Information -->
                            <div class="col-span-1 md:col-span-2 mt-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                            </div>

                            <!-- Phone -->
                            <div>
                                <x-input-label for="phone" :value="__('Phone')" />
                                <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <!-- Email -->
                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Website -->
                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="website" :value="__('Website')" />
                                <x-text-input id="website" class="block mt-1 w-full" type="url" name="website" :value="old('website')" />
                                <x-input-error :messages="$errors->get('website')" class="mt-2" />
                            </div>

                            <!-- Status and Subscription -->
                            <div class="col-span-1 md:col-span-2 mt-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Status and Subscription</h3>
                            </div>

                            <!-- Is Active -->
                            <div>
                                <label for="is_active" class="inline-flex items-center">
                                    <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                                </label>
                            </div>

                            <!-- Subscription Start -->
                            <div>
                                <x-input-label for="subscription_start" :value="__('Subscription Start Date')" />
                                <x-text-input id="subscription_start" class="block mt-1 w-full" type="date" name="subscription_start" :value="old('subscription_start')" />
                                <x-input-error :messages="$errors->get('subscription_start')" class="mt-2" />
                            </div>

                            <!-- Subscription End -->
                            <div>
                                <x-input-label for="subscription_end" :value="__('Subscription End Date')" />
                                <x-text-input id="subscription_end" class="block mt-1 w-full" type="date" name="subscription_end" :value="old('subscription_end')" />
                                <x-input-error :messages="$errors->get('subscription_end')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('schools.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Create School') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>