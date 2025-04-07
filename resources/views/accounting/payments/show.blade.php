<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payment Details') }} #{{ $payment->payment_number }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('accounting.payments.receipt', $payment) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Print Receipt
                </a>
                <a href="{{ route('accounting.payments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to Payments
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    <!-- Payment Status -->
                    <div class="mb-6">
                        @if($payment->status === 'completed')
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">
                                            Payment was completed successfully on {{ $payment->payment_date->format('M d, Y') }}.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @elseif($payment->status === 'pending')
                            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">
                                            This payment is pending processing.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @elseif($payment->status === 'failed')
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">
                                            This payment failed to process.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Payment Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                            
                            <dl class="grid grid-cols-1 gap-y-3">
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Payment Number</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->payment_number }}</dd>
                                </div>
                                
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Receipt Number</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->receipt_number ?? 'Not generated' }}</dd>
                                </div>
                                
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->payment_date->format('M d, Y') }}</dd>
                                </div>
                                
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                    <dd class="text-sm text-gray-900 font-semibold">{{ number_format($payment->amount, 2) }}</dd>
                                </div>
                                
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->paymentMethod->name }}</dd>
                                </div>
                                
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Reference</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->reference ?? 'N/A' }}</dd>
                                </div>
                                
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="text-sm">
                                        @if($payment->status === 'completed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                        @elseif($payment->status === 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($payment->status === 'failed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Deposit Account</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->account->name }}</dd>
                                </div>
                                
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->creator->name }}</dd>
                                </div>
                                
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Created On</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Customer Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                            
                            <dl class="grid grid-cols-1 gap-y-3">
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Contact Name</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->contact->name }}</dd>
                                </div>
                                
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Contact Type</dt>
                                    <dd class="text-sm text-gray-900">{{ ucfirst($payment->contact->type) }}</dd>
                                </div>
                                
                                @if($payment->contact->email)
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->contact->email }}</dd>
                                </div>
                                @endif
                                
                                @if($payment->contact->phone)
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->contact->phone }}</dd>
                                </div>
                                @endif
                                
                                @if($payment->student)
                                <div class="grid grid-cols-2">
                                    <dt class="text-sm font-medium text-gray-500">Student</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</dd>
                                </div>
                                @endif
                                
                                @if($payment->invoice)
                                <div class="mt-4">
                                    <h4 class="text-md font-medium text-gray-700 mb-2">Applied to Invoice</h4>
                                    
                                    <a href="{{ route('accounting.invoices.show', $payment->invoice) }}" class="block bg-white p-4 border border-gray-200 rounded-md hover:bg-gray-50">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="text-sm font-medium text-indigo-600">{{ $payment->invoice->invoice_number }}</p>
                                                <p class="text-xs text-gray-500">{{ $payment->invoice->issue_date->format('M d, Y') }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-gray-900">{{ number_format($payment->invoice->total, 2) }}</p>
                                                <p class="text-xs text-gray-500">
                                                    @if($payment->invoice->status === 'paid')
                                                        <span class="text-green-600">Paid in Full</span>
                                                    @elseif($payment->invoice->status === 'partial')
                                                        <span class="text-yellow-600">Partially Paid</span>
                                                    @else
                                                        <span class="text-gray-600">{{ ucfirst($payment->invoice->status) }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($payment->notes)
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Notes</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $payment->notes }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Journal Entries -->
                    @if($payment->journal)
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Journal Entries</h3>
                        
                        <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($payment->journal->entries as $entry)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $entry->account->name }} ({{ $entry->account->account_code }})
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $entry->debit > 0 ? number_format($entry->debit, 2) : '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $entry->credit > 0 ? number_format($entry->credit, 2) : '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $entry->description }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="mt-6 flex justify-end space-x-3">
                        @if($payment->status === 'pending')
                            <a href="{{ route('accounting.payments.edit', $payment) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Edit Payment
                            </a>
                            
                            <form method="POST" action="{{ route('accounting.payments.destroy', $payment) }}" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    onclick="return confirm('Are you sure you want to delete this payment?')">
                                    Delete Payment
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>