<x-admin-layout>
    <x-slot name="title">{{ __('Asset Maintenance') }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Asset Maintenance') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- Action buttons -->
            <div class="flex justify-between mb-6">
                <div class="flex space-x-4">
                    <x-primary-button onclick="window.location.href='{{ route('asset-maintenance.create') }}'">
                        <i class="fa fa-plus mr-1"></i> {{ __('New Maintenance Record') }}
                    </x-primary-button>
                    
                    <x-secondary-button onclick="window.location.href='{{ route('asset-maintenance-reports.index') }}'">
                        <i class="fa fa-file-alt mr-1"></i> {{ __('Reports') }}
                    </x-secondary-button>
                </div>
                
                <div>
                    <form method="GET" action="{{ route('asset-maintenance.index') }}" class="flex items-center">
                        <x-text-input id="search" class="block w-64" type="text" name="search" 
                            :value="request('search')" placeholder="{{ __('Search maintenance records...') }}" />
                        
                        <x-primary-button class="ml-4">
                            <i class="fa fa-search"></i>
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-4">
                @include('administration.asset-maintenance.partials.filters')
            </div>

            <!-- Asset Maintenance Table -->
            <div class="overflow-x-auto relative">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3 px-6">{{ __('Asset') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Maintenance Type') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Date') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Cost') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Status') }}</th>
                            <th scope="col" class="py-3 px-6">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($maintenanceRecords as $record)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="py-4 px-6 font-medium text-gray-900">
                                    {{ $record->asset->name }} 
                                    <span class="text-xs text-gray-500">({{ $record->asset->asset_code }})</span>
                                </td>
                                <td class="py-4 px-6">
                                    {{ $record->maintenance_type }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $record->maintenance_date->format('M d, Y') }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ number_format($record->cost, 2) }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $record->getStatusBadgeClass() }}">
                                        {{ $record->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('asset-maintenance.show', $record) }}" class="text-blue-600 hover:text-blue-900">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('asset-maintenance.edit', $record) }}" class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        
                                        <form method="POST" action="{{ route('asset-maintenance.destroy', $record) }}" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" 
                                                onclick="return confirm('{{ __('Are you sure you want to delete this maintenance record?') }}')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="bg-white border-b">
                                <td colspan="6" class="py-4 px-6 text-center text-gray-500">
                                    {{ __('No maintenance records found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $maintenanceRecords->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>