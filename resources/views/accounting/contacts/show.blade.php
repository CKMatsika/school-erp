<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Contact Details') }}: {{ $contact->name }}
            </h2>
            <div class="flex space-x-2">
                 <a href="{{ route('accounting.contacts.edit', $contact->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Edit
                </a>
                {{-- Optionally add Delete button here if needed, using the same logic as index --}}
                 @if($contact->invoices->count() === 0 && $contact->payments->count() === 0)
                    <form action="{{ route('accounting.contacts.destroy', $contact) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150" onclick="return confirm('Are you sure you want to delete this contact?')">
                            Delete
                        </button>
                    </form>
                @endif
                <a href="{{ route('accounting.contacts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

             {{-- Contact Details Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Contact Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $contact->name }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($contact->contact_type == 'customer') bg-blue-100 text-blue-800
                                    @elseif($contact->contact_type == 'vendor') bg-purple-100 text-purple-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($contact->contact_type) }}
                                </span>
                            </dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($contact->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </dd>
                        </div>

                        @if($contact->email)
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $contact->email }}</dd>
                        </div>
                        @endif
                         @if($contact->phone)
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $contact->phone }}</dd>
                        </div>
                        @endif
                         @if($contact->tax_number)
                         <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Tax / ID Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $contact->tax_number }}</dd>
                        </div>
                        @endif

                         @if($contact->address || $contact->city || $contact->state)
                         <div class="sm:col-span-3">
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $contact->address }}<br>
                                {{ $contact->city }}{{ $contact->state ? ', ' . $contact->state : '' }} {{ $contact->postal_code }}<br>
                                {{ $contact->country }}
                            </dd>
                        </div>
                        @endif

                        @if($contact->student)
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Linked Student</dt>
                            {{-- Add link to student profile if applicable --}}
                            <dd class="mt-1 text-sm text-gray-900">{{ $contact->student->name }} (ID: {{ $contact->student->student_number ?? $contact->student->id }})</dd>
                        </div>
                        @endif

                         <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Current Balance</dt>
                            <dd class="mt-1 text-lg font-semibold {{ $contact->getBalance() >= 0 ? 'text-gray-900' : 'text-red-600' }}">{{ number_format($contact->getBalance(), 2) }}</dd>
                         </div>
                    </dl>
                </div>
            </div>

            {{-- Invoices Section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800 mb-4">Invoices</h3>
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                 <tr>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                 </tr>
                             </thead>
                             <tbody class="bg-white divide-y divide-gray-200">
                                 @forelse ($contact->invoices->sortByDesc('issue_date') as $invoice)
                                     <tr>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($invoice->issue_date)->format('M d, Y') }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice->invoice_number }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($invoice->total, 2) }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-center">
                                             {{-- Status Badge Logic (reuse from index/dashboard) --}}
                                             @if($invoice->status == 'paid') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                             @elseif($invoice->status == 'partial') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                                             @elseif($invoice->status == 'sent') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Sent</span>
                                             @elseif($invoice->status == 'draft') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Draft</span>
                                             @elseif($invoice->status == 'overdue') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                                             @else <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($invoice->status ?? 'Unknown') }}</span>
                                             @endif
                                         </td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                             <a href="{{ route('accounting.invoices.show', $invoice->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                         </td>
                                     </tr>
                                 @empty
                                     <tr><td colspan="5" class="text-center py-4 text-gray-500">No invoices found.</td></tr>
                                 @endforelse
                             </tbody>
                         </table>
                     </div>
                </div>
            </div>

             {{-- Payments Section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800 mb-4">Payments Received</h3>
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                 <tr>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                 </tr>
                             </thead>
                             <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($contact->payments->sortByDesc('payment_date') as $payment)
                                     <tr>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($payment->payment_date)->format('M d, Y') }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->payment_number ?? $payment->id }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">{{ number_format($payment->amount, 2) }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-center">
                                             {{-- Status Badge Logic (reuse from index/dashboard) --}}
                                             @if($payment->status == 'completed') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                             @elseif($payment->status == 'pending') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                             @elseif($payment->status == 'failed') <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                             @else <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($payment->status ?? 'Unknown') }}</span>
                                             @endif
                                         </td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                             <a href="{{ route('accounting.payments.show', $payment->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                         </td>
                                     </tr>
                                 @empty
                                     <tr><td colspan="5" class="text-center py-4 text-gray-500">No payments found.</td></tr>
                                 @endforelse
                             </tbody>
                         </table>
                     </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>