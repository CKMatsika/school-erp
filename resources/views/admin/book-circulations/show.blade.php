<x-admin-layout>
    <x-slot name="title">{{ __('Circulation Details') }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Circulation Details') }}
            </h2>
            
            <div class="flex space-x-3">
                @if($circulation->status === 'Issued')
                    <form method="POST" action="{{ route('book-circulations.return', $circulation) }}">
                        @csrf
                        @method('PATCH')
                        <x-primary-button>
                            <i class="fa fa-undo mr-1"></i> {{ __('Return Book') }}
                        </x-primary-button>
                    </form>
                @endif
                
                <x-secondary-button onclick="window.location.href='{{ route('book-circulations.edit', $circulation) }}'">
                    <i class="fa fa-edit mr-1"></i> {{ __('Edit') }}
                </x-secondary-button>
                
                <x-secondary-button onclick="window.location.href='{{ route('book-circulations.index') }}'">
                    <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Circulations') }}
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
            <!-- Circulation Details -->
            <div class="md:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Circulation Information') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Circulation ID') }}</p>
                                <p class="text-base">{{ $circulation->id }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Status') }}</p>
                                <p class="text-base">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $circulation->getStatusBadgeClass() }}">
                                        {{ $circulation->status }}
                                    </span>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Issue Date') }}</p>
                                <p class="text-base">{{ $circulation->issue_date->format('M d, Y') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Due Date') }}</p>
                                <p class="text-base">
                                    {{ $circulation->due_date->format('M d, Y') }}
                                    @if($circulation->is_overdue)
                                        <span class="text-red-600 text-xs font-medium">
                                            ({{ $circulation->days_overdue }} {{ __('days overdue') }})
                                        </span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Return Date') }}</p>
                                <p class="text-base">
                                    {{ $circulation->return_date ? $circulation->return_date->format('M d, Y') : __('Not returned yet') }}
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Fine Amount') }}</p>
                                <p class="text-base">
                                    {{ $circulation->fine_amount ? number_format($circulation->fine_amount, 2) : '0.00' }}
                                    @if($circulation->is_overdue && !$circulation->return_date)
                                        <span class="text-red-600 text-xs font-medium">
                                            ({{ __('Estimated') }}: {{ number_format($circulation->calculateFine(), 2) }})
                                        </span>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500">{{ __('Notes') }}</p>
                                <p class="text-base">{{ $circulation->notes ?: __('No notes provided') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Book Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Book Information') }}</h3>
                            
                            <x-secondary-button onclick="window.location.href='{{ route('books.show', $circulation->book) }}'">
                                <i class="fa fa-eye mr-1"></i> {{ __('View Book') }}
                            </x-secondary-button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Title') }}</p>
                                <p class="text-base">{{ $circulation->book->title }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Book Code') }}</p>
                                <p class="text-base">{{ $circulation->book->book_code }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Author') }}</p>
                                <p class="text-base">{{ $circulation->book->author }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Category') }}</p>
                                <p class="text-base">{{ $circulation->book->category->name ?? '-' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Available Copies') }}</p>
                                <p class="text-base">{{ $circulation->book->available_copies }} / {{ $circulation->book->total_copies }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Current Status') }}</p>
                                <p class="text-base">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $circulation->book->getStatusBadgeClass() }}">
                                        {{ $circulation->book->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Member History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Member Borrowing History') }}</h3>
                            
                            <x-secondary-button onclick="window.location.href='{{ route('book-circulations.index', ['member_id' => $circulation->member_id]) }}'">
                                <i class="fa fa-eye mr-1"></i> {{ __('View All') }}
                            </x-secondary-button>
                        </div>
                        
                        <div class="overflow-x-auto relative">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">{{ __('Book') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Issue Date') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Due Date') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Return Date') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($memberHistory as $record)
                                        <tr class="{{ $record->id === $circulation->id ? 'bg-blue-50' : 'bg-white' }} border-b hover:bg-gray-50">
                                            <td class="py-4 px-6 font-medium text-gray-900">
                                                {{ $record->book->title }}
                                            </td>
                                            <td class="py-4 px-6">
                                                {{ $record->issue_date->format('M d, Y') }}
                                            </td>
                                            <td class="py-4 px-6">
                                                {{ $record->due_date->format('M d, Y') }}
                                            </td>
                                            <td class="py-4 px-6">
                                                {{ $record->return_date ? $record->return_date->format('M d, Y') : __('Not returned') }}
                                            </td>
                                            <td class="py-4 px-6">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $record->getStatusBadgeClass() }}">
                                                    {{ $record->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="bg-white border-b">
                                            <td colspan="5" class="py-4 px-6 text-center text-gray-500">
                                                {{ __('No borrowing history found') }}
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
                        
                        @if($circulation->book->cover_image)
                            <img src="{{ asset('storage/' . $circulation->book->cover_image) }}" alt="{{ $circulation->book->title }}" class="w-full h-auto rounded-lg shadow-md">
                        @else
                            <div class="flex items-center justify-center h-48 bg-gray-100 rounded-lg">
                                <i class="fa fa-book text-4xl text-gray-400"></i>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Member Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Member Information') }}</h3>
                            
                            <x-secondary-button onclick="window.location.href='{{ route('library-members.show', $circulation->member) }}'">
                                <i class="fa fa-eye mr-1"></i> {{ __('View Member') }}
                            </x-secondary-button>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Name') }}</p>
                                <p class="text-base">{{ $circulation->member->name }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Member ID') }}</p>
                                <p class="text-base">{{ $circulation->member->member_id ?: '-' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Member Type') }}</p>
                                <p class="text-base">{{ $circulation->member->member_type }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Contact') }}</p>
                                <p class="text-base">{{ $circulation->member->contact_no ?: '-' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Email') }}</p>
                                <p class="text-base">{{ $circulation->member->email ?: '-' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Status') }}</p>
                                <p class="text-base">
                                    @if($circulation->member->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                    
                                    @if($circulation->member->is_blocked)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('Blocked') }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Quick Actions') }}</h3>
                        
                        <div class="space-y-3">
                            @if($circulation->status === 'Issued')
                                <div>
                                    <form action="{{ route('book-circulations.return', $circulation) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <x-primary-button class="w-full justify-center">
                                            <i class="fa fa-undo mr-1"></i> {{ __('Return Book') }}
                                        </x-primary-button>
                                    </form>
                                </div>
                                
                                <div>
                                    <form action="{{ route('book-circulations.extend', $circulation) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <x-secondary-button class="w-full justify-center">
                                            <i class="fa fa-calendar-plus mr-1"></i> {{ __('Extend Due Date') }}
                                        </x-secondary-button>
                                    </form>
                                </div>
                            @endif
                            
                            <div>
                                <form action="{{ route('book-circulations.print', $circulation) }}" method="GET" target="_blank">
                                    <x-secondary-button class="w-full justify-center">
                                        <i class="fa fa-print mr-1"></i> {{ __('Print Receipt') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                            
                            @if(in_array($circulation->status, ['Pending', 'Reserved']))
                                <div>
                                    <form method="POST" action="{{ route('book-circulations.destroy', $circulation) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this circulation record?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <x-danger-button class="w-full justify-center">
                                            <i class="fa fa-trash mr-1"></i> {{ __('Delete Record') }}
                                        </x-danger-button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>