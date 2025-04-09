<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tender #{{ $tender->tender_number }} - {{ $tender->title }}
                {{-- Status Badge --}}
                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                    @switch($tender->status)
                        @case('draft') bg-gray-100 text-gray-800 @break
                        @case('published') bg-blue-100 text-blue-800 @break
                        @case('closed') bg-yellow-100 text-yellow-800 @break
                        @case('awarded') bg-green-100 text-green-800 @break
                        @case('cancelled') bg-red-100 text-red-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch">
                    {{ ucfirst($tender->status) }}
                </span>
            </h2>
            <div class="flex space-x-2">
                 {{-- Edit Button (if Draft/Published) --}}
                 @if(in_array($tender->status, ['draft', 'published']))
                      {{-- @can('update', $tender) --}}
                     <a href="{{ route('accounting.tenders.edit', $tender) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                         Edit Tender
                     </a>
                      {{-- @endcan --}}
                 @endif
                 {{-- Manage Bids Button --}}
                 {{-- @can('viewAny', App\Models\Accounting\TenderBid::class) --}}
                 <a href="{{ route('accounting.tenders.bids', $tender) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                     Manage Bids ({{ $bidCount }})
                 </a>
                 {{-- @endcan --}}

                 {{-- Award Button (if Closed/Published and not already awarded) --}}
                 @if(in_array($tender->status, ['closed', 'published']) && !$tender->awarded_to)
                    {{-- @can('award', $tender) --}}
                    <button type="button" onclick="showAwardModal()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                         Award Tender
                     </button>
                     {{-- @endcan --}}
                 @endif

                 {{-- Delete Button (if Draft) --}}
                  @if($tender->status == 'draft')
                     {{-- @can('delete', $tender) --}}
                     <form action="{{ route('accounting.tenders.destroy', $tender) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this draft tender?');">
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

              {{-- Award Tender Modal (Hidden by default) --}}
             <div id="awardModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form action="{{ route('accounting.tenders.award', $tender) }}" method="POST">
                            @csrf
                             <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                         <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            Award Tender #{{ $tender->tender_number }}
                                        </h3>
                                        <div class="mt-4 space-y-4">
                                            {{-- Option 1: Select Winning Bid --}}
                                            <div>
                                                <label for="tender_bid_id" class="block text-sm font-medium text-gray-700">Select Winning Bid (Recommended)</label>
                                                <select name="tender_bid_id" id="tender_bid_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="">-- Select Winning Bid --</option>
                                                     @foreach($tender->bids as $bid)
                                                        <option value="{{ $bid->id }}">
                                                            {{ $bid->supplier->name ?? 'Unknown' }} - Amount: {{ number_format($bid->bid_amount, 2) }} (Score: {{ $bid->total_score ?? 'N/A' }})
                                                        </option>
                                                     @endforeach
                                                </select>
                                                <x-input-error :messages="$errors->get('tender_bid_id')" class="mt-1" />
                                                <p class="text-xs text-gray-500 mt-1">Selecting a bid will automatically assign the supplier.</p>
                                            </div>
                                             <div class="text-center text-sm text-gray-500">OR</div>
                                             {{-- Option 2: Select Supplier Directly --}}
                                            <div>
                                                <label for="awarded_to" class="block text-sm font-medium text-gray-700">Award Directly To Supplier</label>
                                                <select name="awarded_to" id="awarded_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="">-- Select Supplier --</option>
                                                     @foreach($tender->bids->unique('supplier_id') as $bid) {{-- Show suppliers who bid --}}
                                                        @if($bid->supplier)
                                                        <option value="{{ $bid->supplier->id }}">
                                                            {{ $bid->supplier->name }}
                                                        </option>
                                                        @endif
                                                     @endforeach
                                                     {{-- Optionally add other suppliers? --}}
                                                </select>
                                                <x-input-error :messages="$errors->get('awarded_to')" class="mt-1" />
                                                 <p class="text-xs text-gray-500 mt-1">Use if awarding without a formal bid selection.</p>
                                            </div>
                                            {{-- Award Date --}}
                                            <div>
                                                <label for="award_date" class="block text-sm font-medium text-gray-700">Award Date <span class="text-red-600">*</span></label>
                                                <input type="date" name="award_date" id="award_date" value="{{ old('award_date', now()->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <x-input-error :messages="$errors->get('award_date')" class="mt-1" />
                                            </div>
                                             {{-- Award Notes --}}
                                             <div>
                                                <label for="award_notes" class="block text-sm font-medium text-gray-700">Award Notes (Optional)</label>
                                                <textarea id="award_notes" name="award_notes" rows="3" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md">{{ old('award_notes') }}</textarea>
                                                 <x-input-error :messages="$errors->get('award_notes')" class="mt-1" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Confirm Award
                                </button>
                                <button type="button" onclick="hideAwardModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
             {{-- End Award Modal --}}


            {{-- Tender Header Info --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Publication Date</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $tender->publication_date->format('F d, Y') }}</p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Closing Date</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $tender->closing_date->format('F d, Y') }}</p>
                        </div>
                         <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Status</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($tender->status) }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Academic Year</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $tender->academicYear?->name ?? ($tender->academicYear?->start_date->format('Y').'/'.($tender->academicYear?->start_date->year + 1)) ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Estimated Value</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $tender->estimated_value ? number_format($tender->estimated_value, 2) : 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Created By</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $tender->creator->name ?? 'N/A' }} on {{ $tender->created_at->format('M d, Y') }}</p>
                        </div>

                         @if($tender->document_path)
                         <div class="md:col-span-3">
                             <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Tender Document</h3>
                             <p class="mt-1 text-sm">
                                 <a href="{{ route('accounting.tenders.download', $tender) }}" class="text-indigo-600 hover:underline inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download Document ({{ basename($tender->document_path) }})
                                </a>
                            </p>
                         </div>
                         @endif

                         <div class="md:col-span-3">
                             <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Description</h3>
                             <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $tender->description ?? 'N/A' }}</p>
                         </div>

                         {{-- Award Details --}}
                         @if($tender->awarded_to)
                            <div class="md:col-span-3 pt-4 border-t mt-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Award Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Awarded Supplier</h4>
                                        <p class="mt-1 text-sm text-gray-900">
                                            @if($tender->awardedSupplier)
                                                <a href="{{ route('accounting.suppliers.show', $tender->awardedSupplier) }}" class="text-green-600 hover:underline font-semibold">
                                                    {{ $tender->awardedSupplier->name }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                     <div>
                                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Award Date</h4>
                                        <p class="mt-1 text-sm text-gray-900">{{ $tender->award_date ? $tender->award_date->format('F d, Y') : 'N/A' }}</p>
                                    </div>
                                     <div class="md:col-span-2">
                                         <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Award Notes</h4>
                                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $tender->award_notes ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                         @endif

                    </div>
                 </div>
            </div>

            {{-- Related Bids (Optional Section - could link to bids page) --}}
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Submitted Bids</h3>
                         <a href="{{ route('accounting.tenders.bids', $tender) }}" class="text-sm text-indigo-600 hover:underline">
                            View/Manage All Bids →
                         </a>
                    </div>
                    @if($tender->bids->count() > 0)
                         <ul class="divide-y divide-gray-200">
                             @foreach($tender->bids->take(5) as $bid) {{-- Show first 5 --}}
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $bid->supplier->name ?? 'Unknown Supplier' }}</p>
                                        <p class="text-sm text-gray-500">Amount: {{ number_format($bid->bid_amount, 2) }} | Score: {{ $bid->total_score ?? 'N/A' }} | Submitted: {{ $bid->bid_date->format('M d, Y') }}</p>
                                    </div>
                                     @if($bid->document_path)
                                        <a href="{{ route('accounting.tenders.bid.download', [$tender, $bid]) }}" class="text-sm text-indigo-600 hover:underline" title="Download Bid Document">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                    @endif
                                </li>
                             @endforeach
                         </ul>
                    @else
                        <p class="text-sm text-gray-500 italic">No bids submitted yet.</p>
                    @endif
                 </div>
             </div>

              {{-- Related Contracts --}}
             @if($tender->contracts->count() > 0)
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                      <h3 class="text-lg font-medium text-gray-900 mb-4">Related Contracts</h3>
                      <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contract #</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                     @foreach($tender->contracts as $contract)
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-indigo-600 hover:underline">
                                                {{-- Adjust route name if different --}}
                                                <a href="{{ route('accounting.contracts.show', $contract) }}">{{ $contract->contract_number }}</a>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $contract->title }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contract->supplier->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($contract->contract_value, 2) }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @switch($contract->status)
                                                        @case('draft') bg-gray-100 text-gray-800 @break
                                                        @case('active') bg-green-100 text-green-800 @break
                                                        @case('expired') bg-yellow-100 text-yellow-800 @break
                                                        @case('terminated') bg-red-100 text-red-800 @break
                                                        @default bg-gray-100 text-gray-800
                                                    @endswitch">
                                                    {{ ucfirst($contract->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                 </div>
             </div>
             @endif

        </div>
    </div>

    <script>
        function showAwardModal() {
            document.getElementById('awardModal').classList.remove('hidden');
        }
        function hideAwardModal() {
            document.getElementById('awardModal').classList.add('hidden');
        }
        // Optional: Clear the other selection when one is made
        document.getElementById('tender_bid_id')?.addEventListener('change', function() {
             if (this.value) {
                 document.getElementById('awarded_to').value = '';
             }
        });
         document.getElementById('awarded_to')?.addEventListener('change', function() {
             if (this.value) {
                 document.getElementById('tender_bid_id').value = '';
             }
        });
    </script>
</x-app-layout>