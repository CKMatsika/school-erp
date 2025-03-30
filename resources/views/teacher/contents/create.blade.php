<!-- resources/views/teacher/contents/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Learning Content') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('teacher.contents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                    <input type="text" id="title" name="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('title')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                                    @error('description')
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
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
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
                                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
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
                        
                        <!-- Content Type and Details -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Content Type and Details</h3>
                            
                            <div class="mb-4">
                                <label for="type" class="block text-sm font-medium text-gray-700">Content Type</label>
                                <select id="type" name="type" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="document" {{ old('type') == 'document' ? 'selected' : '' }}>Document</option>
                                    <option value="video" {{ old('type') == 'video' ? 'selected' : '' }}>Video</option>
                                    <option value="link" {{ old('type') == 'link' ? 'selected' : '' }}>Link</option>
                                    <option value="image" {{ old('type') == 'image' ? 'selected' : '' }}>Image</option>
                                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Document Upload -->
                            <div id="document-upload" class="content-type-field">
                                <label for="file" class="block text-sm font-medium text-gray-700">Upload Document</label>
                                <input type="file" id="file" name="file" class="mt-1 block w-full">
                                <p class="text-xs text-gray-500 mt-1">Accepted file types: PDF, DOCX, PPT, etc.</p>
                                @error('file')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Video URL -->
                            <div id="video-url" class="content-type-field hidden">
                                <label for="external_url" class="block text-sm font-medium text-gray-700">Video URL</label>
                                <input type="url" id="external_url" name="external_url" value="{{ old('external_url') }}" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <p class="text-xs text-gray-500 mt-1">YouTube, Vimeo, or other video platform URL</p>
                                @error('external_url')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- External Link -->
                            <div id="external-link" class="content-type-field hidden">
                                <label for="external_url" class="block text-sm font-medium text-gray-700">External Link</label>
                                <input type="url" id="external_url" name="external_url" value="{{ old('external_url') }}" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <p class="text-xs text-gray-500 mt-1">Link to external resource</p>
                                @error('external_url')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Image Upload -->
                            <div id="image-upload" class="content-type-field hidden">
                                <label for="file" class="block text-sm font-medium text-gray-700">Upload Image</label>
                                <input type="file" id="file" name="file" accept="image/*" class="mt-1 block w-full">
                                <p class="text-xs text-gray-500 mt-1">Accepted file types: JPG, PNG, GIF, etc.</p>
                                @error('file')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Text Content -->
                            <div id="content-text" class="content-type-field mt-4">
                                <label for="content_text" class="block text-sm font-medium text-gray-700">Content Text (Optional)</label>
                                <textarea id="content_text" name="content_text" rows="6" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('content_text') }}</textarea>
                                @error('content_text')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Publish Settings -->
                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex items-center justify-end">
                            <a href="{{ route('teacher.contents.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Create Content
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
            const contentTypeDivs = document.querySelectorAll('.content-type-field');
            
            // Function to show/hide content type fields
            function toggleContentTypeFields() {
                const selectedType = typeSelect.value;
                
                // Hide all content type fields
                contentTypeDivs.forEach(div => {
                    div.classList.add('hidden');
                });
                
                // Show relevant content type field
                if (selectedType === 'document') {
                    document.getElementById('document-upload').classList.remove('hidden');
                    document.getElementById('content-text').classList.remove('hidden');
                } else if (selectedType === 'video') {
                    document.getElementById('video-url').classList.remove('hidden');
                    document.getElementById('content-text').classList.remove('hidden');
                } else if (selectedType === 'link') {
                    document.getElementById('external-link').classList.remove('hidden');
                    document.getElementById('content-text').classList.remove('hidden');
                } else if (selectedType === 'image') {
                    document.getElementById('image-upload').classList.remove('hidden');
                    document.getElementById('content-text').classList.remove('hidden');
                } else {
                    document.getElementById('content-text').classList.remove('hidden');
                }
            }
            
            // Initial toggle based on selected value
            toggleContentTypeFields();
            
            // Add event listener for changes
            typeSelect.addEventListener('change', toggleContentTypeFields);
        });
    </script>
    @endpush
</x-app-layout>