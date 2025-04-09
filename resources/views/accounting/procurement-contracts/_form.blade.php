@csrf
{{-- Use PUT/PATCH method spoofing for the update route --}}
@isset($contract) {{-- Assuming variable is $contract in edit view --}}
    @method('PUT') {{-- Or PATCH --}}
@endisset

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Contract Number -->
    <div>
        <x-input-label for="contract_number" :value="__('Contract Number')" />
        <x-text-input id="contract_number" class="block mt-1 w-full bg-gray-100" type="text" name="contract_number" :value="old('contract_number', $contractNumber ?? $contract->contract_number ?? '')" required readonly />
        <x-input-error :messages="$errors->get('contract_number')" class="mt-2" />
        <p class="text-xs text-gray-500 mt-1">Contract Number is auto-generated.</p>
    </div>

    <!-- Title -->
    <div class="md:col-span-2">
        <x-input-label for="title" :value="__('Contract Title')" class="required"/>
        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $contract->title ?? '')" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <!-- Supplier -->
    <div>
        <x-input-label for="supplier_id" :value="__('Supplier')" class="required"/>
        <select name="supplier_id" id="supplier_id" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">-- Select Supplier --</option>
            {{-- Ensure controller passes $suppliers --}}
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}"
                    {{ old('supplier_id', $preSelectedSupplierId ?? $contract->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->name }}
                </option>
            @endforeach
        </select>
         <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
         @if(isset($selectedTender))
             <p class="text-xs text-green-600 mt-1">Supplier pre-selected based on awarded tender.</p>
         @endif
    </div>

    <!-- Related Tender (Optional) -->
    <div>
        <x-input-label for="tender_id" :value="__('Related Tender (Optional)')" />
        <select name="tender_id" id="tender_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">-- Select Tender (if applicable) --</option>
            {{-- Ensure controller passes $tenders (list of awarded, contract-less tenders) OR $selectedTender --}}
            @if(isset($selectedTender))
                {{-- If creating from specific tender, only show that one --}}
                 <option value="{{ $selectedTender->id }}" selected>
                     {{ $selectedTender->tender_number }} - {{ Str::limit($selectedTender->title, 40) }}
                 </option>
            @elseif(isset($tenders))
                 {{-- If creating generally, show available tenders --}}
                 @foreach($tenders as $tenderOption)
                     <option value="{{ $tenderOption->id }}"
                         {{ old('tender_id', $contract->tender_id ?? '') == $tenderOption->id ? 'selected' : '' }}>
                         {{ $tenderOption->tender_number }} - {{ Str::limit($tenderOption->title, 40) }} ({{ $tenderOption->awardedSupplier->name ?? '?' }})
                     </option>
                 @endforeach
             @endif
        </select>
        <x-input-error :messages="$errors->get('tender_id')" class="mt-2" />
    </div>

    <!-- Academic Year -->
    <div>
        <x-input-label for="academic_year_id" :value="__('Academic Year')" class="required"/>
        @if(isset($academicYears)) {{-- Passed during edit --}}
            <select name="academic_year_id" id="academic_year_id" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('-- Select Year --') }}</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ old('academic_year_id', $contract->academic_year_id ?? '') == $year->id ? 'selected' : '' }}>
                        {{ $year->name ?? $year->start_date->format('Y').'/'.($year->start_date->year + 1) }}
                    </option>
                @endforeach
            </select>
        @elseif(isset($academicYear)) {{-- Passed during create --}}
             <x-text-input id="academic_year_display" class="block mt-1 w-full bg-gray-100" type="text" :value="$academicYear->name ?? ($academicYear->start_date->format('Y').'/'.($academicYear->start_date->year + 1))" readonly />
             <input type="hidden" name="academic_year_id" value="{{ $academicYear->id ?? '' }}">
        @else
             <x-text-input id="academic_year_display" class="block mt-1 w-full bg-gray-100" type="text" value="N/A" readonly />
        @endif
        <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
    </div>

    <!-- Contract Value -->
    <div>
        <x-input-label for="contract_value" :value="__('Contract Value')" class="required"/>
        <x-text-input id="contract_value" class="block mt-1 w-full" type="number" step="0.01" min="0" name="contract_value" :value="old('contract_value', $contract->contract_value ?? '')" required />
        <x-input-error :messages="$errors->get('contract_value')" class="mt-2" />
    </div>

    <!-- Start Date -->
    <div>
        <x-input-label for="start_date" :value="__('Start Date')" class="required"/>
        <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', isset($contract->start_date) ? $contract->start_date->format('Y-m-d') : '')" required />
        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
    </div>

    <!-- End Date -->
    <div>
        <x-input-label for="end_date" :value="__('End Date')" class="required"/>
        <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', isset($contract->end_date) ? $contract->end_date->format('Y-m-d') : '')" required />
        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
    </div>

    {{-- Status - Only potentially editable in 'edit' view --}}
    @isset($contract)
        @php $editableStatuses = ['draft', 'active', 'terminated']; @endphp
        @if(in_array($contract->status, $editableStatuses))
        <div>
            <x-input-label for="status" :value="__('Status')" />
             <select name="status" id="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                {{-- Only allow valid transitions based on current status --}}
                <option value="draft" {{ old('status', $contract->status) == 'draft' ? 'selected' : '' }} @if($contract->status != 'draft') disabled @endif>Draft</option>
                <option value="active" {{ old('status', $contract->status) == 'active' ? 'selected' : '' }} @if(!in_array($contract->status, ['draft', 'active'])) disabled @endif>Active</option>
                <option value="terminated" {{ old('status', $contract->status) == 'terminated' ? 'selected' : '' }} @if($contract->status == 'expired') disabled @endif>Terminated</option>
                 {{-- Expired is usually set automatically, not manually --}}
                @if($contract->status == 'expired')
                 <option value="expired" selected disabled>Expired</option>
                @endif
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
        @else
             <div>
                <x-input-label :value="__('Status')" />
                <x-text-input class="block mt-1 w-full bg-gray-100" type="text" :value="ucfirst($contract->status)" readonly />
            </div>
        @endif
    @endisset

     <!-- Document Upload -->
    <div class="md:col-span-3">
        <x-input-label for="contract_document" :value="__('Contract Document (Optional PDF/DOCX, Max 10MB)')" />
        <input type="file" id="contract_document" name="contract_document" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
        <x-input-error :messages="$errors->get('contract_document')" class="mt-2" />
        @isset($contract)
            @if($contract->document_path)
                 <p class="text-xs text-gray-500 mt-1">Current file:
                    <a href="{{ route('accounting.procurement-contracts.download', $contract) }}" class="text-indigo-600 hover:underline" target="_blank">
                        {{ basename($contract->document_path) }}
                    </a> (Uploading a new file will replace the current one).
                </p>
            @endif
        @endisset
    </div>

</div>

<!-- Description -->
<div class="mt-6">
    <x-input-label for="description" :value="__('Scope / Description (Optional)')" />
    <textarea id="description" name="description" rows="4" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $contract->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<!-- Terms & Conditions -->
<div class="mt-6">
    <x-input-label for="terms_and_conditions" :value="__('Terms & Conditions (Optional)')" />
    {{-- Consider using a Rich Text Editor component here if needed --}}
    <textarea id="terms_and_conditions" name="terms_and_conditions" rows="6" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('terms_and_conditions', $contract->terms_and_conditions ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('terms_and_conditions')" class="mt-2" />
</div>