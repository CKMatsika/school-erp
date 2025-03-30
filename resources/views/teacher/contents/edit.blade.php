<!-- resources/views/teacher/contents/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Content') }}
            </h2>
            <a href="{{ route('teacher.contents.show', $content) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
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
                    <form method="POST" action="{{ route('teacher.contents.update', $content) }}" enctype="multipart/form-data">
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
                                        <x-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $content->title)" required autofocus />
                                        @error('title')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Content Type -->
                                    <div>
                                        <x-label for="type" :value="__('Content Type')" />
                                        <select id="type" name="type" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="document" {{ $content->type == 'document' ? 'selected' : '' }}>Document</option>
                                            <option value="video" {{ $content->type == 'video' ? 'selected' : '' }}>Video</option>
                                            <option value="link" {{ $content->type == 'link' ? 'selected' : '' }}>Link</option>
                                            <option value="image" {{ $content->type == 'image' ? 'selected' : '' }}>Image</option>
                                        </select>
                                        @error('type')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Class -->
                                    <div>
                                        <x-label for="class_id" :value="__('Class')" />
                                        <select id="class_id" name="class_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="">Select Class</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ $content->class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
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
                                                <option value="{{ $subject->id }}" {{ $content->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('subject_id')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <x-label for="description" :value="__('Description')" />
                                        <textarea id="description" name="description" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('description', $content->description) }}</textarea>
                                        @error('description')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Details -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Content Details</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <!-- File Upload (for document and image types) -->
                                <div id="fileUploadSection" class="mb-4 {{ in_array($content->type, ['document', 'image']) ? '' : 'hidden' }}">
                                    <x-label for="file" :value="__('Upload File')" />
                                    <input id="file" type="file" name="file" class="block mt-1 w-full" />
                                    @if($content->file_path)
                                        <div class="mt-2 text-sm text-gray-600">
                                            <span>Current file: </span>
                                            <a href="{{ asset('storage/' . $content->file_path) }}" class="text-indigo-600 hover:text-indigo-900" target="_blank">
                                                {{ basename($content->file_path) }}
                                            </a>
                                        </div>
                                    @endif
                                    @error('file')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- External URL (for video and link types) -->
                                <div id="urlSection" class="mb-4 {{ in_array($content->type, ['video', 'link']) ? '' : 'hidden' }}">
                                    <x-label for="external_url" :value="__('External URL')" />
                                    <x-input id="external_url" class="block mt-1 w-full" type="url" name="external_url" :value="old('external_url', $content->external_url)" placeholder="https://" />
                                    @error('external_url')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Content Text (for all types) -->
                                <div>
                                    <x-label for="content_text" :value="__('Additional Content Text')" />
                                    <textarea id="content_text" name="content_text" rows="5" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('content_text', $content->content_text) }}</textarea>
                                    @error('content_text')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('teacher.contents.show', $content) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Update Content
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
            const typeSelect = document.getElementById('type');
            const fileUploadSection = document.getElementById('fileUploadSection');
            const urlSection = document.getElementById('urlSection');

            function toggleSections() {
                const selectedType = typeSelect.value;
                
                if (selectedType === 'document' || selectedType === 'image') {
                    fileUploadSection.classList.remove('hidden');
                    urlSection.classList.add('hidden');
                } else if (selectedType === 'video' || selectedType === 'link') {
                    fileUploadSection.classList.add('hidden');
                    urlSection.classList.remove('hidden');
                }
            }

            typeSelect.addEventListener('change', toggleSections);
        });
    </script>
    @endpush
</x-app-layout>