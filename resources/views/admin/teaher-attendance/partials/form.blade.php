<div>
    <form method="POST" action="{{ isset($attendanceRecord) ? route('teacher-attendance.update', $attendanceRecord) : route('teacher-attendance.store') }}">
        @csrf
        @if(isset($attendanceRecord))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Date Selection -->
            <div>
                <x-input-label for="date" :value="__('Attendance Date')" />
                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', isset($attendanceRecord) ? $attendanceRecord->date->format('Y-m-d') : now()->format('Y-m-d'))" required {{ isset($attendanceRecord) ? 'readonly' : '' }} />
                <x-input-error :messages="$errors->get('date')" class="mt-2" />
            </div>

            <!-- Teacher Selection (if creating a new record) -->
            @if(!isset($attendanceRecord))
                <div>
                    <x-input-label for="teacher_id" :value="__('Teacher')" />
                    <select id="teacher_id" name="teacher_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="">{{ __('Select Teacher') }}</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }} ({{ $teacher->department }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('teacher_id')" class="mt-2" />
                </div>
            @else
                <!-- Display Teacher Info (if editing) -->
                <div>
                    <x-input-label :value="__('Teacher')" />
                    <div class="block mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50">
                        {{ $attendanceRecord->teacher->name }} ({{ $attendanceRecord->teacher->department }})
                    </div>
                    <input type="hidden" name="teacher_id" value="{{ $attendanceRecord->teacher_id }}">
                </div>
            @endif

            <!-- Attendance Status -->
            <div>
                <x-input-label for="status" :value="__('Attendance Status')" />
                <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    <option value="">{{ __('Select Status') }}</option>
                    <option value="Present" {{ old('status', isset($attendanceRecord) ? $attendanceRecord->status : '') == 'Present' ? 'selected' : '' }}>{{ __('Present') }}</option>
                    <option value="Late" {{ old('status', isset($attendanceRecord) ? $attendanceRecord->status : '') == 'Late' ? 'selected' : '' }}>{{ __('Late') }}</option>
                    <option value="Absent" {{ old('status', isset($attendanceRecord) ? $attendanceRecord->status : '') == 'Absent' ? 'selected' : '' }}>{{ __('Absent') }}</option>
                    <option value="Leave" {{ old('status', isset($attendanceRecord) ? $attendanceRecord->status : '') == 'Leave' ? 'selected' : '' }}>{{ __('On Leave') }}</option>
                    <option value="Half Day" {{ old('status', isset($attendanceRecord) ? $attendanceRecord->status : '') == 'Half Day' ? 'selected' : '' }}>{{ __('Half Day') }}</option>
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>

            <!-- Leave Type (shown only if status is Leave) -->
            <div id="leave_type_container" class="{{ old('status', isset($attendanceRecord) ? $attendanceRecord->status : '') == 'Leave' ? '' : 'hidden' }}">
                <x-input-label for="leave_type" :value="__('Leave Type')" />
                <select id="leave_type" name="leave_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('Select Leave Type') }}</option>
                    <option value="Sick" {{ old('leave_type', isset($attendanceRecord) ? $attendanceRecord->leave_type : '') == 'Sick' ? 'selected' : '' }}>{{ __('Sick Leave') }}</option>
                    <option value="Casual" {{ old('leave_type', isset($attendanceRecord) ? $attendanceRecord->leave_type : '') == 'Casual' ? 'selected' : '' }}>{{ __('Casual Leave') }}</option>
                    <option value="Emergency" {{ old('leave_type', isset($attendanceRecord) ? $attendanceRecord->leave_type : '') == 'Emergency' ? 'selected' : '' }}>{{ __('Emergency Leave') }}</option>
                    <option value="Maternity" {{ old('leave_type', isset($attendanceRecord) ? $attendanceRecord->leave_type : '') == 'Maternity' ? 'selected' : '' }}>{{ __('Maternity Leave') }}</option>
                    <option value="Paternity" {{ old('leave_type', isset($attendanceRecord) ? $attendanceRecord->leave_type : '') == 'Paternity' ? 'selected' : '' }}>{{ __('Paternity Leave') }}</option>
                    <option value="Study" {{ old('leave_type', isset($attendanceRecord) ? $attendanceRecord->leave_type : '') == 'Study' ? 'selected' : '' }}>{{ __('Study Leave') }}</option>
                    <option value="Other" {{ old('leave_type', isset($attendanceRecord) ? $attendanceRecord->leave_type : '') == 'Other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                </select>
                <x-input-error :messages="$errors->get('leave_type')" class="mt-2" />
            </div>

            <!-- Time In -->
            <div id="time_in_container" class="{{ in_array(old('status', isset($attendanceRecord) ? $attendanceRecord->status : ''), ['Present', 'Late', 'Half Day']) ? '' : 'hidden' }}">
                <x-input-label for="time_in" :value="__('Time In')" />
                <x-text-input id="time_in" class="block mt-1 w-full" type="time" name="time_in" :value="old('time_in', isset($attendanceRecord) ? $attendanceRecord->time_in : '')" />
                <x-input-error :messages="$errors->get('time_in')" class="mt-2" />
            </div>

            <!-- Time Out -->
            <div id="time_out_container" class="{{ in_array(old('status', isset($attendanceRecord) ? $attendanceRecord->status : ''), ['Present', 'Late', 'Half Day']) ? '' : 'hidden' }}">
                <x-input-label for="time_out" :value="__('Time Out')" />
                <x-text-input id="time_out" class="block mt-1 w-full" type="time" name="time_out" :value="old('time_out', isset($attendanceRecord) ? $attendanceRecord->time_out : '')" />
                <x-input-error :messages="$errors->get('time_out')" class="mt-2" />
            </div>

            <!-- Notes -->
            <div class="md:col-span-2">
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes', isset($attendanceRecord) ? $attendanceRecord->notes : '') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                {{ __('Cancel') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ isset($attendanceRecord) ? __('Update Attendance Record') : __('Save Attendance Record') }}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        const leaveTypeContainer = document.getElementById('leave_type_container');
        const timeInContainer = document.getElementById('time_in_container');
        const timeOutContainer = document.getElementById('time_out_container');
        
        if (statusSelect && leaveTypeContainer && timeInContainer && timeOutContainer) {
            statusSelect.addEventListener('change', function() {
                // Show/hide leave type based on status
                if (this.value === 'Leave') {
                    leaveTypeContainer.classList.remove('hidden');
                } else {
                    leaveTypeContainer.classList.add('hidden');
                }
                
                // Show/hide time fields based on status
                if (['Present', 'Late', 'Half Day'].includes(this.value)) {
                    timeInContainer.classList.remove('hidden');
                    timeOutContainer.classList.remove('hidden');
                } else {
                    timeInContainer.classList.add('hidden');
                    timeOutContainer.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush