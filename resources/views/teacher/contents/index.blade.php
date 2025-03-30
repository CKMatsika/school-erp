<!-- resources/views/teacher/contents/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Learning Content') }}
            </h2>
            <a href="{{ route('teacher.contents.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add New Content
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Search and Filter -->
                    <div class="mb-6">
                        <form action="{{ route('teacher.contents.index') }}" method="GET" class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <input type="text" name="search" placeholder="Search content..." class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ request('search') }}">
                            </div>
                            <div>
                                <select name="type" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">All Types</option>
                                    <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Document</option>
                                    <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
                                    <option value="link" {{ request('type') == 'link' ? 'selected' : '' }}>Link</option>
                                    <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Image</option>
                                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div>
                                <select name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">All Statuses</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Content Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($contents as $content)
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div class="p-4">
                                    <!-- Content Type Icon -->
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="rounded-full w-10 h-10 flex items-center justify-center
                                            @if($content->type == 'document') bg-blue-100 text-blue-600
                                            @elseif($content->type == 'video') bg-red-100 text-red-600
                                            @elseif($content->type == 'link') bg-purple-100 text-purple-600
                                            @elseif($content->type == 'image') bg-green-100 text-green-600
                                            @else bg-gray-100 text-gray-600 @endif
                                        ">
                                            @if($content->type == 'document')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                                </svg>
                                            @elseif($content->type == 'video')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                                                </svg>
                                            @elseif($content->type == 'link')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                                                </svg>
                                            @elseif($content->type == 'image')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </div>
                                        
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($content->status == 'draft') bg-gray-100 text-gray-800
                                            @elseif($content->status == 'published') bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($content->status) }}
                                        </span>
                                    </div>
                                    
                                    <!-- Content Info -->
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ $content->title }}</h3>
                                    <p class="text-sm text-gray-600 mb-3">
                                        {{ $content->class->name ?? 'Unknown Class' }} | 
                                        {{ $content->subject->name ?? 'Unknown Subject' }}
                                    </p>
                                    
                                    @if($content->description)
                                        <p class="text-sm text-gray-700 mb-4 line-clamp-2">{{ $content->description }}</p>
                                    @endif
                                    <!-- Continuing resources/views/teacher/contents/index.blade.php -->
                                    <!-- Content Actions -->
                                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($content->created_at)->format('M d, Y') }}
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('teacher.contents.show', $content) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                                            @if($content->status != 'archived')
                                            <a href="{{ route('teacher.contents.edit', $content) }}" class="text-yellow-600 hover:text-yellow-900 text-sm">Edit</a>
                                            @endif
                                            @if($content->status == 'draft')
                                            <form class="inline-block" action="{{ route('teacher.contents.destroy', $content) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this content?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 bg-white rounded-lg border border-gray-200 p-6 text-center text-gray-500">
                                No content found. <a href="{{ route('teacher.contents.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first content</a>.
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $contents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>