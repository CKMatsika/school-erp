<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Assign Subjects to: {{ $staff->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Ensure controller passes $staff, $subjects, $assignedSubjectIds --}}
                <form method="POST" action="{{ route('hr.staff.assign-subjects.sync', $staff) }}">
                     @csrf
                    <div class="p-6 bg-white border-b border-gray-200">
                         <p class="mb-4 text-sm text-gray-600">Select the subjects taught by this staff member.</p>

                         <div class="space-y-4 max-h-96 overflow-y-auto border p-4 rounded-md">
                            @forelse($subjects as $subject)
                                <label class="flex items-center">
                                    <input type="checkbox" name="subjects[]" value="{{ $subject->id }}"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50"
                                           {{ in_array($subject->id, old('subjects', $assignedSubjectIds ?? [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $subject->name }} {{ $subject->subject_code ? '('.$subject->subject_code.')' : '' }}</span>
                                </label>
                             @empty
                                <p class="text-sm text-gray-500 italic">No subjects found in the system. Please add subjects first.</p>
                            @endforelse
                         </div>
                         <x-input-error :messages="$errors->get('subjects')" class="mt-2" />
                         <x-input-error :messages="$errors->get('subjects.*')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('hr.staff.show', $staff) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Update Assigned Subjects') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>