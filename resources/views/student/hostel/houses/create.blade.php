<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Hostel House') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('student.hostel.houses.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-label for="name" :value="__('House Name')" />
                                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="code" :value="__('House Code')" />
                                <x-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required />
                                @error('code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="gender" :value="__('Gender')" />
                                <select id="gender" name="gender" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="mixed" {{ old('gender') == 'mixed' ? 'selected' : '' }}>Mixed</option>
                                </select>
                                @error('gender')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="capacity" :value="__('Total Capacity')" />
                                <x-input id="capacity" class="block mt-1 w-full" type="number" min="1" name="capacity" :value="old('capacity', 40)" required />
                                @error('capacity')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="floor_count" :value="__('Number of Floors')" />
                                <x-input id="floor_count" class="block mt-1 w-full" type="number" min="1" name="floor_count" :value="old('floor_count', 1)" required />
                                @error('floor_count')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="location" :value="__('Location')" />
                                <x-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location')" />
                                @error('location')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="warden_name" :value="__('Warden Name')" />
                                <x-input id="warden_name" class="block mt-1 w-full" type="text" name="warden_name" :value="old('warden_name')" />
                                @error('warden_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="warden_contact" :value="__('Warden Contact')" />
                                <x-input id="warden_contact" class="block mt-1 w-full" type="text" name="warden_contact" :value="old('warden_contact')" />
                                @error('warden_contact')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-span-2">
                                <x-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description" rows="4" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-span-2">
                                <label for="is_active" class="inline-flex items-center mt-3">
                                    <input id="is_active" type="checkbox" name="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('House is active and available for allocation') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('student.hostel.houses.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <x-button>
                                {{ __('Create House') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>