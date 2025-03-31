<x-admin-layout>
    <x-slot name="title">{{ __('Library Members') }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Library Members') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- Action buttons -->
            <div class="flex justify-between mb-6">
                <div class="flex space-x-4">
                    <x-primary-button onclick="window.location.href='{{ route('library-members.create') }}'">
                        <i class="fa fa-plus mr-1"></i> {{ __('New Member') }}
                    </x-primary-button>
                    
                    <x-secondary-button onclick="window.location.href='{{ route('library-members.import') }}'">
                        <i class="fa fa-file-import mr-1"></i> {{ __('Import Members') }}
                    </x-secondary-button>
                    
                    <x-secondary-button onclick="window.location.href='{{ route('library-member-reports.index') }}'">
                        <i class="fa fa-file-alt mr-1"></i> {{ __('Reports') }}
                    </x-secondary-button>
                </div>
                
                <div>
                    <form method="GET" action="{{ route('library-members.index') }}" class="flex items-center">
                        <x-text-input id="search" class="block w-64" type="text" name="search" 
                            :value="request('search')" placeholder="{{ __('Search members...') }}" />
                        
                        <x-primary-button class="ml-4">
                            <i class="fa fa-search"></i>
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-4">
                @include('administration.library-members.partials.filters')
            </div>

            <!-- Library Members Table -->
            <div class="overflow-x-auto relative">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3 px-6">{{ __('Member ID') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Name') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Type') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Email') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Status') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Books Out') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="py-4 px-6">
                                    {{ $member->member_id ?: '-' }}
                                </td>
                                <td class="py-4 px-6 font-medium text-gray-900">
                                    {{ $member->name }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $member->member_type }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $member->email ?: '-' }}
                                </td>
                                <td class="py-4 px-6">
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
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('Blocked') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    {{ $member->current_books_count }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('library-members.show', $member) }}" class="text-blue-600 hover:text-blue-900">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('library-members.edit', $member) }}" class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        
                                        <form method="POST" action="{{ route('library-members.destroy', $member) }}" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" 
                                                onclick="return confirm('{{ __('Are you sure you want to delete this member?') }}')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="bg-white border-b">
                                <td colspan="7" class="py-4 px-6 text-center text-gray-500">
                                    {{ __('No library members found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $members->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>