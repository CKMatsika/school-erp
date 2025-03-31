<x-admin-layout>
    <x-slot name="title">{{ __('Book Details') }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Book Details') }}: {{ $book->title }}
            </h2>
            
            <div class="flex space-x-3">
                <x-secondary-button onclick="window.location.href='{{ route('book-circulations.create', ['book_id' => $book->id]) }}'">
                    <i class="fa fa-exchange-alt mr-1"></i> {{ __('Issue Book') }}
                </x-secondary-button>
                
                <x-secondary-button onclick="window.location.href='{{ route('books.edit', $book) }}'">
                    <i class="fa fa-edit mr-1"></i> {{ __('Edit') }}
                </x-secondary-button>
                
                <x-secondary-button onclick="window.location.href='{{ route('books.index') }}'">
                    <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Books') }}
                </x-secondary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <!-- Alert Messages -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Book Details -->
            <div class="md:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Book Information') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Book Code') }}</p>
                                <p class="text-base">{{ $book->book_code }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Status') }}</p>
                                <p class="text-base">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $book->getStatusBadgeClass() }}">
                                        {{ $book->status }}
                                    </span>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Author') }}</p>
                                <p class="text-base">{{ $book->author }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Category') }}</p>
                                <p class="text-base">{{ $book->category->name ?? '-' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Publisher') }}</p>
                                <p class="text-base">{{ $book->publisher ?: __('Not specified') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Publication Year') }}</p>
                                <p class="text-base">{{ $book->publication_year ?: __('Not specified') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('ISBN') }}</p>
                                <p class="text-base">{{ $book->isbn ?: __('Not specified') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Location in Library') }}</p>
                                <p class="text-base">{{ $book->location ?: __('Not specified') }}</p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500">{{ __('Description') }}</p>
                                <p class="text-base">{{ $book->description ?: __('No description available') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Inventory Status') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="bg-blue-50 rounded-lg p-4 text-center">
                                    <p class="text-sm font-medium text-gray-500 mb-1">{{ __('Total Copies') }}</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ $book->total_copies }}</p>
                                </div>
                            </div>
                            
                            <div>
                                <div class="bg-green-50 rounded-lg p-4 text-center">
                                    <p class="text-sm font-medium text-gray-500 mb-1">{{ __('Available Copies') }}</p>
                                    <p class="text-2xl font-bold text-green-600">{{ $book->available_copies }}</p>
                                </div>
                            </div>
                            
                            <div>
                                <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                    <p class="text-sm font-medium text-gray-500 mb-1">{{ __('Issued Copies') }}</p>
                                    <p class="text-2xl font-bold text-yellow-600">{{ $book->total_copies - $book->available_copies }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Circulation History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Circulation History') }}</h3>
                            
                            <x-secondary-button onclick="window.location.href='{{ route('book-circulations.index', ['book_id' => $book->id]) }}'">
                                <i class="fa fa-eye mr-1"></i> {{ __('View All') }}
                            </x-secondary-button>
                        </div>
                        
                        <div class="overflow-x-auto relative">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">{{ __('Member') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Issue Date') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Due Date') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Return Date') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Status') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($circulations as $circulation)
                                        <tr class="bg-white border-b hover:bg-gray-50">
                                            <td class="py-4 px-6">
                                                {{ $circulation->member->name ?? $circulation->member_id }}
                                            </td>
                                            <td class="py-4 px-6">
                                                {{ $circulation->issue_date->format('M d, Y') }}
                                            </td>
                                            <td class="py-4 px-6">
                                                {{ $circulation->due_date->format('M d, Y') }}
                                                @if($circulation->is_overdue)
                                                    <span class="text-red-600 text-xs font-medium">
                                                        ({{ $circulation->days_overdue }} {{ __('days overdue') }})
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6">
                                                {{ $circulation->return_date ? $circulation->return_date->format('M d, Y') : __('Not returned') }}
                                            </td>
                                            <td class="py-4 px-6">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $circulation->getStatusBadgeClass() }}">
                                                    {{ $circulation->status }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-6">
                                                <a href="{{ route('book-circulations.show', $circulation) }}" class="text-blue-600 hover:text-blue-900">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="bg-white border-b">
                                            <td colspan="6" class="py-4 px-6 text-center text-gray-500">
                                                {{ __('No circulation history found') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="md:col-span-1">
                <!-- Book Cover Image -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Book Cover') }}</h3>
                        
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-full h-auto rounded-lg shadow-md">
                        @else
                            <div class="flex items-center justify-center h-48 bg-gray-100 rounded-lg">
                                <i class="fa fa-book text-4xl text-gray-400"></i>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Quick Actions') }}</h3>
                        
                        <div class="space-y-3">
                            @if($book->available_copies > 0)
                                <div>
                                    <a href="{{ route('book-circulations.create', ['book_id' => $book->id]) }}" class="w-full block">
                                        <x-primary-button class="w-full justify-center">
                                            <i class="fa fa-exchange-alt mr-1"></i> {{ __('Issue Book') }}
                                        </x-primary-button>
                                    </a>
                                </div>
                            @endif
                            
                            <div>
                                <form action="{{ route('books.print', $book) }}" method="GET" target="_blank">
                                    <x-secondary-button class="w-full justify-center">
                                        <i class="fa fa-print mr-1"></i> {{ __('Print Details') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                            
                            <div>
                                <form action="{{ route('books.barcode', $book) }}" method="GET" target="_blank">
                                    <x-secondary-button class="w-full justify-center">
                                        <i class="fa fa-barcode mr-1"></i> {{ __('Generate Barcode') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                            
                            <div>
                                <form action="{{ route('books.duplicate', $book) }}" method="POST">
                                    @csrf
                                    <x-secondary-button class="w-full justify-center">
                                        <i class="fa fa-copy mr-1"></i> {{ __('Duplicate Book') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                            
                            <div>
                                <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this book?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button class="w-full justify-center">
                                        <i class="fa fa-trash mr-1"></i> {{ __('Delete Book') }}
                                    </x-danger-button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Books -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Related Books') }}</h3>
                        
                        @if($relatedBooks->count() > 0)
                            <ul class="space-y-3">
                                @foreach($relatedBooks as $relatedBook)
                                    <li class="border-b border-gray-200 pb-2 last:border-0 last:pb-0">
                                        <a href="{{ route('books.show', $relatedBook) }}" class="hover:text-blue-600">
                                            <p class="font-medium">{{ $relatedBook->title }}</p>
                                            <p class="text-sm text-gray-500">{{ $relatedBook->author }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500 text-center py-4">{{ __('No related books found') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>