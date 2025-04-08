<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payment Method Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('accounting.payment-methods.edit', $paymentMethod) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:border-yellow-800 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('accounting.payment-methods.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('components.flash-messages')

                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Payment Method Information') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Method Name') }}</h4>
                                    <p class="text-md font-semibold">{{ $paymentMethod->name }}</p>
                                </div>

                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Method Code') }}</h4>
                                    <p class="text-md font-semibold">{{ $paymentMethod->code }}</p>
                                </div>

                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Status') }}</h4>
                                    @if($paymentMethod->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Require Reference') }}</h4>
                                    <p class="text-md">
                                        @if($paymentMethod->requires_reference)
                                            <span class="text-green-600">{{ __('Yes') }}</span>
                                        @else
                                            <span class="text-red-600">{{ __('No') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Associated Account') }}</h4>
                                    <p class="text-md">
                                        @if($paymentMethod->account)
                                            {{ $paymentMethod->account->name }} ({{ $paymentMethod->account->code }})
                                        @else
                                            <span class="text-gray-400">{{ __('Not specified') }}</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Display Order') }}</h4>
                                    <p class="text-md">{{ $paymentMethod->display_order }}</p>
                                </div>

                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Created At') }}</h4>
                                    <p class="text-md">{{ $paymentMethod->created_at->format('M d, Y H:i') }}</p>
                                </div>

                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Last Updated') }}</h4>
                                    <p class="text-md">{{ $paymentMethod->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($paymentMethod->description || $paymentMethod->instructions)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Additional Information') }}</h3>
                            
                            @if($paymentMethod->description)
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Description') }}</h4>
                                    <p class="text-md mt-1">{{ $paymentMethod->description }}</p>
                                </div>
                            @endif
                            
                            @if($paymentMethod->instructions)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">{{ __('Payment Instructions') }}</h4>
                                    <div class="bg-gray-50 p-4 rounded-md mt-1">
                                        <p class="text-md whitespace-pre-line">{{ $paymentMethod->instructions }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 mt-6">
                        <form action="{{ route('accounting.payment-methods.destroy', $paymentMethod) }}" method="POST" class="inline-block delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-800 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150" onclick="return confirm('{{ __('Are you sure you want to delete this payment method?') }}')">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('Delete Payment Method') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>