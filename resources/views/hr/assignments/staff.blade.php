```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Assign Payroll Elements to: {{ $staff->full_name }}
        </h2>
    </x-slot>

     <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
             @include('components.flash-messages')
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 {{-- Ensure controller passes $staff, $availableElements, $assignedData --}}
                 <form method="POST" action="{{ route('hr.payroll.assignments.sync', $staff) }}">
                    @csrf
                    <div class="p-6 bg-white border-b border-gray-200">
                        <p class="mb-4 text-sm text-gray-600">Select elements for this staff member and optionally override the default amount/rate.</p>

                        <div class="space-y-4">
                            @foreach($availableElements as $element)
                                @php
                                    $assignment = $assignedData->get($element->id); // Get current assignment data if exists
                                    $isChecked = $assignment !== null;
                                    $currentValue = old('assignments.'.$element->id.'.amount_or_rate', $assignment->pivot->amount_or_rate ?? '');
                                @endphp
                                <div class="flex flex-wrap items-center space-x-4 p-3 border rounded-md bg-gray-50">
                                     <div class="flex-shrink-0 w-6">
                                         <input type="checkbox" name="assignments[{{ $element->id }}][element_id]" value="{{ $element->id }}"
                                               id="element_{{ $element->id }}"
                                               class="element-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-5 w-5"
                                               {{ $isChecked ? 'checked' : '' }}>
                                    </div>
                                    <label for="element_{{ $element->id }}" class="flex-grow text-sm font-medium text-gray-800 w-1/3">
                                        {{ $element->name }}
                                        <span class="text-xs text-gray-500">({{ ucfirst($element->type) }} - {{ ucfirst(str_replace('_', ' ', $element->calculation_type)) }})</span>
                                    </label>
                                     {{-- Override Amount/Rate --}}
                                    <div class="w-1/3">
                                         <label for="amount_rate_{{ $element->id }}" class="sr-only">Override Amount/Rate</label>
                                         <x-text-input id="amount_rate_{{ $element->id }}"
                                               name="assignments[{{ $element->id }}][amount_or_rate]"
                                               type="number" step="0.0001" min="0"
                                               class="override-input block w-full text-sm"
                                               :value="$currentValue"
                                               placeholder="Default: {{ $element->calculation_type === 'percentage_basic' ? number_format($element->default_amount_or_rate * 100, 2).'%' : number_format($element->default_amount_or_rate, 2) }}"
                                               {{ !$isChecked ? 'disabled' : '' }} />
                                         <x-input-error :messages="$errors->get('assignments.'.$element->id.'.amount_or_rate')" class="mt-1 text-xs" />
                                    </div>
                                    {{-- Add Start/End Date if needed --}}

                                </div>
                            @endforeach
                        </div>
                         <x-input-error :messages="$errors->get('assignments')" class="mt-2" />
                    </div>
                     <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('hr.staff.show', $staff) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                        <x-primary-button type="submit">Update Assignments</x-primary-button>
                    </div>
                 </form>
            </div>
        </div>
    </div>
    <script>
        // Enable/disable override input based on checkbox state
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.element-checkbox');
            checkboxes.forEach(checkbox => {
                const overrideInput = checkbox.closest('.flex').querySelector('.override-input');
                checkbox.addEventListener('change', function () {
                    overrideInput.disabled = !this.checked;
                    if (!this.checked) {
                        overrideInput.value = ''; // Clear value if unchecked
                    }
                });
                // Initial state
                overrideInput.disabled = !checkbox.checked;
            });
        });
    </script>
</x-app-layout>