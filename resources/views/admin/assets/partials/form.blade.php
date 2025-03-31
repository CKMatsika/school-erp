<div>
    <form method="POST" action="{{ isset($assetAllocation) ? route('asset-allocations.update', $assetAllocation) : route('asset-allocations.store') }}">
        @csrf
        @if(isset($assetAllocation))
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
                        <select id="asset_id" name="asset_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required {{ isset($assetAllocation) && !in_array($assetAllocation->status, ['Pending', 'Draft']) ? 'disabled' : '' }}>
                            <option value="">{{ __('Select Asset') }}</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ (old('asset_id', isset($assetAllocation) ? $assetAllocation->asset_id : request('asset_id')) == $asset->id) ? 'selected' : '' }}>
                                    {{ $asset->name }} ({{ $asset->asset_code }}) - {{ $asset->status }}
                                </option>
                            @endforeach
                        </select>
                        @if(isset($assetAllocation) && !in_array($assetAllocation->status, ['Pending', 'Draft']))
                            <input type="hidden" name="asset_id" value="{{ $assetAllocation->asset_id }}">
                        @endif
                        <x-input-error :messages="$errors->get('asset_id')" class="mt-2" />
                    </div>
                    
                    <!-- Asset Status -->
                    <div>
                        <x-input-label for="asset_status" :value="__('Asset Status')" />
                        <p class="mt-1 text-sm text-gray-500" id="asset_status">
                            {{ __('Select an asset to view its status') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Allocation Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Allocation Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Allocation Type -->
                    <div>
                        <x-input-label for="allocatable_type" :value="__('Allocate To')" />
                        <select id="allocatable_type" name="allocatable_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required {{ isset($assetAllocation) && !in_array($assetAllocation->status, ['Pending', 'Draft']) ? 'disabled' : '' }}>
                            <option value="">{{ __('Select Type') }}</option>
                            <option value="App\Models\Staff" {{ (old('allocatable_type', isset($assetAllocation) ? $assetAllocation->allocatable_type : '')) == 'App\Models\Staff' ? 'selected' : '' }}>{{ __('Staff') }}</option>
                            <option value="App\Models\Student" {{ (old('allocatable_type', isset($assetAllocation) ? $assetAllocation->allocatable_type : '')) == 'App\Models\Student' ? 'selected' : '' }}>{{ __('Student') }}</option>
                            <option value="App\Models\Department" {{ (old('allocatable_type', isset($assetAllocation) ? $assetAllocation->allocatable_type : '')) == 'App\Models\Department' ? 'selected' : '' }}>{{ __('Department') }}</option>
                        </select>
                        @if(isset($assetAllocation) && !in_array($assetAllocation->status, ['Pending', 'Draft']))
                            <input type="hidden" name="allocatable_type" value="{{ $assetAllocation->allocatable_type }}">
                        @endif
                        <x-input-error :messages="$errors->get('allocatable_type')" class="mt-2" />
                    </div>

                    <!-- Allocation Recipient -->
                    <div>
                        <x-input-label id="allocatable_id_label" for="allocatable_id" :value="__('Select Recipient')" />
                        <select id="allocatable_id" name="allocatable_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required {{ isset($assetAllocation) && !in_array($assetAllocation->status, ['Pending', 'Draft']) ? 'disabled' : '' }}>
                            <option value="">{{ __('Select Recipient') }}</option>
                            <!-- Options will be populated via JavaScript -->
                        </select>
                        @if(isset($assetAllocation) && !in_array($assetAllocation->status, ['Pending', 'Draft']))
                            <input type="hidden" name="allocatable_id" value="{{ $assetAllocation->allocatable_id }}">
                        @endif
                        <x-input-error :messages="$errors->get('allocatable_id')" class="mt-2" />
                    </div>

                    <!-- Allocation Date -->
                    <div>
                        <x-input-label for="allocated_at" :value="__('Allocation Date')" />
                        <x-text-input id="allocated_at" class="block mt-1 w-full" type="date" name="allocated_at" :value="old('allocated_at', isset($assetAllocation) ? $assetAllocation->allocated_at->format('Y-m-d') : now()->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('allocated_at')" class="mt-2" />
                    </div>

                    <!-- Due Date -->
                    <div>
                        <x-input-label for="due_date" :value="__('Return Due Date')" />
                        <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date', isset($assetAllocation) && $assetAllocation->due_date ? $assetAllocation->due_date->format('Y-m-d') : '')" />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ (old('status', isset($assetAllocation) ? $assetAllocation->status : 'Pending')) == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <!-- Return Date (shown only for returned status) -->
                    <div id="return_date_container" class="{{ (old('status', isset($assetAllocation) ? $assetAllocation->status : '')) == 'Returned' ? '' : 'hidden' }}">
                        <x-input-label for="returned_at" :value="__('Return Date')" />
                        <x-text-input id="returned_at" class="block mt-1 w-full" type="date" name="returned_at" :value="old('returned_at', isset($assetAllocation) && $assetAllocation->returned_at ? $assetAllocation->returned_at->format('Y-m-d') : now()->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('returned_at')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="col-span-2">
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes', isset($assetAllocation) ? $assetAllocation->notes : '') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                {{ __('Cancel') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ isset($assetAllocation) ? __('Update Allocation') : __('Create Allocation') }}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle status change to show/hide return date
        const statusSelect = document.getElementById('status');
        const returnDateContainer = document.getElementById('return_date_container');
        
        if (statusSelect && returnDateContainer) {
            statusSelect.addEventListener('change', function() {
                if (this.value === 'Returned') {
                    returnDateContainer.classList.remove('hidden');
                } else {
                    returnDateContainer.classList.add('hidden');
                }
            });
        }
        
        // Handle allocatable type change to load appropriate recipients
        const allocatableTypeSelect = document.getElementById('allocatable_type');
        const allocatableIdSelect = document.getElementById('allocatable_id');
        const allocatableIdLabel = document.getElementById('allocatable_id_label');
        
        if (allocatableTypeSelect && allocatableIdSelect) {
            allocatableTypeSelect.addEventListener('change', function() {
                // Clear current options
                allocatableIdSelect.innerHTML = '<option value="">{{ __("Select Recipient") }}</option>';
                
                // Update label based on selection
                if (this.value === 'App\\Models\\Staff') {
                    allocatableIdLabel.textContent = '{{ __("Select Staff") }}';
                    fetchRecipients('staff');
                } else if (this.value === 'App\\Models\\Student') {
                    allocatableIdLabel.textContent = '{{ __("Select Student") }}';
                    fetchRecipients('students');
                } else if (this.value === 'App\\Models\\Department') {
                    allocatableIdLabel.textContent = '{{ __("Select Department") }}';
                    fetchRecipients('departments');
                }
            });
            
            // Function to fetch recipients based on type
            function fetchRecipients(type) {
                fetch(`/api/${type}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.name;
                            allocatableIdSelect.appendChild(option);
                        });
                        
                        // If editing, select the current value
                        @if(isset($assetAllocation))
                        allocatableIdSelect.value = "{{ $assetAllocation->allocatable_id }}";
                        @endif
                    })
                    .catch(error => console.error('Error fetching recipients:', error));
            }
            
            // Trigger change event if a value is already selected (edit mode)
            if (allocatableTypeSelect.value) {
                allocatableTypeSelect.dispatchEvent(new Event('change'));
            }
        }
        
        // Show asset status when asset is selected
        const assetSelect = document.getElementById('asset_id');
        const assetStatusElement = document.getElementById('asset_status');
        
        if (assetSelect && assetStatusElement) {
            assetSelect.addEventListener('change', function() {
                if (this.value) {
                    fetch(`/api/assets/${this.value}`)
                        .then(response => response.json())
                        .then(data => {
                            assetStatusElement.textContent = `${data.status} - ${data.category?.name || 'No Category'}`;
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
    });
</script>
@endpush