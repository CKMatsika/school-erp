<!-- resources/views/teacher/online-classes/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Schedule New Online Class') }}
            </h2>
            <a href="{{ route('teacher.online-classes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('teacher.online-classes.store') }}">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Class Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Title -->
                                    <div>
                                        <x-label for="title" :value="__('Class Title')" />
                                        <x-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus placeholder="e.g. Mathematics Review Session" />
                                        @error('title')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Platform -->
                                    <div>
                                        <x-label for="platform" :value="__('Meeting Platform')" />
                                        <select id="platform" name="platform" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="zoom" {{ old('platform') == 'zoom' ? 'selected' : '' }}>Zoom</option>
                                            <option value="google_meet" {{ old('platform') == 'google_meet' ? 'selected' : '' }}>Google Meet</option>
                                            <option value="microsoft_teams" {{ old('platform') == 'microsoft_teams' ? 'selected' : '' }}>Microsoft Teams</option>
                                            <option value="other" {{ old('platform') == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('platform')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Class -->
                                    <div>
                                        <x-label for="class_id" :value="__('Class')" />
                                        <select id="class_id" name="class_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                            <option value="">Select Class</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Subject -->
                                    <div>
                                        <x-label for="subject_id" :value="__('Subject')" />
                                        <select id="subject_id" name="subject_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                            <option value="">Select Subject</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('subject_id')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Date -->
                                    <div>
                                        <x-label for="date" :value="__('Date')" />
                                        <x-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date')" required min="{{ date('Y-m-d') }}" />
                                        @error('date')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Time Range -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <x-label for="start_time" :value="__('Start Time')" />
                                            <x-input id="start_time" class="block mt-1 w-full" type="time" name="start_time" :value="old('start_time')" required />
                                            @error('start_time')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <x-label for="end_time" :value="__('End Time')" />
                                            <x-input id="end_time" class="block mt-1 w-full" type="time" name="end_time" :value="old('end_time')" required />
                                            @error('end_time')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Meeting Link -->
                                    <div class="md:col-span-2">
                                        <x-label for="meeting_link" :value="__('Meeting Link')" />
                                        <x-input id="meeting_link" class="block mt-1 w-full" type="url" name="meeting_link" :value="old('meeting_link')" placeholder="https://" required />
                                        @error('meeting_link')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Password (Optional) -->
                                    <div>
                                        <x-label for="password" :value="__('Meeting Password (Optional)')" />
                                        <x-input id="password" class="block mt-1 w-full" type="text" name="password" :value="old('password')" placeholder="Leave blank if no password is required" />
                                        @error('password')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Recurring -->
                                    <div class="flex items-center mt-6">
                                        <input id="recurring" type="checkbox" name="recurring" value="1" {{ old('recurring') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label for="recurring" class="ml-2 block text-sm text-gray-900">This is a recurring class</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recurring Options (conditionally shown) -->
                        <div id="recurringOptions" class="mb-6 {{ old('recurring') ? '' : 'hidden' }}">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Recurring Options</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Recurrence Pattern -->
                                    <div>
                                        <x-label for="recurrence_pattern" :value="__('Repeat')" />
                                        <select id="recurrence_pattern" name="recurrence_pattern" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="daily" {{ old('recurrence_pattern') == 'daily' ? 'selected' : '' }}>Daily</option>
                                            <option value="weekly" {{ old('recurrence_pattern') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="biweekly" {{ old('recurrence_pattern') == 'biweekly' ? 'selected' : '' }}>Bi-weekly</option>
                                            <option value="monthly" {{ old('recurrence_pattern') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        </select>
                                    </div>

                                    <!-- End Date -->
                                    <div>
                                        <x-label for="recurrence_end_date" :value="__('End Date')" />
                                        <x-input id="recurrence_end_date" class="block mt-1 w-full" type="date" name="recurrence_end_date" :value="old('recurrence_end_date')" min="{{ date('Y-m-d', strtotime('+1 day')) }}" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Class Details</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <x-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" rows="4" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" placeholder="Provide details about the class content, requirements, etc.">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mt-4">
                                    <x-label for="instructions" :value="__('Special Instructions for Students (Optional)')" />
                                    <textarea id="instructions" name="instructions" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" placeholder="Any special instructions or prerequisites for this class...">{{ old('instructions') }}</textarea>
                                    @error('instructions')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Notification Settings</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <input id="notify_students" type="checkbox" name="notify_students" value="1" {{ old('notify_students', '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label for="notify_students" class="ml-2 block text-sm text-gray-900">Send email notification to all students</label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input id="send_reminder" type="checkbox" name="send_reminder" value="1" {{ old('send_reminder', '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label for="send_reminder" class="ml-2 block text-sm text-gray-900">Send reminder 15 minutes before class starts</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('teacher.online-classes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Schedule Class
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const recurringCheckbox = document.getElementById('recurring');
            const recurringOptions = document.getElementById('recurringOptions');

            recurringCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    recurringOptions.classList.remove('hidden');
                } else {
                    recurringOptions.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>