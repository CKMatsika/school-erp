<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Assign Classes to: {{ $staff->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 {{-- Ensure controller passes $staff, $classes, $academicYears, $assignedClasses --}}
                <form method="POST" action="{{ route('hr.staff.assign-classes.sync', $staff) }}">
                     @csrf
                    <div class="p-6 bg-white border-b border-gray-200">
                         <p class="mb-6 text-sm text-gray-600">Select the classes and corresponding academic year this staff member teaches.</p>

                         <div class="space-y-6">
                            {{-- Iterate through available classes --}}
                             @forelse($classes as $class)
                                <div class="flex items-center space-x-4 p-3 border rounded-md bg-gray-50">
                                    @php
                                        // Find if this class is assigned and for which year
                                        $currentAssignment = $staff->classes->firstWhere('id', $class->id);
                                        $assignedYearId = $currentAssignment->pivot->academic_year_id ?? null;
                                    @endphp
                                    <input type="checkbox" name="assignments[{{ $class->id }}][class_id]" value="{{ $class->id }}"
                                            id="class_{{ $class->id }}"
                                           class="class-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50 h-5 w-5"
                                           {{ $currentAssignment ? 'checked' : '' }}>
                                    <label for="class_{{ $class->id }}" class="flex-grow text-sm font-medium text-gray-800">{{ $class->name }}</label>

                                    {{-- Academic Year Selector for this class --}}
                                    <div class="w-1/2">
                                         <select name="assignments[{{ $class->id }}][academic_year_id]"
                                                class="year-select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                {{ !$currentAssignment ? 'disabled' : '' }} {{-- Disable if class checkbox is not checked --}}
                                                >
                                            <option value="">-- Select Year --</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}" {{ old('assignments.'.$class->id.'.academic_year_id', $assignedYearId) == $year->id ? 'selected' : '' }}>
                                                     {{ $year->name ?? $year->start_date->format('Y').'/'.($year->start_date->year + 1) }}
                                                </option>
                                            @endforeach
                                        </select>
                                         {{-- Display errors specific to this row --}}
                                        @error('assignments.'.$class->id.'.academic_year_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                             @empty
                                 <p class="text-sm text-gray-500 italic">No classes found in the system. Please add classes first.</p>
                            @endforelse
                         </div>
                         <x-input-error :messages="$errors->get('assignments')" class="mt-2" />
                         <x-input-error :messages="$errors->get('assignments.*.class_id')" class="mt-2" />

                    </div>

                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('hr.staff.show', $staff) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Update Assigned Classes') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Enable/disable year dropdown based on checkbox state
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.class-checkbox');
            checkboxes.forEach(checkbox => {
                const yearSelect = checkbox.closest('.flex').querySelector('.year-select');
                checkbox.addEventListener('change', function () {
                    yearSelect.disabled = !this.checked;
                    if (!this.checked) {
                        yearSelect.value = ''; // Clear selection if unchecked
                    }
                });
                // Initial state
                yearSelect.disabled = !checkbox.checked;
            });
        });
    </script>
</x-app-layout>