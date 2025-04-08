<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('CAPEX Project Details') }}: {{ $capexItem->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     {{-- Make sure your Controller passes $capexItem --}}
                    <div class="space-y-4">
                         <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $capexItem->name }}</h3>
                        </div>

                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4 text-sm">
                            <div class="sm:col-span-1">
                                <dt class="font-medium text-gray-500">{{ __('Budgeted Amount') }}</dt>
                                <dd class="mt-1 text-gray-900">{{ number_format($capexItem->budgeted_amount ?? 0, 2) }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="font-medium text-gray-500">{{ __('Status') }}</dt>
                                <dd class="mt-1 text-gray-900">{{ Str::title(str_replace('_', ' ', $capexItem->status ?? 'N/A')) }}</dd>
                            </div>
                             <div class="sm:col-span-1">
                                <dt class="font-medium text-gray-500">{{ __('Start Date') }}</dt>
                                <dd class="mt-1 text-gray-900">{{ $capexItem->start_date ? \Carbon\Carbon::parse($capexItem->start_date)->format('F j, Y') : 'N/A' }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="font-medium text-gray-500">{{ __('Expected Completion Date') }}</dt>
                                <dd class="mt-1 text-gray-900">{{ $capexItem->completion_date ? \Carbon\Carbon::parse($capexItem->completion_date)->format('F j, Y') : 'N/A' }}</dd>
                            </div>
                            {{-- Add more fields as needed --}}
                             {{-- <div class="sm:col-span-1">
                                <dt class="font-medium text-gray-500">{{ __('Actual Spent') }}</dt>
                                <dd class="mt-1 text-gray-900">{{ number_format($capexItem->actual_amount ?? 0, 2) }}</dd>
                            </div> --}}
                             {{-- <div class="sm:col-span-1">
                                <dt class="font-medium text-gray-500">{{ __('Associated Account') }}</dt>
                                <dd class="mt-1 text-gray-900">{{ $capexItem->account->name ?? 'N/A' }}</dd> // Assuming an 'account' relationship exists
                            </div> --}}
                             <div class="sm:col-span-2">
                                <dt class="font-medium text-gray-500">{{ __('Description') }}</dt>
                                <dd class="mt-1 text-gray-900 whitespace-pre-wrap">{{ $capexItem->description ?? 'N/A' }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="font-medium text-gray-500">{{ __('Created At') }}</dt>
                                <dd class="mt-1 text-gray-900">{{ $capexItem->created_at->format('F j, Y H:i:s') }}</dd>
                            </div>
                             <div class="sm:col-span-1">
                                <dt class="font-medium text-gray-500">{{ __('Last Updated At') }}</dt>
                                <dd class="mt-1 text-gray-900">{{ $capexItem->updated_at->format('F j, Y H:i:s') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="flex items-center justify-end mt-6 border-t border-gray-200 pt-4">
                         <a href="{{ route('accounting.capex.index') }}" class="underline text-sm text-gray-600 hover:text-gray-900 mr-4">
                             {{ __('Back to List') }}
                         </a>
                         {{-- Add Edit link if Edit route/view exists --}}
                         {{-- <a href="{{ route('accounting.capex.edit', $capexItem->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                             {{ __('Edit') }}
                         </a> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>