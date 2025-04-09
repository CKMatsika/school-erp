```blade
@csrf
@isset($element)
    @method('PUT')
@endisset

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" :value="__('Element Name')" class="required"/>
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $element->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="type" :value="__('Element Type')" class="required"/>
        <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="allowance" {{ old('type', $element->type ?? '') == 'allowance' ? 'selected' : '' }}>Allowance</option>
            <option value="deduction" {{ old('type', $element->type ?? '') == 'deduction' ? 'selected' : '' }}>Deduction</option>
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>
     <div>
        <x-input-label for="calculation_type" :value="__('Calculation Type')" class="required"/>
        <select name="calculation_type" id="calculation_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="fixed" {{ old('calculation_type', $element->calculation_type ?? 'fixed') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
            <option value="percentage_basic" {{ old('calculation_type', $element->calculation_type ?? '') == 'percentage_basic' ? 'selected' : '' }}>Percentage of Basic Salary</option>
        </select>
        <x-input-error :messages="$errors->get('calculation_type')" class="mt-2" />
    </div>
     <div>
        <x-input-label for="default_amount_or_rate" :value="__('Default Amount / Rate')" class="required"/>
        <x-text-input id="default_amount_or_rate" name="default_amount_or_rate" type="number" step="0.0001" min="0" class="mt-1 block w-full" :value="old('default_amount_or_rate', $element->default_amount_or_rate ?? '')" required />
        <x-input-error :messages="$errors->get('default_amount_or_rate')" class="mt-2" />
         <p class="text-xs text-gray-500 mt-1">Enter amount if Fixed, enter decimal rate if Percentage (e.g., 0.1 for 10%).</p>
    </div>
    <div class="md:col-span-2 grid grid-cols-2 gap-6">
         <div class="flex items-center">
            <input type="hidden" name="is_taxable" value="0">
            <input id="is_taxable" name="is_taxable" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-4 w-4" {{ old('is_taxable', $element->is_taxable ?? false) ? 'checked' : '' }}>
            <label for="is_taxable" class="ml-2 block text-sm text-gray-900">Is Taxable?</label>
            <x-input-error :messages="$errors->get('is_taxable')" class="mt-2" />
        </div>
        <div class="flex items-center">
             <input type="hidden" name="is_active" value="0">
             <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-4 w-4" {{ old('is_active', $element->is_active ?? true) ? 'checked' : '' }}>
             <label for="is_active" class="ml-2 block text-sm text-gray-900">Is Active?</label>
             <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
        </div>
    </div>
    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Description (Optional)')" />
        <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $element->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>