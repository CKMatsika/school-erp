<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('CAPEX Projects / Budgets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Manage CAPEX Projects') }}</h3>
                        <a href="{{ route('accounting.capex.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            {{ __('Add New CAPEX Project') }}
                        </a>
                    </div>

                    @include('components.flash-messages') {{-- Assuming you have this component --}}

                    {{-- UPDATED LINE: Use PHP count() function instead of Collection method --}}
                    @if(count($capexItems) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name / Title') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Budgeted Amount') }}</th>
                                        {{-- Add more relevant columns as needed --}}
                                        {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actual Spent') }}</th> --}}
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Start Date') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {{-- Make sure your Controller passes $capexItems --}}
                                    @foreach($capexItems as $item)
                                        <tr>
                                            {{-- Assuming $item is an object even if $capexItems is an array --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->budgeted_amount ?? 0, 2) }}</td>
                                            {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->actual_amount ?? 0, 2) }}</td> --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{-- You might want status badges here --}}
                                                {{ Str::title(str_replace('_', ' ', $item->status ?? 'N/A')) }}
                                            </td>
                                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ isset($item->start_date) ? \Carbon\Carbon::parse($item->start_date)->format('Y-m-d') : 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    {{-- Ensure your model uses $item->id or appropriate key --}}
                                                    <a href="{{ route('accounting.capex.show', $item->id ?? '#') }}"
                                                       class="text-indigo-600 hover:text-indigo-900" title="{{ __('View Details') }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    </a>

                                                    {{-- Add Edit Route if defined --}}
                                                    {{-- <a href="{{ route('accounting.capex.edit', $item->id ?? '#') }}"
                                                       class="text-yellow-600 hover:text-yellow-900" title="{{ __('Edit') }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </a> --}}

                                                    {{-- Add Delete Route if defined --}}
                                                    {{-- <form action="{{ route('accounting.capex.destroy', $item->id ?? 0) }}"
                                                          method="POST" class="inline-block delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                                title="{{ __('Delete') }}" onclick="return confirm('{{ __('Are you sure you want to delete this CAPEX project?') }}')">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form> --}}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                         {{-- Add Pagination if needed - This will NOT work if $capexItems is an array --}}
                         {{-- <div class="mt-4">
                             {{ $capexItems->links() }}
                         </div> --}}
                    @else
                         <div class="bg-blue-50 p-4 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                     <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                         <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                     </svg>
                                </div>
                                <div class="ml-3 flex-1 md:flex md:justify-between">
                                    <p class="text-sm text-blue-700">
                                        {{ __('No CAPEX projects found.') }}
                                    </p>
                                    <p class="mt-3 text-sm md:mt-0 md:ml-6">
                                        <a href="{{ route('accounting.capex.create') }}" class="whitespace-nowrap font-medium text-blue-700 hover:text-blue-600">
                                            {{ __('Create your first CAPEX project') }} <span aria-hidden="true">→</span>
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>