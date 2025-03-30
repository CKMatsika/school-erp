<!-- resources/views/teacher/marking-schemes/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Marking Scheme') }}
            </h2>
            <a href="{{ route('teacher.marking-schemes.show', $markingScheme) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
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
                    <form method="POST" action="{{ route('teacher.marking-schemes.update', $markingScheme) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Basic Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Title -->
                                    <div>
                                        <x-label for="title" :value="__('Title')" />
                                        <x-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $markingScheme->title)" required autofocus />
                                        @error('title')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Assessment Type -->
                                    <div>
                                        <x-label for="assessment_type" :value="__('Assessment Type')" />
                                        <select id="assessment_type" name="assessment_type" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="exam" {{ $markingScheme->assessment_type == 'exam' ? 'selected' : '' }}>Exam</option>
                                            <option value="test" {{ $markingScheme->assessment_type == 'test' ? 'selected' : '' }}>Test</option>
                                            <option value="quiz" {{ $markingScheme->assessment_type == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                            <option value="assignment" {{ $markingScheme->assessment_type == 'assignment' ? 'selected' : '' }}>Assignment</option>
                                            <option value="project" {{ $markingScheme->assessment_type == 'project' ? 'selected' : '' }}>Project</option>
                                        </select>
                                        @error('assessment_type')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Class -->
                                    <div>
                                        <x-label for="class_id" :value="__('Class')" />
                                        <select id="class_id" name="class_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="">Select Class</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ $markingScheme->class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Subject -->
                                    <div>
                                        <x-label for="subject_id" :value="__('Subject')" />
                                        <select id="subject_id" name="subject_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="">Select Subject</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ $markingScheme->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('subject_id')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Total Marks -->
                                    <div>
                                        <x-label for="total_marks" :value="__('Total Marks')" />
                                        <x-input id="total_marks" class="block mt-1 w-full" type="number" name="total_marks" :value="old('total_marks', $markingScheme->total_marks)" min="1" required />
                                        @error('total_marks')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Passing Marks -->
                                    <div>
                                        <x-label for="passing_marks" :value="__('Passing Marks')" />
                                        <x-input id="passing_marks" class="block mt-1 w-full" type="number" name="passing_marks" :value="old('passing_marks', $markingScheme->passing_marks)" min="1" required />
                                        @error('passing_marks')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <x-label for="description" :value="__('Description')" />
                                        <textarea id="description" name="description" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('description', $markingScheme->description) }}</textarea>
                                        @error('description')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Marking Criteria -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-700">Marking Criteria</h3>
                                <button type="button" id="addCriterionBtn" class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Add Criterion
                                </button>
                            </div>
                            <div id="criteriaContainer" class="space-y-4">
                                @foreach($markingScheme->criteria as $index => $criterion)
                                <div class="criterion-item bg-gray-50 p-4 rounded-lg">
                                    <input type="hidden" name="criteria[{{ $index }}][id]" value="{{ $criterion->id }}">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                        <div class="md:col-span-5">
                                            <x-label for="criteria[{{ $index }}][name]" :value="__('Criterion Name')" />
                                            <x-input id="criteria[{{ $index }}][name]" class="block mt-1 w-full" type="text" name="criteria[{{ $index }}][name]" :value="$criterion->name" required />
                                        </div>
                                        <div class="md:col-span-3">
                                            <x-label for="criteria[{{ $index }}][marks]" :value="__('Marks')" />
                                            <x-input id="criteria[{{ $index }}][marks]" class="block mt-1 w-full" type="number" name="criteria[{{ $index }}][marks]" :value="$criterion->marks" min="1" required />
                                        </div>
                                        <div class="md:col-span-3">
                                            <x-label for="criteria[{{ $index }}][weight]" :value="__('Weight (%)')" />
                                            <x-input id="criteria[{{ $index }}][weight]" class="block mt-1 w-full" type="number" name="criteria[{{ $index }}][weight]" :value="$criterion->weight" min="1" max="100" required />
                                        </div>
                                        <div class="md:col-span-1 pt-6">
                                            <button type="button" class="remove-criterion text-red-500 hover:text-red-700" {{ $markingScheme->criteria->count() <= 1 ? 'disabled' : '' }}>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <x-label for="criteria[{{ $index }}][description]" :value="__('Description')" />
                                        <textarea id="criteria[{{ $index }}][description]" name="criteria[{{ $index }}][description]" rows="2" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ $criterion->description }}</textarea>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- File Upload (optional) -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Additional Materials (Optional)</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <x-label for="file" :value="__('Upload Marking Scheme Document')" />
                                    <input id="file" type="file" name="file" class="block mt-1 w-full" />
                                    @if($markingScheme->file_path)
                                        <div class="mt-2 text-sm text-gray-600">
                                            <span>Current file: </span>
                                            <a href="{{ asset('storage/' . $markingScheme->file_path) }}" class="text-indigo-600 hover:text-indigo-900" target="_blank">
                                                {{ basename($markingScheme->file_path) }}
                                            </a>
                                        </div>
                                    @endif
                                    <p class="text-sm text-gray-500 mt-1">Upload PDF, Word, or Excel document with detailed marking scheme.</p>
                                    @error('file')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mt-4">
                                    <x-label for="notes" :value="__('Additional Notes')" />
                                    <textarea id="notes" name="notes" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('notes', $markingScheme->notes) }}</textarea>
                                    @error('notes')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('teacher.marking-schemes.show', $markingScheme) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button type="submit" name="action" value="update" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Update
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
            const criteriaContainer = document.getElementById('criteriaContainer');
            const addCriterionBtn = document.getElementById('addCriterionBtn');
            let criterionCount = {{ $markingScheme->criteria->count() }};

            // Add event listeners to existing remove buttons
            document.querySelectorAll('.remove-criterion').forEach(button => {
                if(!button.hasEventListener) {
                    button.addEventListener('click', function() {
                        if (criteriaContainer.children.length > 1) {
                            this.closest('.criterion-item').remove();
                        }
                    });
                    button.hasEventListener = true;
                }
            });

            addCriterionBtn.addEventListener('click', function() {
                const newCriterion = document.createElement('div');
                newCriterion.className = 'criterion-item bg-gray-50 p-4 rounded-lg';
                newCriterion.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                        <div class="md:col-span-5">
                            <x-label for="criteria[${criterionCount}][name]" :value="__('Criterion Name')" />
                            <input id="criteria[${criterionCount}][name]" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" type="text" name="criteria[${criterionCount}][name]" required />
                        </div>
                        <div class="md:col-span-3">
                            <x-label for="criteria[${criterionCount}][marks]" :value="__('Marks')" />
                            <input id="criteria[${criterionCount}][marks]" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" type="number" name="criteria[${criterionCount}][marks]" min="1" required />
                        </div>
                        <div class="md:col-span-3">
                            <x-label for="criteria[${criterionCount}][weight]" :value="__('Weight (%)')" />
                            <input id="criteria[${criterionCount}][weight]" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" type="number" name="criteria[${criterionCount}][weight]" min="1" max="100" required />
                        </div>
                        <div class="md:col-span-1 pt-6">
                            <button type="button" class="remove-criterion text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <x-label for="criteria[${criterionCount}][description]" :value="__('Description')" />
                        <textarea id="criteria[${criterionCount}][description]" name="criteria[${criterionCount}][description]" rows="2" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full"></textarea>
                    </div>
                `;
                criteriaContainer.appendChild(newCriterion);
                criterionCount++;

                // Enable all remove buttons when we have more than one criterion
                if (criteriaContainer.children.length > 1) {
                    document.querySelectorAll('.remove-criterion').forEach(button => {
                        button.disabled = false;
                    });
                }

                // Add event listeners to new remove buttons
                document.querySelectorAll('.remove-criterion').forEach(button => {
                    if(!button.hasEventListener) {
                        button.addEventListener('click', function() {
                            if (criteriaContainer.children.length > 1) {
                                this.closest('.criterion-item').remove();
                                
                                // If only one criterion is left, disable its remove button
                                if (criteriaContainer.children.length <= 1) {
                                    document.querySelectorAll('.remove-criterion').forEach(btn => {
                                        btn.disabled = true;
                                    });
                                }
                            }
                        });
                        button.hasEventListener = true;
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>