<x-admin-layout>
    <x-slot name="title">{{ __('Asset Allocation Details') }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Asset Allocation Details') }}
            </h2>
            
            <div class="flex space-x-3">
                @if(in_array($assetAllocation->status, ['Pending', 'Active']))
                    <x-primary-button onclick="document.getElementById('return-form').submit();">
                        <i class="fa fa-undo mr-1"></i> {{ __('Return Asset') }}
                    </x-primary-button>
                    
                    <form id="return-form" action="{{ route('asset-allocations.return', $assetAllocation) }}" method="POST" class="hidden">
                        @csrf
                        @method('PATCH')
                    </form>
                @endif
                
                <x-secondary-button onclick="window.location.href='{{ route('asset-allocations.edit', $assetAllocation) }}'">
                    <i class="fa fa-edit mr-1"></i> {{ __('Edit') }}
                </x-secondary-button>
                
                <x-secondary-button onclick="window.location.href='{{ route('asset-allocations.index') }}'">
                    <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Allocations') }}
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
            <!-- Asset Allocation Details -->
            <div class="md:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Allocation Information') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Allocation Code') }}</p>
                                <p class="text-base">{{ $assetAllocation->allocation_code }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Status') }}</p>
                                <p class="text-base">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $assetAllocation->getStatusBadgeClass() }}">
                                        {{ $assetAllocation->status }}
                                    </span>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Allocated To') }}</p>
                                <p class="text-base">
                                    {{ $assetAllocation->allocatable->name ?? 'Unknown' }}
                                    <span class="text-sm text-gray-500">
                                        ({{ Str::afterLast($assetAllocation->allocatable_type, '\\') }})
                                    </span>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Allocation Date') }}</p>
                                <p class="text-base">{{ $assetAllocation->allocated_at->format('M d, Y') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Return Due Date') }}</p>
                                <p class="text-base">
                                    {{ $assetAllocation->due_date ? $assetAllocation->due_date->format('M d, Y') : __('No due date') }}
                                    
                                    @if($assetAllocation->due_date && $assetAllocation->status === 'Active' && now()->gt($assetAllocation->due_date))
                                        <span class="text-red-600 text-sm font-medium">
                                            ({{ __('Overdue') }})
                                        </span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Return Date') }}</p>
                                <p class="text-base">
                                    {{ $assetAllocation->returned_at ? $assetAllocation->returned_at->format('M d, Y') : __('Not returned') }}
                                </p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500">{{ __('Notes') }}</p>
                                <p class="text-base">{{ $assetAllocation->notes ?: __('No notes provided') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Asset Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Asset Information') }}</h3>
                            
                            <x-secondary-button onclick="window.location.href='{{ route('assets.show', $assetAllocation->asset) }}'">
                                <i class="fa fa-eye mr-1"></i> {{ __('Asset Details') }}
                            </x-secondary-button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Asset Name') }}</p>
                                <p class="text-base">{{ $assetAllocation->asset->name }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Asset Code') }}</p>
                                <p class="text-base">{{ $assetAllocation->asset->asset_code }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Category') }}</p>
                                <p class="text-base">{{ $assetAllocation->asset->category->name ?? '-' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Current Status') }}</p>
                                <p class="text-base">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $assetAllocation->asset->getStatusBadgeClass() }}">
                                        {{ $assetAllocation->asset->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Allocation History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('History') }}</h3>
                        
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                                    <i class="fas fa-plus text-white"></i>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">{{ __('Created allocation') }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time datetime="{{ $assetAllocation->created_at->format('Y-m-d') }}">{{ $assetAllocation->created_at->format('M d, Y') }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center">
                                                    <i class="fas fa-exchange-alt text-white"></i>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">{{ __('Allocated to') }} {{ $assetAllocation->allocatable->name ?? 'Unknown' }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time datetime="{{ $assetAllocation->allocated_at->format('Y-m-d') }}">{{ $assetAllocation->allocated_at->format('M d, Y') }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                
                                @if($assetAllocation->returned_at)
                                <li>
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center">
                                                    <i class="fas fa-undo text-white"></i>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">{{ __('Asset returned') }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time datetime="{{ $assetAllocation->returned_at->format('Y-m-d') }}">{{ $assetAllocation->returned_at->format('M d, Y') }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @else
                                <li>
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center">
                                                    <i class="fas fa-clock text-white"></i>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">{{ __('Pending return') }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    {{ __('Not returned yet') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif
                            </ul>
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
                        
                        @if($assetAllocation->asset->image)
                            <img src="{{ asset('storage/' . $assetAllocation->asset->image) }}" alt="{{ $assetAllocation->asset->name }}" class="w-full h-auto rounded-lg shadow-md">
                        @else
                            <div class="flex items-center justify-center h-48 bg-gray-100 rounded-lg">
                                <p class="text-gray-500">{{ __('No image available') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Allocatee Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Recipient Details') }}</h3>
                        
                        @if($assetAllocation->allocatable)
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('Name') }}</p>
                                    <p class="text-base">{{ $assetAllocation->allocatable->name }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('Type') }}</p>
                                    <p class="text-base">{{ Str::afterLast($assetAllocation->allocatable_type, '\\') }}</p>
                                </div>
                                
                                <!-- Conditionally show different fields based on allocatable type -->
                                @if(Str::contains($assetAllocation->allocatable_type, 'Staff'))
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">{{ __('Position') }}</p>
                                        <p class="text-base">{{ $assetAllocation->allocatable->position ?? '-' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">{{ __('Department') }}</p>
                                        <p class="text-base">{{ $assetAllocation->allocatable->department->name ?? '-' }}</p>
                                    </div>
                                @elseif(Str::contains($assetAllocation->allocatable_type, 'Student'))
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">{{ __('Class') }}</p>
                                        <p class="text-base">{{ $assetAllocation->allocatable->class->name ?? '-' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">{{ __('Roll Number') }}</p>
                                        <p class="text-base">{{ $assetAllocation->allocatable->roll_number ?? '-' }}</p>
                                    </div>
                                @elseif(Str::contains($assetAllocation->allocatable_type, 'Department'))
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">{{ __('Head of Department') }}</p>
                                        <p class="text-base">{{ $assetAllocation->allocatable->head->name ?? '-' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">{{ __('Location') }}</p>
                                        <p class="text-base">{{ $assetAllocation->allocatable->location ?? '-' }}</p>
                                    </div>
                                @endif
                                
                                <div>
                                    <x-secondary-button onclick="window.location.href='{{ route(Str::plural(Str::kebab(class_basename($assetAllocation->allocatable_type))) . '.show', $assetAllocation->allocatable_id) }}'">
                                        <i class="fa fa-user mr-1"></i> {{ __('View Full Profile') }}
                                    </x-secondary-button>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">{{ __('Recipient information not available') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Quick Actions') }}</h3>
                        
                        <div class="space-y-3">
                            @if(in_array($assetAllocation->status, ['Pending', 'Active']))
                                <div>
                                    <form action="{{ route('asset-allocations.return', $assetAllocation) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <x-primary-button class="w-full justify-center">
                                            <i class="fa fa-undo mr-1"></i> {{ __('Mark as Returned') }}
                                        </x-primary-button>
                                    </form>
                                </div>
                            @endif
                            
                            <div>
                                <form action="{{ route('asset-allocations.print', $assetAllocation) }}" method="GET" target="_blank">
                                    <x-secondary-button class="w-full justify-center">
                                        <i class="fa fa-print mr-1"></i> {{ __('Print Form') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                            
                            @if($assetAllocation->status !== 'Cancelled')
                                <div>
                                    <form action="{{ route('asset-allocations.cancel', $assetAllocation) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to cancel this allocation?') }}')">
                                        @csrf
                                        @method('PATCH')
                                        <x-danger-button class="w-full justify-center">
                                            <i class="fa fa-times mr-1"></i> {{ __('Cancel Allocation') }}
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