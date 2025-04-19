{{-- resources/views/accounting/subjects/_form.blade.php --}}
@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Name --}}
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Subject Name *') }}</label>
        <input type="text" name="name" id="name" value="{{ old('name', $subject->name ?? '') }}" required
               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Subject Code --}}
    <div>
        <label for="subject_code" class="block text-sm font-medium text-gray-700">{{ __('Subject Code (Optional)') }}</label>
        <input type="text" name="subject_code" id="subject_code" value="{{ old('subject_code', $subject->subject_code ?? '') }}"
               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
        @error('subject_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">A unique code for the subject (e.g., MATH101, PHY01).</p>
    </div>

    {{-- Is Active Checkbox --}}
    <div class="flex items-center pt-6"> {{-- Added padding for alignment --}}
         <label for="is_active" class="flex items-center">
            <input type="hidden" name="is_active" value="0"> {{-- Sends 0 if checkbox is unchecked --}}
            <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                   @checked(old('is_active', $subject->is_active ?? true))> {{-- Default to active on create form --}}
            <span class="ml-2 text-sm text-gray-600">{{ __('Active Subject') }}</span>
        </label>
         @error('is_active') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>


    {{-- Description --}}
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description (Optional)') }}</label>
        <textarea name="description" id="description" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('description', $subject->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

</div>