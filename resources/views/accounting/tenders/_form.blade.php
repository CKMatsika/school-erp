@csrf
{{-- Use PUT/PATCH method spoofing for the update route --}}
@isset($tender)
    @method('PUT') {{-- Or PATCH --}}
@endisset

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Tender Number -->
    <div>
        <x-input-label for="tender_number" :value="__('Tender Number')" />
        <x-text-input id="tender_number" class="block mt-1 w-full bg-gray-100" type="text" name="tender_number" :value="old('tender_number', $tenderNumber ?? $tender->tender_number ?? '')" required readonly />
        <x-input-error :messages="$errors->get('tender_number')" class="mt-2" />
        <p class="text-xs text-gray-500 mt-1">Tender Number is auto-generated.</p>
    </div>

    <!-- Title -->
    <div>
        <x-input-label for="title" :value="__('Tender Title')" class="required"/>
        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $tender->title ?? '')" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

     <!-- Academic Year -->
    <div>
        <x-input-label for="academic_year_id" :value="__('Academic Year')" class="required"/>
        {{-- If editing, show dropdown; if creating, show the determined active year --}}
        @if(isset($academicYears)) {{-- Passed during edit --}}
            <select name="academic_year_id" id="academic_year_id" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('-- Select Year --') }}</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ old('academic_year_id', $tender->academic_year_id ?? '') == $year->id ? 'selected' : '' }}>
                        {{ $year->name ?? $year->start_date->format('Y').'/'.($year->start_date->year + 1) }} {{-- Adjust display as needed --}}
                    </option>
                @endforeach
            </select>
        @elseif(isset($academicYear)) {{-- Passed during create --}}
             <x-text-input id="academic_year_display" class="block mt-1 w-full bg-gray-100" type="text" :value="$academicYear->name ?? ($academicYear->start_date->format('Y').'/'.($academicYear->start_date->year + 1))" readonly />
             <input type="hidden" name="academic_year_id" value="{{ $academicYear->id ?? '' }}">
        @else
             <x-text-input id="academic_year_display" class="block mt-1 w-full bg-gray-100" type="text" value="N/A" readonly />
             <p class="text-xs text-red-600 mt-1">No active or default academic year found.</p>
        @endif
        <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
    </div>

    <!-- Estimated Value -->
    <div>
        <x-input-label for="estimated_value" :value="__('Estimated Value (Optional)')" />
        <x-text-input id="estimated_value" class="block mt-1 w-full" type="number" step="0.01" min="0" name="estimated_value" :value="old('estimated_value', $tender->estimated_value ?? '')" />
        <x-input-error :messages="$errors->get('estimated_value')" class="mt-2" />
    </div>

    <!-- Publication Date -->
    <div>
        <x-input-label for="publication_date" :value="__('Publication Date')" class="required"/>
        <x-text-input id="publication_date" class="block mt-1 w-full" type="date" name="publication_date" :value="old('publication_date', isset($tender->publication_date) ? $tender->publication_date->format('Y-m-d') : now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('publication_date')" class="mt-2" />
    </div>

    <!-- Closing Date -->
    <div>
        <x-input-label for="closing_date" :value="__('Closing Date')" class="required"/>
        <x-text-input id="closing_date" class="block mt-1 w-full" type="date" name="closing_date" :value="old('closing_date', isset($tender->closing_date) ? $tender->closing_date->format('Y-m-d') : '')" required />
        <x-input-error :messages="$errors->get('closing_date')" class="mt-2" />
    </div>

    {{-- Status - Only potentially editable in 'edit' view --}}
    @isset($tender)
        @if(in_array($tender->status, ['draft', 'published']))
        <div>
            <x-input-label for="status" :value="__('Status')" />
             <select name="status" id="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                {{-- Only allow valid transitions --}}
                <option value="draft" {{ old('status', $tender->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $tender->status ?? '') == 'published' ? 'selected' : '' }}>Published</option>
                {{-- Maybe allow setting to closed? Depends on workflow --}}
                 {{-- <option value="closed" {{ old('status', $tender->status ?? '') == 'closed' ? 'selected' : '' }}>Closed</option> --}}
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
        @else
            {{-- Display status read-only if not editable --}}
             <div>
                <x-input-label :value="__('Status')" />
                <x-text-input class="block mt-1 w-full bg-gray-100" type="text" :value="ucfirst($tender->status)" readonly />
            </div>
        @endif
    @endisset

     <!-- Document Upload -->
    <div class="md:col-span-2">
        <x-input-label for="tender_document" :value="__('Tender Document (Optional PDF/DOCX, Max 10MB)')" />
        <input type="file" id="tender_document" name="tender_document" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
        <x-input-error :messages="$errors->get('tender_document')" class="mt-2" />
        @isset($tender)
            @if($tender->document_path)
                 <p class="text-xs text-gray-500 mt-1">Current file:
                    <a href="{{ route('accounting.tenders.download', $tender) }}" class="text-indigo-600 hover:underline" target="_blank">
                        {{ basename($tender->document_path) }}
                    </a> (Uploading a new file will replace the current one).
                </p>
            @endif
        @endisset
    </div>

</div>

<!-- Description -->
<div class="mt-6">
    <x-input-label for="description" :value="__('Description')" class="required"/>
    {{-- Consider using a Rich Text Editor component here if needed --}}
    <textarea id="description" name="description" rows="6" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $tender->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>