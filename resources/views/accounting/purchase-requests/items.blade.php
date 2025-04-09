<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manage Items for Purchase Request #{{ $purchaseRequest->pr_number }}
             <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($purchaseRequest->status) }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('components.flash-messages')

            {{-- Summary Info --}}
             <div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
                 <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                      <div>
                        <span class="font-medium text-gray-500">PR Number:</span>
                        <span class="ml-1 text-gray-900">{{ $purchaseRequest->pr_number }}</span>
                     </div>
                     <div>
                        <span class="font-medium text-gray-500">Date Requested:</span>
                        <span class="ml-1 text-gray-900">{{ $purchaseRequest->date_requested->format('M d, Y') }}</span>
                     </div>
                      <div>
                        <span class="font-medium text-gray-500">Requested By:</span>
                        <span class="ml-1 text-gray-900">{{ $purchaseRequest->requester->name ?? 'N/A' }}</span>
                     </div>
                     <div>
                        <span class="font-medium text-gray-500">Department:</span>
                        <span class="ml-1 text-gray-900">{{ $purchaseRequest->department ?? 'N/A' }}</span>
                     </div>
                 </div>
             </div>

            {{-- Add Item Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <form method="POST" action="{{ route('accounting.purchase-requests.items.store', $purchaseRequest) }}">
                    @csrf
                    <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Add New Item</h3>
                        {{-- Optional: Include _item_form.blade.php partial here --}}
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                             {{-- Inventory Item Selector --}}
                            <div class="md:col-span-2">
                                <x-input-label for="item_id" :value="__('Select Inventory Item (Optional)')" />
                                <select name="item_id" id="item_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">-- Type Description Below --</option>
                                    {{-- Ensure controller passes $inventoryItems --}}
                                     @foreach($inventoryItems as $item)
                                        <option value="{{ $item->id }}" data-unit="{{ $item->unit_of_measure }}" data-cost="{{ $item->unit_cost ?? 0 }}" data-description="{{ $item->name }}">
                                            {{ $item->name }} ({{ $item->item_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                             {{-- Description --}}
                            <div class="md:col-span-3">
                                <x-input-label for="description" :value="__('Description')" class="required" />
                                <x-text-input id="description" class="block mt-1 w-full text-sm" type="text" name="description" :value="old('description')" required />
                                <x-input-error :messages="$errors->get('description')" class="mt-1" />
                            </div>

                            {{-- Quantity --}}
                            <div>
                                <x-input-label for="quantity" :value="__('Quantity')" class="required" />
                                <x-text-input id="quantity" class="block mt-1 w-full text-sm" type="number" step="0.01" min="0.01" name="quantity" :value="old('quantity')" required />
                                <x-input-error :messages="$errors->get('quantity')" class="mt-1" />
                            </div>

                             {{-- Unit --}}
                            <div>
                                <x-input-label for="unit" :value="__('Unit')" class="required" />
                                <x-text-input id="unit" class="block mt-1 w-full text-sm" type="text" name="unit" :value="old('unit')" required />
                                <x-input-error :messages="$errors->get('unit')" class="mt-1" />
                            </div>

                             {{-- Est Unit Cost --}}
                            <div>
                                <x-input-label for="estimated_unit_cost" :value="__('Est. Unit Cost')" />
                                <x-text-input id="estimated_unit_cost" class="block mt-1 w-full text-sm" type="number" step="0.01" min="0" name="estimated_unit_cost" :value="old('estimated_unit_cost')" placeholder="0.00"/>
                                <x-input-error :messages="$errors->get('estimated_unit_cost')" class="mt-1" />
                            </div>

                            {{-- Budget Line --}}
                            <div class="md:col-span-2">
                                <x-input-label for="budget_line" :value="__('Budget Line (Optional)')" />
                                <x-text-input id="budget_line" class="block mt-1 w-full text-sm" type="text" name="budget_line" :value="old('budget_line')" />
                                <x-input-error :messages="$errors->get('budget_line')" class="mt-1" />
                            </div>

                             {{-- Item Notes --}}
                            <div class="md:col-span-4">
                                <x-input-label for="notes" :value="__('Item Notes (Optional)')" />
                                <x-text-input id="notes" class="block mt-1 w-full text-sm" type="text" name="notes" :value="old('notes')" />
                                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                            </div>

                            <div class="md:col-span-6 flex justify-end">
                                 <x-primary-button type="submit">
                                    {{ __('Add Item') }}
                                </x-primary-button>
                            </div>
                        </div>
                         {{-- End Add Item Form Fields --}}
                    </div>
                 </form>
            </div>

            {{-- Existing Items List --}}
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-medium text-gray-900 mb-4">Current Items in Request</h3>
                      <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Unit Cost</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Total Cost</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                     @php $totalEstimatedCost = 0; @endphp
                                    @forelse($purchaseRequest->items as $item)
                                         @php
                                            $lineTotal = $item->quantity * $item->estimated_unit_cost;
                                            $totalEstimatedCost += $lineTotal;
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-4 whitespace-normal text-sm font-medium text-gray-900">
                                                {{ $item->description }}
                                                @if($item->notes)<p class="text-xs text-gray-500 mt-1">Note: {{ $item->notes }}</p>@endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($item->quantity, 2) }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{ $item->unit }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($item->estimated_unit_cost, 2) }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($lineTotal, 2) }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                                 <form action="{{ route('accounting.purchase-requests.items.destroy', [$purchaseRequest, $item]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this item?');">
                                                     @csrf
                                                     @method('DELETE')
                                                     <button type="submit" class="text-red-600 hover:text-red-900" title="Remove Item">
                                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                     </button>
                                                 </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center italic">No items added yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($purchaseRequest->items->count() > 0)
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <th colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-900 uppercase">Total Estimated Cost</th>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">{{ number_format($totalEstimatedCost, 2) }}</td>
                                        <td></td> {{-- Empty cell for actions column --}}
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                 </div>
                 <div class="flex items-center justify-between p-6 bg-gray-50 border-t border-gray-200">
                    <a href="{{ route('accounting.purchase-requests.show', $purchaseRequest) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                        {{ __('Back to Request Details') }}
                    </a>
                    {{-- Add Submit button here as well if needed/desired --}}
                    @if($purchaseRequest->status == 'draft' && $purchaseRequest->items()->exists())
                         <form action="{{ route('accounting.purchase-requests.submit', $purchaseRequest) }}" method="POST" onsubmit="return confirm('Are you sure you want to submit this request for approval?');">
                             @csrf
                             <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                 Submit for Approval
                             </button>
                         </form>
                    @endif
                 </div>
             </div>

        </div>
    </div>
    {{-- Script to auto-fill fields when inventory item is selected --}}
    <script>
        document.getElementById('item_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const descriptionInput = document.getElementById('description');
            const unitInput = document.getElementById('unit');
            const costInput = document.getElementById('estimated_unit_cost');

            if (this.value) { // If an actual item is selected
                descriptionInput.value = selectedOption.getAttribute('data-description');
                unitInput.value = selectedOption.getAttribute('data-unit');
                // Only fill cost if it's not already filled by user or old input
                if (!costInput.value || costInput.value == '0' || costInput.value == '0.00') {
                   costInput.value = selectedOption.getAttribute('data-cost');
                }
            } else {
                 // Optionally clear fields if '-- Type Description Below --' is selected
                 // descriptionInput.value = '';
                 // unitInput.value = '';
                 // costInput.value = '';
            }
        });
    </script>
</x-app-layout>