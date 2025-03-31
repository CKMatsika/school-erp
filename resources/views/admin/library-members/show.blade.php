<x-admin-layout>
    <x-slot name="title">{{ __('Library Member Details') }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Library Member Details') }}: {{ $member->name }}
            </h2>
            
            <div class="flex space-x-3">
                <x-secondary-button onclick="window.location.href='{{ route('book-circulations.create', ['member_id' => $member->id]) }}'">
                    <i class="fa fa-book mr-1"></i> {{ __('Issue Book') }}
                </x-secondary-button>
                
                <x-secondary-button onclick="window.location.href='{{ route('library-members.edit', $member) }}'">
                    <i class="fa fa-edit mr-1"></i> {{ __('Edit') }}
                </x-secondary-button>
                
                <x-secondary-button onclick="window.location.href='{{ route('library-members.index') }}'">
                    <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Members') }}
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
            <!-- Member Details -->
            <div class="md:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Member Information') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Name') }}</p>
                                <p class="text-base">{{ $member->name }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Member ID') }}</p>
                                <p class="text-base">{{ $member->member_id ?: __('Not assigned') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Member Type') }}</p>
                                <p class="text-base">{{ $member->member_type }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Status') }}</p>
                                <p class="text-base">
                                    @if($member->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                    
                                    @if($member->is_blocked)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 ml-2">
                                            {{ __('Blocked') }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Email') }}</p>
                                <p class="text-base">{{ $member->email ?: __('Not provided') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Contact Number') }}</p>
                                <p class="text-base">{{ $member->contact_no ?: __('Not provided') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Date of Birth') }}</p>
                                <p class="text-base">{{ $member->date_of_birth ? $member->date_of_birth->format('M d, Y') : __('Not provided') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Address') }}</p>
                                <p class="text-base">{{ $member->address ?: __('Not provided') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Membership Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Membership Information') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="bg-blue-50 rounded-lg p-4 text-center">
                                    <p class="text-sm font-medium text-gray-500 mb-1">{{ __('Membership Date') }}</p>
                                    <p class="text-base font-bold text-blue-600">{{ $member->membership_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                            
                            <div>
                                <div class="bg-blue-50 rounded-lg p-4 text-center">
                                    <p class="text-sm font-medium text-gray-500 mb-1">{{ __('Expiry Date') }}</p>
                                    <p class="text-base font-bold text-blue-600">
                                        {{ $member->expiry_date->format('M d, Y') }}
                                        @if($member->expiry_date->isPast())
                                            <span class="block text-xs font-medium text-red-600">{{ __('Expired') }}</span>
                                        @elseif($member->expiry_date->diffInDays(now()) < 30)
                                            <span class="block text-xs font-medium text-yellow-600">{{ __('Expires soon') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div>
                                <div class="bg-blue-50 rounded-lg p-4 text-center">
                                    <p class="text-sm font-medium text-gray-500 mb-1">{{ __('Max Books Allowed') }}</p>
                                    <p class="text-base font-bold text-blue-600">{{ $member->max_books_allowed }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($member->is_blocked)
                            <div class="mt-4 p-4 bg-red-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-500">{{ __('Block Reason') }}</p>
                                <p class="text-base text-red-600">{{ $member->block_reason ?: __('No reason provided') }}</p>
                            </div>
                        @endif
                        
                        @if($member->notes)
                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('Notes') }}</p>
                                <p class="text-base">{{ $member->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Borrowing History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Borrowing History') }}</h3>
                            
                            <x-secondary-button onclick="window.location.href='{{ route('book-circulations.index', ['member_id' => $member->id]) }}'">
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
                                        <th scope="col" class="py-3 px-6">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($circulations as $circulation)
                                        <tr class="bg-white border-b hover:bg-gray-50">
                                            <td class="py-4 px-6 font-medium text-gray-900">
                                                {{ $circulation->book->title ?? 'Unknown' }}
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
                                                {{ __('No borrowing history found') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination for circulations if needed -->
                        @if($circulations->count() > 0)
                            <div class="mt-4 text-center">
                                <a href="{{ route('book-circulations.index', ['member_id' => $member->id]) }}" class="text-sm text-blue-600 hover:text-blue-900">
                                    {{ __('View All Circulation Records') }} &rarr;
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="md:col-span-1">
                <!-- Member Photo -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Member Photo') }}</h3>
                        
                        @if($member->photo)
                            <img src="{{ asset('storage/' . $member->photo) }}" alt="{{ $member->name }}" class="w-full h-auto rounded-lg shadow-md">
                        @else
                            <div class="flex items-center justify-center h-48 bg-gray-100 rounded-lg">
                                <i class="fa fa-user text-4xl text-gray-400"></i>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Current Books -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Current Books') }}</h3>
                        
                        @if($currentBooks->count() > 0)
                            <ul class="space-y-3">
                                @foreach($currentBooks as $circulation)
                                    <li class="border-b border-gray-200 pb-2 last:border-0 last:pb-0">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="font-medium">{{ $circulation->book->title ?? 'Unknown' }}</p>
                                                <p class="text-sm text-gray-500">
                                                    {{ __('Due') }}: {{ $circulation->due_date->format('M d, Y') }}
                                                    @if($circulation->is_overdue)
                                                        <span class="text-red-600 font-medium">
                                                            ({{ $circulation->days_overdue }} {{ __('days overdue') }})
                                                        </span>
                                                    @endif
                                                </p>
                                            </div>
                                            
                                            <a href="{{ route('book-circulations.show', $circulation) }}" class="text-blue-600 hover:text-blue-900">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500 text-center py-4">{{ __('No books currently issued') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Quick Actions') }}</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <a href="{{ route('book-circulations.create', ['member_id' => $member->id]) }}" class="w-full block">
                                    <x-primary-button class="w-full justify-center">
                                        <i class="fa fa-book mr-1"></i> {{ __('Issue Book') }}
                                    </x-primary-button>
                                </a>
                            </div>
                            
                            <div>
                                <form action="{{ route('library-members.print-card', $member) }}" method="GET" target="_blank">
                                    <x-secondary-button class="w-full justify-center">
                                        <i class="fa fa-id-card mr-1"></i> {{ __('Print Member Card') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                            
                            @if($member->is_blocked)
                                <div>
                                    <form action="{{ route('library-members.unblock', $member) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <x-secondary-button class="w-full justify-center">
                                            <i class="fa fa-unlock mr-1"></i> {{ __('Unblock Member') }}
                                        </x-secondary-button>
                                    </form>
                                </div>
                            @else
                                <div>
                                    <form action="{{ route('library-members.block', $member) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <x-secondary-button class="w-full justify-center">
                                            <i class="fa fa-ban mr-1"></i> {{ __('Block Member') }}
                                        </x-secondary-button>
                                    </form>
                                </div>
                            @endif
                            
                            <div>
                                <form action="{{ route('library-members.renew', $member) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <x-secondary-button class="w-full justify-center">
                                        <i class="fa fa-redo mr-1"></i> {{ __('Renew Membership') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                            
                            <div>
                                <form method="POST" action="{{ route('library-members.destroy', $member) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this member?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button class="w-full justify-center">
                                        <i class="fa fa-trash mr-1"></i> {{ __('Delete Member') }}
                                    </x-danger-button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>