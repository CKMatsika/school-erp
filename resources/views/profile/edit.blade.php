<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h2 class="text-lg font-medium text-gray-900">{{ __('Profile Information') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __("Update your account's profile information and email address.") }}
                    </p>
                    
                    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
                        @csrf
                        @method('patch')
                        
                        <div>
                            <label for="name">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', auth()->user()->name) }}" required autofocus>
                        </div>

                        <div>
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" required>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>