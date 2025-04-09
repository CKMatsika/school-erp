<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Contract #{{ $contract->contract_number }} - {{ $contract->title }}
                {{-- Status Badge --}}
                 <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                    @switch($contract->status)
                        @case('draft') bg-gray-100 text-gray-800 @break
                        @case('active') bg-green-100 text-green-800 @break
                        @case('expired') bg-yellow-100 text-yellow-800 @break
                        @case('terminated') bg-red-100 text-red-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch">
                    {{ ucfirst($contract->status) }}
                </span>
            </h2>
            <div class="flex space-x-2">
                 {{-- Edit Button (if Draft/Active?) --}}
                 @if(in_array($contract->status, ['draft', 'active'])) {{-- Adjust editable statuses --}}
                      {{-- @can('update', $contract) --}}
                     <a href="{{ route('accounting.procurement-contracts.edit', $contract) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                         Edit Contract
                     </a>
                      {{-- @endcan --}}
                 @endif
                 {{-- Delete Button (if Draft) --}}
                  @if($contract->status == 'draft')
                     {{-- @can('delete', $contract) --}}
                     <form action="{{ route('accounting.procurement-contracts.destroy', $contract) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this draft contract?');">
                         @csrf
                         @method('DELETE')
                         <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                             Delete Draft
                         </button>
                     </form>
                     {{-- @endcan --}}
                 @endif
            </div>
         </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
             @include('components.flash-messages')

            {{-- Contract Header Info --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Supplier</h3>
                            <p class="mt-1 text-sm text-gray-900 font-medium">
                                <a href="{{ route('accounting.suppliers.show', $contract->supplier_id) }}" class="text-indigo-600 hover:underline">
                                    {{ $contract->supplier->name ?? 'N/A' }}
                                </a>
                            </p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Contract Value</h3>
                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ number_format($contract->contract_value, 2) }}</p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Status</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($contract->status) }}</p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Start Date</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $contract->start_date->format('F d, Y') }}</p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">End Date</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $contract->end_date->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Academic Year</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $contract->academicYear?->name ?? ($contract->academicYear?->start_date->format('Y').'/'.($contract->academicYear?->start_date->year + 1)) ?? 'N/A' }}</p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Related Tender</h3>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($contract->tender)
                                 <a href="{{ route('accounting.tenders.show', $contract->tender_id) }}" class="text-indigo-600 hover:underline">
                                     #{{ $contract->tender->tender_number }}
                                 </a>
                                @else
                                N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Created By</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $contract->creator->name ?? 'N/A' }} on {{ $contract->created_at->format('M d, Y') }}</p>
                        </div>
                        @if($contract->document_path)
                         <div class="md:col-span-3">
                             <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Contract Document</h3>
                             <p class="mt-1 text-sm">
                                 <a href="{{ route('accounting.procurement-contracts.download', $contract) }}" class="text-indigo-600 hover:underline inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download Document ({{ basename($contract->document_path) }})
                                </a>
                            </p>
                         </div>
                         @endif

                         <div class="md:col-span-3">
                             <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Scope / Description</h3>
                             <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $contract->description ?? 'N/A' }}</p>
                         </div>
                         <div class="md:col-span-3">
                             <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Terms & Conditions</h3>
                             <div class="mt-1 text-sm text-gray-900 prose max-w-none"> {{-- Using prose for basic formatting --}}
                                {!! nl2br(e($contract->terms_and_conditions ?? 'N/A')) !!}
                             </div>
                         </div>
                    </div>
                 </div>
                  <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                    <a href="{{ route('accounting.procurement-contracts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                        {{ __('Back to Contracts List') }}
                    </a>
                 </div>
            </div>

            {{-- Add sections for related POs, Payments, etc. if needed later --}}

        </div>
    </div>
</x-app-layout>