<!-- resources/views/teacher/assignments/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Assignment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('teacher.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                    <input type="text" id="title" name="title" value="{{ old('title', $assignment->title) }}" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('title')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea id="description" name="description" rows="3" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $assignment->description) }}</textarea>
                                    @error('description')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="instructions" class="block text-sm font-medium text-gray-700">Instructions</label>
                                    <textarea id="instructions" name="instructions" rows="5" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('instructions', $assignment->instructions) }}</textarea>
                                    @error('instructions')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Class and Subject -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Class and Subject</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="class_id" class="block text-sm font-medium text-gray-700">Class</label>
                                    <select id="class_id" name="class_id" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Select a class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id', $assignment->class_id) == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="subject_id" class="block text-sm font-medium text-gray-700">Subject</label>
                                    <select id="subject_id" name="subject_id" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Select a subject</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ old('subject_id', $assignment->subject_id) == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Due Date and Time -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Due Date and Time</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                                    <input type="date" id="due_date" name="due_date" value="{{ old('due_date', $assignment->due_date->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('due_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="due_time" class="block text-sm font-medium text-gray-700">Due Time (Optional)</label>
                                    <input type="time" id="due_time" name="due_time" value="{{ old('due_time', $assignment->due_time ? \Carbon\Carbon::parse($assignment->due_time)->format('H:i') : '') }}" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('due_time')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submission Settings -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Submission Settings</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="submission_type" class="block text-sm font-medium text-gray-700">Submission Type</label>
                                    <select id="submission_type" name="submission_type" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="online" {{ old('submission_type', $assignment->submission_type) == 'online' ? 'selected' : '' }}>Online</option>
                                        <option value="offline" {{ old('submission_type', $assignment->submission_type) == 'offline' ? 'selected' : '' }}>Offline</option>
                                        <option value="both" {{ old('submission_type', $assignment->submission_type) == 'both' ? 'selected' : '' }}>Both</option>
                                    </select>
                                    @error('submission_type')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="max_score" class="block text-sm font-medium text-gray-700">Maximum Score</label>
                                    <input type="number" id="max_score" name="max_score" min="1" value="{{ old('max_score', $assignment->max_score) }}" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('max_score')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Marking Scheme -->
                        <div class="mb-6">
                            <label for="marking_scheme_id" class="block text-sm font-medium text-gray-700">Marking Scheme (Optional)</label>
                            <select id="marking_scheme_id" name="marking_scheme_id" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">None</option>
                                @foreach($markingSchemes as $scheme)
                                    <option value="{{ $scheme->id }}" {{ old('marking_scheme_id', $assignment->marking_scheme_id) == $scheme->id ? 'selected' : '' }}>
                                        {{ $scheme->title }} ({{ $scheme->total_marks }} marks)
                                    </option>
                                @endforeach
                            </select>
                            @error('marking_scheme_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Attachment -->
                        <div class="mb-6">
                            <label for="attachment" class="block text-sm font-medium text-gray-700">Attachment</label>
                            @if($assignment->attachment_path)
                                <div class="flex items-center mt-1 mb-2">
                                    <span class="mr-2">Current attachment:</span>
                                    <a href="{{ asset('storage/' . $assignment->attachment_path) }}" class="text-indigo-600 hover:text-indigo-900">{{ basename($assignment->attachment_path) }}</a>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" id="remove_attachment" name="remove_attachment" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label for="remove_attachment" class="ml-2 text-sm text-gray-700">Remove current attachment</label>
                                </div>
                            @endif
                            <input type="file" id="attachment" name="attachment" class="mt-1 block w-full">
                            @error('attachment')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex items-center justify-end">
                            <a href="{{ route('teacher.assignments.show', $assignment) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <button type="submit" name="action" value="draft" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Save as Draft
                            </button>
                            <button type="submit" name="action" value="publish" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Publish
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>