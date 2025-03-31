<div>
    <form method="POST" action="{{ isset($asset) ? route('assets.update', $asset) : route('assets.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($asset))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Basic Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Asset Name -->
                    <div>
                        <x-input-label for="name" :value="__('Asset Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', isset($asset) ? $asset->name : '')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Asset Code -->
                    <div>
                        <x-input-label for="asset_code" :value="__('Asset Code')" />
                        <x-text-input id="asset_code" class="block mt-1 w-full" type="text" name="asset_code" :value="old('asset_code', isset($asset) ? $asset->asset_code : $assetCode)" required />
                        <x-input-error :messages="$errors->get('asset_code')" class="mt-2" />
                    </div>

                    <!-- Category -->
                    <div>
                        <x-input-label for="category_id" :value="__('Category')" />
                        <select id="category_id" name="category_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (old('category_id', isset($asset) ? $asset->category_id : '') == $category->id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ (old('status', isset($asset) ? $asset->status : '') == $status) ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Purchase Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Purchase Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Purchase Date -->
                    <div>
                        <x-input-label for="purchase_date" :value="__('Purchase Date')" />
                        <x-text-input id="purchase_date" class="block mt-1 w-full" type="date" name="purchase_date" :value="old('purchase_date', isset($asset) ? $asset->purchase_date->format('Y-m-d') : '')" required />
                        <x-input-error :messages="$errors->get('purchase_date')" class="mt-2" />
                    </div>

                    <!-- Purchase Cost -->
                    <div>
                        <x-input-label for="purchase_cost" :value="__('Purchase Cost')" />
                        <x-text-input id="purchase_cost" class="block mt-1 w-full" type="number" step="0.01" min="0" name="purchase_cost" :value="old('purchase_cost', isset($asset) ? $asset->purchase_cost : '')" required />
                        <x-input-error :messages="$errors->get('purchase_cost')" class="mt-2" />
                    </div>

                    <!-- Warranty Period (Months) -->
                    <div>
                        <x-input-label for="warranty_months" :value="__('Warranty (Months)')" />
                        <x-text-input id="warranty_months" class="block mt-1 w-full" type="number" min="0" name="warranty_months" :value="old('warranty_months', isset($asset) ? $asset->warranty_months : '')" />
                        <x-input-error :messages="$errors->get('warranty_months')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Additional Information') }}</h3>
                
                <!-- Description -->
                <div class="mb-4">
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', isset($asset) ? $asset->description : '') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Asset Image -->
                    <div>
                        <x-input-label for="image" :value="__('Asset Image')" />
                        <input id="image" name="image" type="file" accept="image/*" class="block mt-1 w-full text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100"
                        />
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        
                        @if(isset($asset) && $asset->image)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $asset->image) }}" alt="{{ $asset->name }}" class="w-32 h-32 object-cover rounded">
                            </div>
                        @endif
                    </div>

                    <!-- Asset Documents -->
                    <div>
                        <x-input-label for="documents" :value="__('Asset Documents (Invoice, Warranty, etc.)')" />
                        <input id="documents" name="documents[]" type="file" multiple class="block mt-1 w-full text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100"
                        />
                        <x-input-error :messages="$errors->get('documents')" class="mt-2" />
                        
                        @if(isset($asset) && $asset->documents->count() > 0)
                            <div class="mt-2">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">{{ __('Existing Documents:') }}</h4>
                                <ul class="list-disc list-inside text-sm">
                                    @foreach($asset->documents as $document)
                                        <li class="flex items-center">
                                            <a href="{{ asset('storage/' . $document->path) }}" target="_blank" class="text-blue-600 hover:underline mr-2">
                                                {{ $document->filename }}
                                            </a>
                                            <button type="button" onclick="document.getElementById('delete-doc-{{ $document->id }}').submit();" class="text-red-600 hover:text-red-900">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <form id="delete-doc-{{ $document->id }}" action="{{ route('asset-documents.destroy', $document) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                {{ __('Cancel') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ isset($asset) ? __('Update Asset') : __('Create Asset') }}
            </x-primary-button>
        </div>
    </form>
</div>