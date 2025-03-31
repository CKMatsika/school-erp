<x-admin-layout>
    <x-slot name="title">{{ __('Maintenance Record Details') }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Maintenance Record Details') }}
            </h2>
            
            <div class="flex space-x-3">
                @if(in_array($maintenanceRecord->status, ['Scheduled', 'In Progress']))
                    <x-primary-button onclick="document.getElementById('complete-form').submit();">
                        <i class="fa fa-check mr-1"></i> {{ __('Mark as Complete') }}
                    </x-primary-button>
                    
                    <form id="complete-form" action="{{ route('asset-maintenance.complete', $maintenanceRecord) }}" method="POST" class="hidden">
                        @csrf
                        @method('PATCH')
                    </form>
                @endif
                
                <x-secondary-button onclick="window.location.href='{{ route('asset-maintenance.edit', $maintenanceRecord) }}'">
                    <i class="fa fa-edit mr-1"></i> {{ __('Edit') }}
                </x-secondary-button>
                
                <x-secondary-button onclick="window.location.href='{{ route('asset-maintenance.index') }}'">
                    <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Maintenance Records') }}
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
            <!-- Maintenance Record Details -->
            <div class="md:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Maintenance Information') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Maintenance Type') }}</p>
                                <p class="text-base">{{ $maintenanceRecord->maintenance_type }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Status') }}</p>
                                <p class="text-base">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $maintenanceRecord->getStatusBadgeClass() }}">
                                        {{ $maintenanceRecord->status }}
                                    </span>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Maintenance Date') }}</p>
                                <p class="text-base">{{ $maintenanceRecord->maintenance_date->format('M d, Y') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Next Service Date') }}</p>
                                <p class="text-base">
                                    {{ $maintenanceRecord->next_service_date ? $maintenanceRecord->next_service_date->format('M d, Y') : __('Not scheduled') }}
                                    
                                    @if($maintenanceRecord->next_service_date && now()->gt($maintenanceRecord->next_service_date))
                                        <span class="text-red-600 text-sm font-medium">
                                            ({{ __('Overdue') }})
                                        </span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Service Provider') }}</p>
                                <p class="text-base">{{ $maintenanceRecord->provider ?: __('Not specified') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Cost') }}</p>
                                <p class="text-base">{{ number_format($maintenanceRecord->cost, 2) }}</p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500">{{ __('Description') }}</p>
                                <p class="text-base">{{ $maintenanceRecord->description ?: __('No description provided') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Asset Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Asset Information') }}</h3>
                            
                            <x-secondary-button onclick="window.location.href='{{ route('assets.show', $maintenanceRecord->asset) }}'">
                                <i class="fa fa-eye mr-1"></i> {{ __('Asset Details') }}
                            </x-secondary-button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Asset Name') }}</p>
                                <p class="text-base">{{ $maintenanceRecord->asset->name }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Asset Code') }}</p>
                                <p class="text-base">{{ $maintenanceRecord->asset->asset_code }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Category') }}</p>
                                <p class="text-base">{{ $maintenanceRecord->asset->category->name ?? '-' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Current Status') }}</p>
                                <p class="text-base">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $maintenanceRecord->asset->getStatusBadgeClass() }}">
                                        {{ $maintenanceRecord->asset->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Maintenance History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Maintenance History for this Asset') }}</h3>
                        
                        <div class="overflow-x-auto relative">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">{{ __('Type') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Date') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Cost') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Status') }}</th>
                                        <th scope="col" class="py-3 px-6">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($maintenanceHistory as $record)
                                        <tr class="{{ $record->id === $maintenanceRecord->id ? 'bg-blue-50' : 'bg-white' }} border-b hover:bg-gray-50">
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
                                                <a href="{{ route('asset-maintenance.show', $record) }}" class="text-blue-600 hover:text-blue-900">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="bg-white border-b">
                                            <td colspan="5" class="py-4 px-6 text-center text-gray-500">
                                                {{ __('No maintenance history found') }}
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
                <!-- Asset Image -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Asset Image') }}</h3>
                        
                        @if($maintenanceRecord->asset->image)
                            <img src="{{ asset('storage/' . $maintenanceRecord->asset->image) }}" alt="{{ $maintenanceRecord->asset->name }}" class="w-full h-auto rounded-lg shadow-md">
                        @else
                            <div class="flex items-center justify-center h-48 bg-gray-100 rounded-lg">
                                <p class="text-gray-500">{{ __('No image available') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Maintenance Documents -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Maintenance Documents') }}</h3>
                        
                        @if($maintenanceRecord->documents->count() > 0)
                            <ul class="space-y-2">
                                @foreach($maintenanceRecord->documents as $document)
                                    <li class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                                        <div class="flex items-center">
                                            <i class="fa fa-file-alt text-gray-500 mr-3"></i>
                                            <span class="text-sm font-medium text-gray-900">{{ $document->filename }}</span>
                                        </div>
                                        <div>
                                            <a href="{{ asset('storage/' . $document->path) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500 text-center py-4">{{ __('No documents available') }}</p>
                        @endif
                        
                        <!-- Upload new document -->
                        <div class="mt-4">
                            <form method="POST" action="{{ route('asset-maintenance.documents.upload', $maintenanceRecord) }}" enctype="multipart/form-data" class="flex flex-col space-y-2">
                                @csrf
                                <input type="file" name="document" required class="text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-indigo-50 file:text-indigo-700
                                    hover:file:bg-indigo-100">
                                <x-primary-button>
                                    <i class="fa fa-upload mr-1"></i> {{ __('Upload Document') }}
                                </x-primary-button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Quick Actions') }}</h3>
                        
                        <div class="space-y-3">
                            @if(in_array($maintenanceRecord->status, ['Scheduled', 'In Progress']))
                                <div>
                                    <form action="{{ route('asset-maintenance.complete', $maintenanceRecord) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <x-primary-button class="w-full justify-center">
                                            <i class="fa fa-check mr-1"></i> {{ __('Mark as Complete') }}
                                        </x-primary-button>
                                    </form>
                                </div>
                            @endif
                            
                            <div>
                                <form action="{{ route('asset-maintenance.print', $maintenanceRecord) }}" method="GET" target="_blank">
                                    <x-secondary-button class="w-full justify-center">
                                        <i class="fa fa-print mr-1"></i> {{ __('Print Record') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                            
                            @if(!in_array($maintenanceRecord->status, ['Completed', 'Cancelled']))
                                <div>
                                    <form action="{{ route('asset-maintenance.cancel', $maintenanceRecord) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to cancel this maintenance record?') }}')">
                                        @csrf
                                        @method('PATCH')
                                        <x-danger-button class="w-full justify-center">
                                            <i class="fa fa-times mr-1"></i> {{ __('Cancel Maintenance') }}
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