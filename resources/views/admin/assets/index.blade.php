<x-admin-layout>
    <x-slot name="title">{{ __('Asset Allocations') }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Asset Allocations') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- Action buttons -->
            <div class="flex justify-between mb-6">
                <div class="flex space-x-4">
                    <x-primary-button onclick="window.location.href='{{ route('asset-allocations.create') }}'">
                        <i class="fa fa-plus mr-1"></i> {{ __('New Allocation') }}
                    </x-primary-button>
                    
                    <x-secondary-button onclick="window.location.href='{{ route('asset-allocation-reports.index') }}'">
                        <i class="fa fa-file-alt mr-1"></i> {{ __('Reports') }}
                    </x-secondary-button>
                </div>
                
                <div>
                    <form method="GET" action="{{ route('asset-allocations.index') }}" class="flex items-center">
                        <x-text-input id="search" class="block w-64" type="text" name="search" 
                            :value="request('search')" placeholder="{{ __('Search allocations...') }}" />
                        
                        <x-primary-button class="ml-4">
                            <i class="fa fa-search"></i>
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-4">
                @include('administration.asset-allocations.partials.filters')
            </div>

            <!-- Asset Allocations Table -->
            <div class="overflow-x-auto relative">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3 px-6">{{ __('Asset') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Allocated To') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Allocated On') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Return Due') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Status') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assetAllocations as $allocation)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="py-4 px-6 font-medium text-gray-900">
                                    {{ $allocation->asset->name }} 
                                    <span class="text-xs text-gray-500">({{ $allocation->asset->asset_code }})</span>
                                </td>
                                <td class="py-4 px-6">
                                    {{ $allocation->allocatable->name ?? $allocation->allocatable_type . ' #' . $allocation->allocatable_id }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $allocation->allocated_at->format('M d, Y') }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $allocation->due_date ? $allocation->due_date->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $allocation->getStatusBadgeClass() }}">
                                        {{ $allocation->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('asset-allocations.show', $allocation) }}" class="text-blue-600 hover:text-blue-900">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('asset-allocations.edit', $allocation) }}" class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        
                                        <form method="POST" action="{{ route('asset-allocations.destroy', $allocation) }}" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" 
                                                onclick="return confirm('{{ __('Are you sure you want to delete this allocation?') }}')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="bg-white border-b">
                                <td colspan="6" class="py-4 px-6 text-center text-gray-500">
                                    {{ __('No asset allocations found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $assetAllocations->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>