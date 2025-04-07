<x-admin-layout>
    <x-slot name="title">{{ __('Vehicle Details') }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vehicle Details') }}: {{ $vehicle->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Action buttons -->
                    <div class="flex justify-between mb-6">
                        <div>
                            <a href="{{ route('vehicle.index') }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Vehicles') }}
                            </a>
                        </div>
                        
                        <div class="flex space-x-4">
                            <a href="{{ route('vehicle.edit', $vehicle) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white py-2 px-4 rounded">
                                <i class="fa fa-edit mr-1"></i> {{ __('Edit') }}
                            </a>
                            
                            <form method="POST" action="{{ route('vehicle.destroy', $vehicle) }}" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded" 
                                    onclick="return confirm('{{ __('Are you sure you want to delete this vehicle?') }}')">
                                    <i class="fa fa-trash mr-1"></i> {{ __('Delete') }}
                                </button>
                            </form>
                            
                            <a href="{{ route('vehicle-maintenance.index', ['vehicle_id' => $vehicle->id]) }}" class="bg-green-500 hover:bg-green-700 text-white py-2 px-4 rounded">
                                <i class="fa fa-wrench mr-1"></i> {{ __('Maintenance Records') }}
                            </a>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="mb-6">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $vehicle->getStatusBadgeClass() }}">
                            {{ $vehicle->status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Vehicle Information -->
                        <div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Vehicle Information') }}</h3>
                                
                                <dl class="grid grid-cols-1 gap-y-2">
                                    <div class="grid grid-cols-2">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $vehicle->name }}</dd>
                                    </div>
                                    
                                    <div class="grid grid-cols-2">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Registration Number') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $vehicle->registration_number }}</dd>
                                    </div>
                                    
                                    <div class="grid grid-cols-2">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Type') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $vehicle->type }}</dd>
                                    </div>
                                    
                                    <div class="grid grid-cols-2">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Make & Model') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})</dd>
                                    </div>
                                    
                                    <div class="grid grid-cols-2">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Color') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $vehicle->color }}</dd>
                                    </div>
                                    
                                    <div class="grid grid-cols-2">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Fuel Type') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $vehicle->fuel_type }}</dd>
                                    </div>
                                    
                                    <div class="grid grid-cols-2">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Seating Capacity') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $vehicle->capacity }} seats</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            @if($vehicle->image)
                                <div class="mt-4">
                                    <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->name }}" class="w-full h-auto rounded-lg">
                                </div>
                            @endif
                        </div>
                        
                        <div>
                            <!-- Driver Information -->
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Driver Information') }}</h3>
                                
                                @if($vehicle->driver)
                                    <dl class="grid grid-cols-1 gap-y-2">
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $vehicle->driver->name }}</dd>
                                        </div>
                                        
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Contact Number') }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $vehicle->driver->phone }}</dd>
                                        </div>
                                        
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('License Number') }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $vehicle->driver->license_number }}</dd>
                                        </div>
                                        
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('License Expiry') }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $vehicle->driver->license_expiry->format('M d, Y') }}</dd>
                                        </div>
                                        
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Experience') }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $vehicle->driver->experience }} years</dd>
                                        </div>
                                    </dl>
                                    
                                    <div class="mt-2">
                                        <a href="{{ route('driver.show', $vehicle->driver) }}" class="text-blue-600 hover:text-blue-900 hover:underline text-sm">
                                            {{ __('View full driver profile') }} →
                                        </a>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">{{ __('No driver currently assigned to this vehicle.') }}</p>
                                    
                                    <div class="mt-2">
                                        <a href="{{ route('vehicle.edit', $vehicle) }}" class="text-blue-600 hover:text-blue-900 hover:underline text-sm">
                                            {{ __('Assign a driver') }} →
                                        </a>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Service Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Service & Maintenance') }}</h3>
                                
                                <dl class="grid grid-cols-1 gap-y-2">
                                    <div class="grid grid-cols-2">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Insurance Expiry') }}</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($vehicle->insurance_expiry)
                                                {{ $vehicle->insurance_expiry->format('M d, Y') }}
                                                @if($vehicle->insurance_expiry->isPast())
                                                    <span class="text-red-600 font-medium ml-1">{{ __('Expired') }}</span>
                                                @elseif($vehicle->insurance_expiry->diffInDays(now()) < 30)
                                                    <span class="text-yellow-600 font-medium ml-1">{{ __('Expiring soon') }}</span>
                                                @endif
                                            @else
                                                {{ __('Not specified') }}
                                            @endif
                                        </dd>
                                    </div>
                                    
                                    <div class="grid grid-cols-2">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Last Service Date') }}</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $vehicle->last_service_date ? $vehicle->last_service_date->format('M d, Y') : __('Not specified') }}
                                        </dd>
                                    </div>
                                    
                                    <div class="grid grid-cols-2">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Next Service Due') }}</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($vehicle->next_service_date)
                                                {{ $vehicle->next_service_date->format('M d, Y') }}
                                                @if($vehicle->next_service_date->isPast())
                                                    <span class="text-red-600 font-medium ml-1">{{ __('Overdue') }}</span>
                                                @elseif($vehicle->next_service_date->diffInDays(now()) < 14)
                                                    <span class="text-yellow-600 font-medium ml-1">{{ __('Due soon') }}</span>
                                                @endif
                                            @else
                                                {{ __('Not scheduled') }}
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                                
                                <div class="mt-4">
                                    <a href="{{ route('vehicle-maintenance.index', ['vehicle_id' => $vehicle->id]) }}" class="text-blue-600 hover:text-blue-900 hover:underline text-sm">
                                        {{ __('View maintenance history') }} →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    @if($vehicle->notes)
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Notes') }}</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $vehicle->notes }}</p>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Recent Trips -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Recent Trips') }}</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            @if($vehicle->trips && $vehicle->trips->count() > 0)
                                <div class="overflow-x-auto relative">
                                    <table class="w-full text-sm text-left text-gray-500">
                                        <thead class="text-xs text-gray-700 uppercase bg-white">
                                            <tr>
                                                <th scope="col" class="py-3 px-6">{{ __('Trip Name') }}</th>
                                                <th scope="col" class="py-3 px-6">{{ __('Date') }}</th>
                                                <th scope="col" class="py-3 px-6">{{ __('Destination') }}</th>
                                                <th scope="col" class="py-3 px-6">{{ __('Status') }}</th>
                                                <th scope="col" class="py-3 px-6">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($vehicle->trips->take(5) as $trip)
                                                <tr class="bg-white border-b hover:bg-gray-50">
                                                    <td class="py-3 px-6 font-medium text-gray-900">
                                                        {{ $trip->name }}
                                                    </td>
                                                    <td class="py-3 px-6">
                                                        {{ $trip->start_date->format('M d, Y') }}
                                                    </td>
                                                    <td class="py-3 px-6">
                                                        {{ $trip->destination }}
                                                    </td>
                                                    <td class="py-3 px-6">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $trip->getStatusBadgeClass() }}">
                                                            {{ $trip->status }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-6">
                                                        <a href="{{ route('trip.show', $trip) }}" class="text-blue-600 hover:text-blue-900">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if($vehicle->trips->count() > 5)
                                    <div class="mt-4 text-center">
                                        <a href="{{ route('trip.index', ['vehicle_id' => $vehicle->id]) }}" class="text-blue-600 hover:text-blue-900 hover:underline text-sm">
                                            {{ __('View all trips with this vehicle') }} →
                                        </a>
                                    </div>
                                @endif
                            @else
                                <p class="text-sm text-gray-500">{{ __('No trips have been recorded with this vehicle.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>