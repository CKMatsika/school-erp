<!-- resources/views/teacher/contents/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Content Details') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('teacher.contents.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back
                </a>
                
                @if($content->status != 'archived')
                <a href="{{ route('teacher.contents.edit', $content) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Edit
                </a>
                @endif
                
                @if($content->status == 'draft')
                <form action="{{ route('teacher.contents.publish', $content) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Publish
                    </button>
                </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Title and Status -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $content->title }}</h1>
                            <p class="text-sm text-gray-600">
                                {{ $content->class->name ?? 'Unknown Class' }} | 
                                {{ $content->subject->name ?? 'Unknown Subject' }}
                            </p>
                        </div>
                        <div class="flex items-center">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($content->status == 'draft') bg-gray-100 text-gray-800
                                @elseif($content->status == 'published') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($content->status) }}
                            </span>
                            <span class="ml-2 px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($content->type == 'document') bg-blue-100 text-blue-800
                                @elseif($content->type == 'video') bg-red-100 text-red-800
                                @elseif($content->type == 'link') bg-purple-100 text-purple-800
                                @elseif($content->type == 'image') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($content->type) }}
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($content->description)
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Description</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-800 whitespace-pre-line">{{ $content->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Content -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Content</h3>
                        
                        @if($content->type == 'document' && $content->file_path)
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <a href="{{ asset('storage/' . $content->file_path) }}" class="text-indigo-600 hover:text-indigo-900 flex items-center" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download Document
                                </a>
                            </div>
                        @elseif($content->type == 'video' && $content->external_url)
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                @if(strpos($content->external_url, 'youtube.com') !== false || strpos($content->external_url, 'youtu.be') !== false)
                                    <div class="aspect-w-16 aspect-h-9">
                                        <iframe src="{{ str_replace('watch?v=', 'embed/', $content->external_url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                @else
                                    <a href="{{ $content->external_url }}" class="text-indigo-600 hover:text-indigo-900" target="_blank">{{ $content->external_url }}</a>
                                @endif
                            </div>
                        @elseif($content->type == 'link' && $content->external_url)
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <a href="{{ $content->external_url }}" class="text-indigo-600 hover:text-indigo-900" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    {{ $content->external_url }}
                                </a>
                            </div>
                        @elseif($content->type == 'image' && $content->file_path)
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <img src="{{ asset('storage/' . $content->file_path) }}" alt="{{ $content->title }}" class="max-w-full h-auto">
                            </div>
                        @endif
                        
                        @if($content->content_text)
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <p class="text-gray-800 whitespace-pre-line">{{ $content->content_text }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Metadata -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Metadata</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Created</span>
                                    <span class="block text-base">{{ $content->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Last Updated</span>
                                    <span class="block text-base">{{ $content->updated_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Created By</span>
                                    <span class="block text-base">{{ $content->teacher->user->name ?? 'Unknown' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>