<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manage Bids for Tender #{{ $tender->tender_number }} - {{ $tender->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
             @include('components.flash-messages')

            {{-- Tender Summary --}}
            <div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
                 <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                      <div>
                        <span class="font-medium text-gray-500">Tender Number:</span>
                        <span class="ml-1 text-gray-900">{{ $tender->tender_number }}</span>
                     </div>
                      <div>
                        <span class="font-medium text-gray-500">Closing Date:</span>
                        <span class="ml-1 text-gray-900">{{ $tender->closing_date->format('M d, Y') }}</span>
                     </div>
                      <div>
                        <span class="font-medium text-gray-500">Status:</span>
                        <span class="ml-1 text-gray-900">{{ ucfirst($tender->status) }}</span>
                     </div>
                     <div>
                        <span class="font-medium text-gray-500">Bids Received:</span>
                        <span class="ml-1 text-gray-900">{{ $tender->bids->count() }}</span>
                     </div>
                 </div>
             </div>

             {{-- Add New Bid Form --}}
             @if(in_array($tender->status, ['published'])) {{-- Only show form if tender is open --}}
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <form method="POST" action="{{ route('accounting.tenders.bids.store', $tender) }}" enctype="multipart/form-data"> {{-- Added enctype --}}
                    @csrf
                    <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Submit New Bid</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                            {{-- Supplier --}}
                            <div>
                                <x-input-label for="supplier_id" :value="__('Supplier')" class="required"/>
                                <select name="supplier_id" id="supplier_id" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Select Supplier --</option>
                                    {{-- Ensure controller passes $suppliers --}}
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('supplier_id')" class="mt-1" />
                            </div>
                            {{-- Bid Date --}}
                            <div>
                                <x-input-label for="bid_date" :value="__('Bid Date')" class="required"/>
                                <x-text-input id="bid_date" class="block mt-1 w-full" type="date" name="bid_date" :value="old('bid_date', now()->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('bid_date')" class="mt-1" />
                            </div>
                             {{-- Bid Amount --}}
                            <div>
                                <x-input-label for="bid_amount" :value="__('Bid Amount')" class="required"/>
                                <x-text-input id="bid_amount" class="block mt-1 w-full" type="number" step="0.01" min="0" name="bid_amount" :value="old('bid_amount')" required />
                                <x-input-error :messages="$errors->get('bid_amount')" class="mt-1" />
                            </div>
                            {{-- Scores (Optional - maybe entered later during evaluation) --}}
                            <div>
                                <x-input-label for="technical_score" :value="__('Technical Score (Optional)')" />
                                <x-text-input id="technical_score" class="block mt-1 w-full" type="number" step="1" min="0" name="technical_score" :value="old('technical_score')" />
                                <x-input-error :messages="$errors->get('technical_score')" class="mt-1" />
                            </div>
                             <div>
                                <x-input-label for="financial_score" :value="__('Financial Score (Optional)')" />
                                <x-text-input id="financial_score" class="block mt-1 w-full" type="number" step="1" min="0" name="financial_score" :value="old('financial_score')" />
                                <x-input-error :messages="$errors->get('financial_score')" class="mt-1" />
                            </div>
                             {{-- Is Compliant --}}
                             <div class="flex items-center pt-7">
                                <input type="checkbox" id="is_compliant" name="is_compliant" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ old('is_compliant', true) ? 'checked' : '' }}>
                                <label for="is_compliant" class="ml-2 text-sm text-gray-600">{{ __('Bid is Compliant') }}</label>
                                <x-input-error :messages="$errors->get('is_compliant')" class="mt-1" />
                            </div>
                             {{-- Notes --}}
                            <div class="md:col-span-3">
                                <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                            </div>
                            {{-- Bid Document Upload --}}
                            <div class="md:col-span-3">
                                <x-input-label for="bid_document" :value="__('Bid Document (Optional PDF/DOCX/ZIP, Max 20MB)')" />
                                <input type="file" id="bid_document" name="bid_document" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                <x-input-error :messages="$errors->get('bid_document')" class="mt-2" />
                            </div>

                        </div>
                    </div>
                     <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <x-primary-button type="submit">
                            {{ __('Submit Bid') }}
                        </x-primary-button>
                    </div>
                 </form>
             </div>
             @else
              <div class="bg-yellow-50 p-4 rounded-md text-center">
                <p class="text-sm text-yellow-700">Bids can only be submitted for 'Published' tenders before the closing date.</p>
              </div>
             @endif


            {{-- Existing Bids List --}}
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-medium text-gray-900 mb-4">Submitted Bids</h3>
                      <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bid Date</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Bid Amount</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tech Score</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Fin Score</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Score</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Compliant?</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($tender->bids as $bid)
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                 <a href="{{ route('accounting.suppliers.show', $bid->supplier_id) }}" class="text-indigo-600 hover:underline">
                                                    {{ $bid->supplier->name ?? 'Unknown' }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bid->bid_date->format('M d, Y') }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($bid->bid_amount, 2) }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ $bid->technical_score ?? '-' }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ $bid->financial_score ?? '-' }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-700">{{ $bid->total_score ?? '-' }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                                 @if($bid->is_compliant)
                                                    <span class="text-green-600">Yes</span>
                                                @else
                                                    <span class="text-red-600">No</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                                {{-- Edit Bid Link (Could go to a dedicated edit bid page) --}}
                                                {{-- <a href="#" class="text-yellow-600 hover:text-yellow-900 mr-2" title="Edit Bid (Scores/Compliance)">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </a> --}}
                                                {{-- Download Bid Document --}}
                                                @if($bid->document_path)
                                                     <a href="{{ route('accounting.tenders.bid.download', [$tender, $bid]) }}" class="text-blue-600 hover:text-blue-900 mr-2" title="Download Bid Document">
                                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                    </a>
                                                @endif
                                                {{-- Delete Bid Form --}}
                                                @if(in_array($tender->status, ['published', 'closed'])) {{-- Allow delete if published or closed? Adjust as needed --}}
                                                {{-- @can('delete', $bid) --}}
                                                <form action="{{ route('accounting.tenders.bids.destroy', [$tender, $bid]) }}" method="POST" class="inline-block delete-form" onsubmit="return confirm('Are you sure you want to delete this bid from {{ $bid->supplier->name ?? 'Unknown' }}?');">
                                                     @csrf
                                                     @method('DELETE')
                                                     <button type="submit" class="text-red-600 hover:text-red-900" title="Delete Bid">
                                                         <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                     </button>
                                                 </form>
                                                {{-- @endcan --}}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center italic">No bids submitted yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                 </div>
                 <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                    <a href="{{ route('accounting.tenders.show', $tender) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                        {{ __('Back to Tender Details') }}
                    </a>
                    {{-- Maybe add Award button here too? --}}
                 </div>
             </div>

        </div>
    </div>
</x-app-layout>