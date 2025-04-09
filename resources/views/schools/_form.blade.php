{{-- resources/views/schools/_form.blade.php --}}
@csrf
{{-- Use PUT/PATCH method spoofing only on the edit form --}}
@if(isset($school) && $school->exists) {{-- Check if editing an existing school --}}
    @method('PUT')
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- School Name --}}
    <div>
        <x-input-label for="name" :value="__('School Name')" class="required"/>
        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $school->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    {{-- === ADDED SCHOOL CODE FIELD === --}}
    <div>
        <x-input-label for="code" :value="__('School Code')" class="required"/>
        <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $school->code ?? '')" required />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
        <p class="text-xs text-gray-500 mt-1">A unique short code for the school (e.g., SPHS).</p>
    </div>
    {{-- === END ADDED FIELD === --}}

    {{-- Email (Remove if column doesn't exist) --}}
     {{-- <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $school->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div> --}}

    {{-- Phone --}}
    <div>
        <x-input-label for="phone" :value="__('Phone Number')" />
        <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone', $school->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    {{-- Principal Name (Example) --}}
    <div>
        <x-input-label for="principal_name" :value="__('Principal Name (Optional)')" />
        <x-text-input id="principal_name" class="block mt-1 w-full" type="text" name="principal_name" :value="old('principal_name', $school->principal_name ?? '')" />
        <x-input-error :messages="$errors->get('principal_name')" class="mt-2" />
    </div>

     {{-- Address --}}
    <div class="md:col-span-2">
        <x-input-label for="address" :value="__('Address')" />
        <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('address', $school->address ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    {{-- Logo Upload --}}
    <div class="md:col-span-2">
        <x-input-label for="logo" :value="__('School Logo (Optional)')" />
        <input type="file" id="logo" name="logo" accept="image/*"
               class="block w-full mt-1 text-sm text-gray-500
                      file:mr-4 file:py-2 file:px-4
                      file:rounded-md file:border-0
                      file:text-sm file:font-semibold
                      file:bg-indigo-50 file:text-indigo-700
                      hover:file:bg-indigo-100"/>
        <x-input-error :messages="$errors->get('logo')" class="mt-2" />
        <p class="text-xs text-gray-500 mt-1">Recommended: PNG/JPG/SVG, Max 2MB.</p>

        {{-- Display current logo only on edit form --}}
        @if(isset($school) && $school->exists && $school->logo_url)
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-700 mb-1">Current Logo:</p>
                <img src="{{ $school->logo_url }}" alt="{{ $school->name }} Logo" class="h-24 w-auto object-contain border rounded">
                <p class="text-xs text-gray-500 mt-1">Uploading a new logo will replace this one.</p>
            </div>
        @endif
    </div>

    {{-- Status (Typically only editable, not set on create) --}}
     @if(isset($school) && $school->exists)
        <div class="md:col-span-2">
            <label for="is_active" class="inline-flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1" {{ old('is_active', $school->is_active ?? true) ? 'checked' : '' }}>
                <span class="ml-2 text-sm text-gray-600">{{ __('School is Active') }}</span>
            </label>
            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
         </div>
     @else
        {{-- Hidden input for create form if needed by validation/controller, defaults to true --}}
        <input type="hidden" name="is_active" value="1">
     @endif

    {{-- Add other fields from your migration here (city, state, country, website, etc.) --}}
    {{-- Example for City --}}
     <div>
        <x-input-label for="city" :value="__('City')" />
        <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city', $school->city ?? '')" />
        <x-input-error :messages="$errors->get('city')" class="mt-2" />
    </div>
    {{-- Add State, Postal Code, Country, Website etc. similarly --}}


</div>