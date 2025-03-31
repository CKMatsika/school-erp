<x-admin-layout>
    <x-slot name="title">{{ __('Books') }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Books') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- Action buttons -->
            <div class="flex justify-between mb-6">
                <div class="flex space-x-4">
                    <x-primary-button onclick="window.location.href='{{ route('books.create') }}'">
                        <i class="fa fa-plus mr-1"></i> {{ __('New Book') }}
                    </x-primary-button>
                    
                    <x-secondary-button onclick="window.location.href='{{ route('books.import') }}'">
                        <i class="fa fa-file-import mr-1"></i> {{ __('Import Books') }}
                    </x-secondary-button>
                    
                    <x-secondary-button onclick="window.location.href='{{ route('book-reports.index') }}'">
                        <i class="fa fa-file-alt mr-1"></i> {{ __('Reports') }}
                    </x-secondary-button>
                </div>
                
                <div>
                    <form method="GET" action="{{ route('books.index') }}" class="flex items-center">
                        <x-text-input id="search" class="block w-64" type="text" name="search" 
                            :value="request('search')" placeholder="{{ __('Search books...') }}" />
                        
                        <x-primary-button class="ml-4">
                            <i class="fa fa-search"></i>
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-4">
                @include('administration.books.partials.filters')
            </div>

            <!-- Books Table -->
            <div class="overflow-x-auto relative">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3 px-6">{{ __('Book Code') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Title') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Author') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Category') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Status') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Copies') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($books as $book)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="py-4 px-6">
                                    {{ $book->book_code }}
                                </td>
                                <td class="py-4 px-6 font-medium text-gray-900">
                                    {{ $book->title }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $book->author }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $book->category->name ?? '-' }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $book->getStatusBadgeClass() }}">
                                        {{ $book->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-medium">{{ $book->total_copies }}</span>
                                    <span class="text-xs text-gray-500">({{ $book->available_copies }} available)</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('books.show', $book) }}" class="text-blue-600 hover:text-blue-900">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('books.edit', $book) }}" class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        
                                        <form method="POST" action="{{ route('books.destroy', $book) }}" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" 
                                                onclick="return confirm('{{ __('Are you sure you want to delete this book?') }}')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="bg-white border-b">
                                <td colspan="7" class="py-4 px-6 text-center text-gray-500">
                                    {{ __('No books found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $books->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>