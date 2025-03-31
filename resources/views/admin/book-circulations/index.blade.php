<x-admin-layout>
    <x-slot name="title">{{ __('Book Circulations') }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Book Circulations') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- Action buttons -->
            <div class="flex justify-between mb-6">
                <div class="flex space-x-4">
                    <x-primary-button onclick="window.location.href='{{ route('book-circulations.create') }}'">
                        <i class="fa fa-plus mr-1"></i> {{ __('Issue Book') }}
                    </x-primary-button>
                    
                    <x-secondary-button onclick="window.location.href='{{ route('book-circulation-reports.index') }}'">
                        <i class="fa fa-file-alt mr-1"></i> {{ __('Reports') }}
                    </x-secondary-button>
                </div>
                
                <div>
                    <form method="GET" action="{{ route('book-circulations.index') }}" class="flex items-center">
                        <x-text-input id="search" class="block w-64" type="text" name="search" 
                            :value="request('search')" placeholder="{{ __('Search by book or member...') }}" />
                        
                        <x-primary-button class="ml-4">
                            <i class="fa fa-search"></i>
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-4">
                @include('administration.book-circulations.partials.filters')
            </div>

            <!-- Book Circulations Table -->
            <div class="overflow-x-auto relative">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3 px-6">{{ __('Book') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Member') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Issue Date') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Due Date') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Return Date') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Status') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($circulations as $circulation)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="py-4 px-6 font-medium text-gray-900">
                                    {{ $circulation->book->title ?? 'Unknown' }} 
                                    <span class="text-xs text-gray-500">({{ $circulation->book->book_code ?? '' }})</span>
                                </td>
                                <td class="py-4 px-6">
                                    {{ $circulation->member->name ?? 'Unknown' }}
                                    @if($circulation->member && $circulation->member->member_id)
                                        <span class="text-xs text-gray-500">({{ $circulation->member->member_id }})</span>
                                    @endif
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
                                    <div class="flex space-x-2">
                                        <a href="{{ route('book-circulations.show', $circulation) }}" class="text-blue-600 hover:text-blue-900">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        
                                        @if($circulation->status === 'Issued')
                                            <form method="POST" action="{{ route('book-circulations.return', $circulation) }}" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-green-600 hover:text-green-900" 
                                                    onclick="return confirm('{{ __('Are you sure you want to mark this book as returned?') }}')">
                                                    <i class="fa fa-undo"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if(in_array($circulation->status, ['Pending', 'Reserved']))
                                            <form method="POST" action="{{ route('book-circulations.destroy', $circulation) }}" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" 
                                                    onclick="return confirm('{{ __('Are you sure you want to delete this circulation record?') }}')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="bg-white border-b">
                                <td colspan="7" class="py-4 px-6 text-center text-gray-500">
                                    {{ __('No circulation records found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $circulations->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>