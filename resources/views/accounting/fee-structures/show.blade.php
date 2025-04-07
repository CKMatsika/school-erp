<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Fee Structure Details') }}: {{ $feeStructure->name }}
            </h2>
            <div class="flex space-x-2">
                 <a href="{{ route('accounting.fee-structures.edit', $feeStructure) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    Edit Structure
                </a>
                 <a href="{{ route('accounting.fee-structures.index') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

             {{-- Structure Details Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800 mb-4">Structure Information</h3>
                     <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                         {{-- Display Structure Name, Academic Year, Description, Status --}}
                         <div class="sm:col-span-1"><dt class="text-sm font-medium text-gray-500">Name</dt><dd class="mt-1 text-sm text-gray-900">{{ $feeStructure->name }}</dd></div>
                         <div class="sm:col-span-1"><dt class="text-sm font-medium text-gray-500">Academic Year</dt><dd class="mt-1 text-sm text-gray-900">{{ $feeStructure->academicYear->name ?? 'N/A' }}</dd></div>
                         <div class="sm:col-span-1"><dt class="text-sm font-medium text-gray-500">Status</dt><dd class="mt-1 text-sm text-gray-900">{{ $feeStructure->is_active ? 'Active' : 'Inactive' }}</dd></div>
                         @if($feeStructure->description)
                            <div class="sm:col-span-3"><dt class="text-sm font-medium text-gray-500">Description</dt><dd class="mt-1 text-sm text-gray-900">{{ $feeStructure->description }}</dd></div>
                         @endif
                     </dl>
                </div>
            </div>

             {{-- Fee Items Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800 mb-4">Fee Items</h3>

                     @if(session('success'))
                        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert"><p>{{ session('success') }}</p></div>
                     @endif
                      @if(session('error'))
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>{{ session('error') }}</p></div>
                     @endif
                      @if ($errors->store_item && $errors->store_item->any()) {{-- Target errors for item form --}}
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <strong class="font-bold">Error adding item:</strong>
                            <ul> @foreach ($errors->store_item->all() as $error) <li>{{ $error }}</li> @endforeach </ul>
                        </div>
                    @endif


                     {{-- Table of Existing Items --}}
                     <div class="overflow-x-auto mb-6 border border-gray-200 sm:rounded-lg">
                         <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Income Account</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                             </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($feeStructure->items as $item)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->amount, 2) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->incomeAccount->name ?? 'N/A' }}
                                            ({{ $item->incomeAccount->account_code ?? 'N/A' }})
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-right">
                                            {{-- Delete Form --}}
                                             <form action="{{ route('accounting.fee-structures.items.destroy', [$feeStructure, $item]) }}" method="POST" onsubmit="return confirm('Delete this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-gray-500">No items added yet.</td></tr>
                                @endforelse
                            </tbody>
                         </table>
                     </div>

                      {{-- Add New Item Form --}}
                     <h4 class="text-md font-semibold text-gray-700 mb-3 pt-4 border-t">Add New Fee Item</h4>
                      <form method="POST" action="{{ route('accounting.fee-structures.items.store', $feeStructure) }}">
                         @csrf
                         <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="item_name" value="Item Name *" />
                                <x-text-input id="item_name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                            </div>
                             <div>
                                <x-input-label for="item_amount" value="Amount *" />
                                <x-text-input id="item_amount" name="amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('amount')" required />
                            </div>
                            <div>
                                <x-input-label for="item_income_account_id" value="Income Account *" />
                                <select id="item_income_account_id" name="income_account_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">-- Select --</option>
                                     @foreach($incomeAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('income_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->account_code }})
                                        </option>
                                     @endforeach
                                </select>
                            </div>
                             <div>
                                 <x-primary-button type="submit">Add Item</x-primary-button>
                             </div>
                         </div>
                          <div class="mt-2"> {{-- Description field spans full width below others --}}
                                <x-input-label for="item_description" value="Description (Optional)" />
                                <textarea id="item_description" name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                           </div>
                     </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>