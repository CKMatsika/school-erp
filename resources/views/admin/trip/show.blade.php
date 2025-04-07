<x-admin-layout>
    <x-slot name="title">{{ __('Trip Details') }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trip Details') }}: {{ $trip->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Action buttons -->
                    <div class="flex justify-between mb-6">
                        <div>
                            <a href="{{ route('trip.index') }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Trips') }}
                            </a>
                        </div>
                        
                        <div class="flex space-x-4">
                            <a href="{{ route('trip.edit', $trip) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white py-2 px-4 rounded">
                                <i class="fa fa-edit mr-1"></i> {{ __('Edit') }}
                            </a>
                            
                            <form method="POST" action="{{ route('trip.destroy', $trip) }}" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded" 
                                    onclick="return confirm('{{ __('Are you sure you want to delete this trip?') }}')">
                                    <i class="fa fa-trash mr-1"></i> {{ __('Delete') }}
                                </button>
                            </form>
                            
                            @if($trip->status === 'Planned' || $trip->status === 'Approved')
                                <a href="{{ route('trip-participants.index', $trip) }}" class="bg-green-500 hover:bg-green-700 text-white py-2 px-4 rounded">
                                    <i class="fa fa-users mr-1"></i> {{ __('Manage Participants') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="mb-6">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $trip->getStatusBadgeClass() }}">
                            {{ $trip->status }}
                        </span>
                    </div>

                    <!-- Trip Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Trip Information') }}</h3>
                            
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('Trip Name') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->name }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Destination') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->destination }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Trip Type') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->trip_type }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Grade/Class') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->grade_class }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Start Date') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->start_date->format('M d, Y') }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('End Date') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->end_date->format('M d, Y') }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Primary Contact') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->primaryContact->name }}</dd>
                            </dl>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Trip Details') }}</h3>
                            
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('Departure Location') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->departure_location }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Departure Time') }}</dt>
                                <dd class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Return Location') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->return_location }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Return Time') }}</dt>
                                <dd class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($trip->return_time)->format('h:i A') }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Transport Type') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->transport_type }}</dd>
                                
                                @if($trip->transport_type === 'School Bus' && $trip->vehicle)
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Vehicle') }}</dt>
                                    <dd class="text-sm text-gray-900">{{ $trip->vehicle->name }} ({{ $trip->vehicle->registration_number }})</dd>
                                @endif
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Cost per Student') }}</dt>
                                <dd class="text-sm text-gray-900">{{ number_format($trip->cost_per_student, 2) }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Max Participants') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->max_participants }}</dd>
                                
                                <dt class="text-sm font-medium text-gray-500">{{ __('Current Participants') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $trip->participant_count }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Trip Purpose & Requirements -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Trip Purpose') }}</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $trip->purpose }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Special Requirements') }}</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $trip->special_requirements ?: __('None specified') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Risk Assessment -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Risk Assessment') }}</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-900 whitespace-pre-line">{{ $trip->risk_assessment ?: __('No risk assessment provided') }}</p>
                        </div>
                    </div>
                    
                    <!-- Attachments -->
                    @if($trip->attachments && $trip->attachments->count() > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Attachments') }}</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <ul class="list-disc list-inside">
                                    @foreach($trip->attachments as $attachment)
                                        <li class="mb-1">
                                            <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="text-blue-600 hover:text-blue-900 hover:underline">
                                                {{ $attachment->filename }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Participants Summary -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Participants Summary') }}</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-green-600">{{ $trip->participant_count }}</p>
                                    <p class="text-sm text-gray-500">{{ __('Registered Participants') }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-blue-600">{{ $trip->max_participants - $trip->participant_count }}</p>
                                    <p class="text-sm text-gray-500">{{ __('Available Spots') }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-800">{{ number_format(($trip->participant_count / $trip->max_participants) * 100, 1) }}%</p>
                                    <p class="text-sm text-gray-500">{{ __('Capacity Filled') }}</p>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <a href="{{ route('trip-participants.index', $trip) }}" class="text-blue-600 hover:text-blue-900 hover:underline">
                                    {{ __('View full participants list') }} →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>