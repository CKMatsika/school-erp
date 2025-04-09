<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Inventory Item') }}: {{ $item->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.inventory-items.update', $item) }}">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="item_code" :value="__('Item Code')" />
                                    <x-text-input id="item_code" class="block mt-1 w-full" type="text" name="item_code" :value="old('item_code', $item->item_code)" required autofocus />
                                    <x-input-error :messages="$errors->get('item_code')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="name" :value="__('Item Name')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $item->name)" required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="category_id" :value="__('Category')" />
                                    <select id="category_id" name="category_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="unit_of_measure" :value="__('Unit of Measure')" />
                                    <x-text-input id="unit_of_measure" class="block mt-1 w-full" type="text" name="unit_of_measure" :value="old('unit_of_measure', $item->unit_of_measure)" placeholder="e.g. kg, pcs, box" />
                                    <x-input-error :messages="$errors->get('unit_of_measure')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Inventory Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="current_stock" :value="__('Current Stock')" />
                                    <x-text-input id="current_stock" class="block mt-1 w-full bg-gray-100" type="number" name="current_stock" :value="old('current_stock', $item->current_stock)" step="0.01" min="0" readonly />
                                    <p class="mt-1 text-xs text-gray-600">To adjust stock, use the Stock Adjustment feature</p>
                                </div>

                                <div>
                                    <x-input-label for="reorder_level" :value="__('Reorder Level')" />
                                    <x-text-input id="reorder_level" class="block mt-1 w-full" type="number" name="reorder_level" :value="old('reorder_level', $item->reorder_level)" step="0.01" min="0" />
                                    <x-input-error :messages="$errors->get('reorder_level')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="reorder_quantity" :value="__('Reorder Quantity')" />
                                    <x-text-input id="reorder_quantity" class="block mt-1 w-full" type="number" name="reorder_quantity" :value="old('reorder_quantity', $item->reorder_quantity)" step="0.01" min="0" />
                                    <x-input-error :messages="$errors->get('reorder_quantity')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Pricing Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="unit_cost" :value="__('Unit Cost')" />
                                    <x-text-input id="unit_cost" class="block mt-1 w-full" type="number" name="unit_cost" :value="old('unit_cost', $item->unit_cost)" step="0.01" min="0" />
                                    <x-input-error :messages="$errors->get('unit_cost')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="tax_rate" :value="__('Tax Rate (%)')" />
                                    <x-text-input id="tax_rate" class="block mt-1 w-full" type="number" name="tax_rate" :value="old('tax_rate', $item->tax_rate)" step="0.01" min="0" />
                                    <x-input-error :messages="$errors->get('tax_rate')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Additional Information</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('description', $item->description) }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="notes" :value="__('Notes')" />
                                    <textarea id="notes" name="notes" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('notes', $item->notes) }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="is_active" class="inline-flex items-center">
                                        <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('accounting.inventory-items.show', $item) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Item') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>