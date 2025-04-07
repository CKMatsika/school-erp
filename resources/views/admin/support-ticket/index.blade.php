<x-admin-layout>
    <x-slot name="title">{{ __('Support Tickets') }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Support Tickets') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- Action buttons -->
            <div class="flex justify-between mb-6">
                <div class="flex space-x-4">
                    <x-primary-button onclick="window.location.href='{{ route('support-ticket.create') }}'">
                        <i class="fa fa-plus mr-1"></i> {{ __('New Support Ticket') }}
                    </x-primary-button>
                    
                    <x-secondary-button onclick="window.location.href='{{ route('support-ticket-reports.index') }}'">
                        <i class="fa fa-file-alt mr-1"></i> {{ __('Reports') }}
                    </x-secondary-button>
                </div>
                
                <div>
                    <form method="GET" action="{{ route('support-ticket.index') }}" class="flex items-center">
                        <x-text-input id="search" class="block w-64" type="text" name="search" 
                            :value="request('search')" placeholder="{{ __('Search tickets...') }}" />
                        
                        <x-primary-button class="ml-4">
                            <i class="fa fa-search"></i>
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-4">
                @include('admin.support-ticket.partials.filters')
            </div>

            <!-- Support Tickets Table -->
            <div class="overflow-x-auto relative">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3 px-6">{{ __('Ticket ID') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Subject') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Requester') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Category') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Priority') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Status') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Created') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="py-4 px-6 font-medium text-gray-900">
                                    #{{ $ticket->ticket_id }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $ticket->subject }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $ticket->requester->name }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $ticket->category }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $ticket->getPriorityBadgeClass() }}">
                                        {{ $ticket->priority }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $ticket->getStatusBadgeClass() }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    {{ $ticket->created_at->format('M d, Y') }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('support-ticket.show', $ticket) }}" class="text-blue-600 hover:text-blue-900">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('support-ticket.edit', $ticket) }}" class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        
                                        <form method="POST" action="{{ route('support-ticket.destroy', $ticket) }}" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" 
                                                onclick="return confirm('{{ __('Are you sure you want to delete this ticket?') }}')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="bg-white border-b">
                                <td colspan="8" class="py-4 px-6 text-center text-gray-500">
                                    {{ __('No support tickets found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>