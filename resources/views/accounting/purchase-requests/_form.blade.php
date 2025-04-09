@csrf
{{-- Use PUT/PATCH method spoofing for the update route --}}
@isset($purchaseRequest)
    @method('PUT') {{-- Or PATCH --}}
@endisset

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- PR Number -->
    <div>
        <x-input-label for="pr_number" :value="__('PR Number')" />
        <x-text-input id="pr_number" class="block mt-1 w-full bg-gray-100" type="text" name="pr_number" :value="old('pr_number', $prNumber ?? $purchaseRequest->pr_number ?? '')" required readonly />
        <x-input-error :messages="$errors->get('pr_number')" class="mt-2" />
        <p class="text-xs text-gray-500 mt-1">PR Number is auto-generated.</p>
    </div>

    <!-- Date Requested -->
    <div>
        <x-input-label for="date_requested" :value="__('Date Requested')" class="required" />
        <x-text-input id="date_requested" class="block mt-1 w-full" type="date" name="date_requested" :value="old('date_requested', isset($purchaseRequest->date_requested) ? $purchaseRequest->date_requested->format('Y-m-d') : now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('date_requested')" class="mt-2" />
    </div>

    <!-- Date Required -->
    <div>
        <x-input-label for="date_required" :value="__('Date Required (Optional)')" />
        <x-text-input id="date_required" class="block mt-1 w-full" type="date" name="date_required" :value="old('date_required', isset($purchaseRequest->date_required) ? $purchaseRequest->date_required->format('Y-m-d') : '')" />
        <x-input-error :messages="$errors->get('date_required')" class="mt-2" />
    </div>

    <!-- Department -->
    <div>
        <x-input-label for="department" :value="__('Department (Optional)')" />
        <x-text-input id="department" class="block mt-1 w-full" type="text" name="department" :value="old('department', $purchaseRequest->department ?? '')" placeholder="e.g., IT Department, Academics"/>
        <x-input-error :messages="$errors->get('department')" class="mt-2" />
    </div>

    <!-- Academic Year -->
    <div>
        <x-input-label for="academic_year_id" :value="__('Academic Year')" />
        {{-- If editing, show dropdown; if creating, show the determined active year --}}
        @if(isset($academicYears)) {{-- Passed during edit --}}
            <select name="academic_year_id" id="academic_year_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('-- Select Year --') }}</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ old('academic_year_id', $purchaseRequest->academic_year_id ?? '') == $year->id ? 'selected' : '' }}>
                        {{ $year->year }} {{-- Assuming 'year' field exists --}}
                    </option>
                @endforeach
            </select>
        @elseif(isset($academicYear)) {{-- Passed during create --}}
             <x-text-input id="academic_year_display" class="block mt-1 w-full bg-gray-100" type="text" :value="$academicYear->year ?? 'N/A - Please setup Academic Year'" readonly />
             <input type="hidden" name="academic_year_id" value="{{ $academicYear->id ?? '' }}">
        @else
             <x-text-input id="academic_year_display" class="block mt-1 w-full bg-gray-100" type="text" value="N/A" readonly />
             <p class="text-xs text-red-600 mt-1">No active or default academic year found.</p>
        @endif
        <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
    </div>

</div>

<!-- Notes -->
<div class="mt-6">
    <x-input-label for="notes" :value="__('Notes / Justification (Optional)')" />
    <textarea id="notes" name="notes" rows="4" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes', $purchaseRequest->notes ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
</div>