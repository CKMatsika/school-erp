<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Assign Classes to: {{ $staff->full_name }} <!-- Assuming full_name attribute/accessor exists -->
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 {{-- Ensure controller passes $staff, $classes, $academicYears --}}
                <form method="POST" action="{{ route('hr.staff.assign-classes.sync', $staff) }}">
                     @csrf
                    <div class="p-6 bg-white border-b border-gray-200">
                         <p class="mb-6 text-sm text-gray-600">Select the classes and corresponding academic year this staff member teaches.</p>

                         {{-- Display General Validation Errors (e.g., for 'assignments' array itself) --}}
                         @if ($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <strong class="font-bold">Whoops!</strong>
                                <span class="block sm:inline">Please correct the errors below.</span>
                                {{-- Optionally list only non-wildcard errors here if needed,
                                     but often the general list is enough. --}}
                                <ul class="mt-3 list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif


                         <div class="space-y-6">
                            {{-- Iterate through available classes --}}
                             @forelse($classes as $class)
                                <div class="flex items-center space-x-4 p-3 border rounded-md bg-gray-50">
                                    @php
                                        // Find if this class is currently assigned to the staff member FOR ANY YEAR
                                        // NOTE: $staff->classes might not be loaded yet depending on controller,
                                        // consider loading it: $staff->load('classes') in controller if needed.
                                        // $currentAssignment will be the SchoolClass model if assigned, null otherwise.
                                        $currentAssignment = $staff->classes->firstWhere('id', $class->id);
                                        // Get the specific pivot data if assigned
                                        $assignedYearId = $currentAssignment->pivot->academic_year_id ?? null;
                                    @endphp

                                    {{-- Checkbox to select the class --}}
                                    <input type="checkbox" name="assignments[{{ $class->id }}][class_id]" value="{{ $class->id }}"
                                           id="class_{{ $class->id }}"
                                           class="class-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50 h-5 w-5"
                                           {{-- Check if this class_id was part of the old input OR currently assigned --}}
                                           @checked(old('assignments.'.$class->id.'.class_id', $currentAssignment))>

                                    <label for="class_{{ $class->id }}" class="flex-grow text-sm font-medium text-gray-800">{{ $class->name }}</label>

                                    {{-- Academic Year Selector for this class --}}
                                    <div class="w-1/2">
                                         <select name="assignments[{{ $class->id }}][academic_year_id]"
                                                {{-- Use x-ref for easier access in JS if using Alpine, otherwise standard class/ID is fine --}}
                                                class="year-select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                {{-- Disable if checkbox is not checked initially --}}
                                                {{ !old('assignments.'.$class->id.'.class_id', $currentAssignment) ? 'disabled' : '' }}>
                                            <option value="">-- Select Year --</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}"
                                                        {{-- Select based on old input OR the currently assigned year ID --}}
                                                        @selected(old('assignments.'.$class->id.'.academic_year_id', $assignedYearId) == $year->id)>
                                                     {{-- Display year name or formatted dates --}}
                                                     {{ $year->name ?? ($year->start_date ? $year->start_date->format('Y').'/'.($year->start_date->year + 1) : $year->id) }}
                                                </option>
                                            @endforeach
                                        </select>
                                         {{-- Display errors specific to this year selection --}}
                                        @error('assignments.'.$class->id.'.academic_year_id') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                             @empty
                                 <p class="text-sm text-gray-500 italic">No classes found in the system. Please add classes first.</p>
                            @endforelse
                         </div>

                         {{-- Display only errors for the 'assignments' array itself (e.g., if it's required) --}}
                         <x-input-error :messages="$errors->get('assignments')" class="mt-2" />
                         {{-- REMOVED this line which caused the error: --}}
                         {{-- <x-input-error :messages="$errors->get('assignments.*.class_id')" class="mt-2" /> --}}

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
                const row = checkbox.closest('.flex'); // Find the parent row container
                 if (!row) return; // Skip if structure is wrong
                const yearSelect = row.querySelector('.year-select');
                 if (!yearSelect) return; // Skip if select not found

                checkbox.addEventListener('change', function () {
                    yearSelect.disabled = !this.checked;
                    if (!this.checked) {
                        yearSelect.value = ''; // Clear selection if unchecked
                    }
                });
                // Initial state already set by Blade's conditional 'disabled' attribute
            });
        });
    </script>
</x-app-layout>