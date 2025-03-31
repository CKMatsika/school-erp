<div>
    <form method="POST" action="{{ isset($maintenanceRecord) ? route('asset-maintenance.update', $maintenanceRecord) : route('asset-maintenance.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($maintenanceRecord))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Asset Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Asset Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Asset Selection -->
                    <div>
                        <x-input-label for="asset_id" :value="__('Asset')" />
                        <select id="asset_id" name="asset_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required {{ isset($maintenanceRecord) && !in_array($maintenanceRecord->status, ['Pending', 'Scheduled']) ? 'disabled' : '' }}>
                            <option value="">{{ __('Select Asset') }}</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ (old('asset_id', isset($maintenanceRecord) ? $maintenanceRecord->asset_id : request('asset_id'))) == $asset->id ? 'selected' : '' }}>
                                    {{ $asset->name }} ({{ $asset->asset_code }})
                                </option>
                            @endforeach
                        </select>
                        @if(isset($maintenanceRecord) && !in_array($maintenanceRecord->status, ['Pending', 'Scheduled']))
                            <input type="hidden" name="asset_id" value="{{ $maintenanceRecord->asset_id }}">
                        @endif
                        <x-input-error :messages="$errors->get('asset_id')" class="mt-2" />
                    </div>
                    
                    <!-- Asset Status -->
                    <div>
                        <x-input-label for="asset_status" :value="__('Current Asset Status')" />
                        <p class="mt-1 text-sm text-gray-500" id="asset_status">
                            {{ isset($maintenanceRecord) && $maintenanceRecord->asset ? $maintenanceRecord->asset->status : __('Select an asset to view its status') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Maintenance Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Maintenance Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Maintenance Type -->
                    <div>
                        <x-input-label for="maintenance_type" :value="__('Maintenance Type')" />
                        <select id="maintenance_type" name="maintenance_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">{{ __('Select Type') }}</option>
                            @foreach($maintenanceTypes as $type)
                                <option value="{{ $type }}" {{ (old('maintenance_type', isset($maintenanceRecord) ? $maintenanceRecord->maintenance_type : '')) == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('maintenance_type')" class="mt-2" />
                    </div>

                    <!-- Maintenance Date -->
                    <div>
                        <x-input-label for="maintenance_date" :value="__('Maintenance Date')" />
                        <x-text-input id="maintenance_date" class="block mt-1 w-full" type="date" name="maintenance_date" :value="old('maintenance_date', isset($maintenanceRecord) ? $maintenanceRecord->maintenance_date->format('Y-m-d') : now()->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('maintenance_date')" class="mt-2" />
                    </div>

                    <!-- Next Service Date -->
                    <div>
                        <x-input-label for="next_service_date" :value="__('Next Service Date')" />
                        <x-text-input id="next_service_date" class="block mt-1 w-full" type="date" name="next_service_date" :value="old('next_service_date', isset($maintenanceRecord) && $maintenanceRecord->next_service_date ? $maintenanceRecord->next_service_date->format('Y-m-d') : '')" />
                        <x-input-error :messages="$errors->get('next_service_date')" class="mt-2" />
                    </div>

                    <!-- Provider -->
                    <div>
                        <x-input-label for="provider" :value="__('Service Provider')" />
                        <x-text-input id="provider" class="block mt-1 w-full" type="text" name="provider" :value="old('provider', isset($maintenanceRecord) ? $maintenanceRecord->provider : '')" />
                        <x-input-error :messages="$errors->get('provider')" class="mt-2" />
                    </div>

                    <!-- Cost -->
                    <div>
                        <x-input-label for="cost" :value="__('Maintenance Cost')" />
                        <x-text-input id="cost" class="block mt-1 w-full" type="number" step="0.01" min="0" name="cost" :value="old('cost', isset($maintenanceRecord) ? $maintenanceRecord->cost : '')" required />
                        <x-input-error :messages="$errors->get('cost')" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ (old('status', isset($maintenanceRecord) ? $maintenanceRecord->status : 'Scheduled')) == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Additional Information') }}</h3>
                
                <div class="grid grid-cols-1 gap-4">
                    <!-- Description -->
                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', isset($maintenanceRecord) ? $maintenanceRecord->description : '') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <!-- Documents -->
                    <div>
                        <x-input-label for="documents" :value="__('Maintenance Documents (Invoices, Reports, etc.)')" />
                        <input id="documents" name="documents[]" type="file" multiple class="block mt-1 w-full text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100"
                        />
                        <x-input-error :messages="$errors->get('documents')" class="mt-2" />
                        
                        @if(isset($maintenanceRecord) && $maintenanceRecord->documents->count() > 0)
                            <div class="mt-2">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">{{ __('Existing Documents:') }}</h4>
                                <ul class="list-disc list-inside text-sm">
                                    @foreach($maintenanceRecord->documents as $document)
                                        <li class="flex items-center">
                                            <a href="{{ asset('storage/' . $document->path) }}" target="_blank" class="text-blue-600 hover:underline mr-2">
                                                {{ $document->filename }}
                                            </a>
                                            <button type="button" onclick="document.getElementById('delete-doc-{{ $document->id }}').submit();" class="text-red-600 hover:text-red-900">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <form id="delete-doc-{{ $document->id }}" action="{{ route('maintenance-documents.destroy', $document) }}" method="POST" class="hidden">
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
                {{ isset($maintenanceRecord) ? __('Update Maintenance Record') : __('Create Maintenance Record') }}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Asset status update on selection
        const assetSelect = document.getElementById('asset_id');
        const assetStatusElement = document.getElementById('asset_status');
        
        if (assetSelect && assetStatusElement) {
            assetSelect.addEventListener('change', function() {
                if (this.value) {
                    fetch(`/api/assets/${this.value}`)
                        .then(response => response.json())
                        .then(data => {
                            assetStatusElement.textContent = data.status;
                        })
                        .catch(error => {
                            assetStatusElement.textContent = '{{ __("Error fetching asset details") }}';
                            console.error('Error fetching asset:', error);
                        });
                } else {
                    assetStatusElement.textContent = '{{ __("Select an asset to view its status") }}';
                }
            });
            
            // Trigger change event if a value is already selected (edit mode)
            if (assetSelect.value) {
                assetSelect.dispatchEvent(new Event('change'));
            }
        }
        
        // Maintenance type change to suggest next service date
        const typeSelect = document.getElementById('maintenance_type');
        const nextServiceDateInput = document.getElementById('next_service_date');
        const maintenanceDateInput = document.getElementById('maintenance_date');
        
        if (typeSelect && nextServiceDateInput && maintenanceDateInput) {
            typeSelect.addEventListener('change', function() {
                if (this.value && maintenanceDateInput.value) {
                    // Suggest next service date based on type
                    let months = 0;
                    switch(this.value) {
                        case 'Preventive':
                            months = 3;
                            break;
                        case 'Regular':
                            months = 6;
                            break;
                        case 'Major':
                            months = 12;
                            break;
                        default:
                            months = 0;
                    }
                    
                    if (months > 0) {
                        const maintenanceDate = new Date(maintenanceDateInput.value);
                        maintenanceDate.setMonth(maintenanceDate.getMonth() + months);
                        nextServiceDateInput.value = maintenanceDate.toISOString().slice(0, 10);
                    }
                }
            });
            
            // Update next service date when maintenance date changes
            maintenanceDateInput.addEventListener('change', function() {
                if (typeSelect.value && this.value) {
                    typeSelect.dispatchEvent(new Event('change'));
                }
            });
        }
    });
</script>
@endpush