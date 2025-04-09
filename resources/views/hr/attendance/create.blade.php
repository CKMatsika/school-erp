```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Record Manual Attendance Entry') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 {{-- Ensure controller passes $staffList --}}
                <form method="POST" action="{{ route('hr.attendance.store') }}">
                     @csrf
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">

                         <div>
                            <x-input-label for="staff_id" :value="__('Staff Member')" class="required"/>
                            <select name="staff_id" id="staff_id" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Select Staff --</option>
                                @foreach($staffList as $staff)
                                    <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->full_name }} ({{ $staff->staff_number }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('staff_id')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="attendance_date" :value="__('Attendance Date')" class="required"/>
                                <x-text-input id="attendance_date" class="block mt-1 w-full" type="date" name="attendance_date" :value="old('attendance_date', now()->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('attendance_date')" class="mt-2" />
                            </div>
                             <div>
                                <x-input-label for="status" :value="__('Status')" class="required"/>
                                <select name="status" id="status" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="present" {{ old('status', 'present') == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Late Arrival</option>
                                    <option value="early_departure" {{ old('status') == 'early_departure' ? 'selected' : '' }}>Early Departure</option>
                                    <option value="on_leave" {{ old('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                    <option value="holiday" {{ old('status') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div id="clock_in_div">
                                <x-input-label for="clock_in_time" :value="__('Clock In Time (HH:MM)')" />
                                <x-text-input id="clock_in_time" class="block mt-1 w-full" type="time" name="clock_in_time" :value="old('clock_in_time')" />
                                <x-input-error :messages="$errors->get('clock_in_time')" class="mt-2" />
                            </div>
                             <div id="clock_out_div">
                                <x-input-label for="clock_out_time" :value="__('Clock Out Time (HH:MM)')" />
                                <x-text-input id="clock_out_time" class="block mt-1 w-full" type="time" name="clock_out_time" :value="old('clock_out_time')" />
                                <x-input-error :messages="$errors->get('clock_out_time')" class="mt-2" />
                            </div>
                        </div>

                        <div id="notes_div">
                            <x-input-label for="notes" :value="__('Notes / Reason')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                    </div>

                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('hr.attendance.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Record Attendance') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show/hide time/notes fields based on status
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const clockInDiv = document.getElementById('clock_in_div');
            const clockOutDiv = document.getElementById('clock_out_div');
            const notesDiv = document.getElementById('notes_div');
            const clockInLabel = clockInDiv?.querySelector('label');
            const notesLabel = notesDiv?.querySelector('label');

            function toggleFields() {
                const status = statusSelect.value;
                const showTimes = ['present', 'late', 'early_departure'].includes(status);
                const showNotes = ['absent', 'late', 'early_departure', 'on_leave'].includes(status);
                const requireClockIn = ['present', 'late'].includes(status);
                const requireClockOut = ['present', 'early_departure'].includes(status); // Maybe always optional unless present?
                const requireNotes = ['absent', 'late', 'early_departure', 'on_leave'].includes(status);

                clockInDiv.style.display = showTimes ? '' : 'none';
                clockOutDiv.style.display = showTimes ? '' : 'none';
                notesDiv.style.display = showNotes ? '' : 'none';

                // Add/remove 'required' visual indicator (optional)
                clockInLabel?.classList.toggle('required', requireClockIn);
                notesLabel?.classList.toggle('required', requireNotes);
                // Maybe add required attributes dynamically? Be careful with validation alignment.
            }

            statusSelect.addEventListener('change', toggleFields);
            toggleFields(); // Initial setup
        });
    </script>
</x-app-layout>