<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between md:items-center space-y-2 md:space-y-0">
            {{-- Invoice Title/Number --}}
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Invoice') }} #{{ $invoice->invoice_number }}
                {{-- Status Badge in Header --}}
                <span class="ml-2 px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                    @switch($invoice->status)
                        @case('paid') bg-green-100 text-green-800 @break
                        @case('partial') bg-yellow-100 text-yellow-800 @break
                        @case('sent') bg-blue-100 text-blue-800 @break
                        @case('approved') bg-cyan-100 text-cyan-800 @break {{-- Added Approved status --}}
                        @case('overdue') bg-red-100 text-red-800 @break
                        @case('void') bg-gray-100 text-gray-500 @break
                        @default bg-gray-100 text-gray-800 {{-- Draft or other --}}
                    @endswitch">
                    {{ ucfirst($invoice->status) }}
                </span>
            </h2>

            {{-- Action Buttons --}}
            <div class="flex space-x-2 flex-wrap justify-start md:justify-end">

                {{-- Actions for DRAFT Invoices --}}
                @if($invoice->status == 'draft')
                    <a href="{{ route('accounting.invoices.edit', $invoice) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        Edit
                    </a>
                    {{-- Approve Button (using a form for POST/PATCH) --}}
                    <form action="{{ route('accounting.invoices.approve', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to approve this invoice? It may become uneditable.');">
                        @csrf
                        @method('POST') {{-- Or PATCH if your route is defined that way --}}
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                            Approve
                        </button>
                    </form>
                     {{-- Delete Button (using a form for DELETE) --}}
                    <form action="{{ route('accounting.invoices.destroy', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this draft invoice? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Delete Draft
                        </button>
                    </form>
                @endif

                {{-- Actions for APPROVED Invoices --}}
                @if($invoice->status == 'approved')
                    {{-- Mark as Sent Button (using a form for POST/PATCH) --}}
                    <form action="{{ route('accounting.invoices.send', $invoice) }}" method="POST" class="inline"> {{-- Renamed route to 'send' conceptually --}}
                        @csrf
                        @method('POST') {{-- Or PATCH --}}
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Mark as Sent
                        </button>
                    </form>
                     {{-- Email Button --}}
                    <a href="{{ route('accounting.invoices.email', $invoice) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Email Invoice
                    </a>
                @endif

                {{-- Actions for SENT or PARTIAL or OVERDUE (or APPROVED) --}}
                 @if(in_array($invoice->status, ['approved', 'sent', 'partial', 'overdue']))
                    {{-- Record Payment Link --}}
                    <a href="{{ route('accounting.payments.create', ['invoice_id' => $invoice->id]) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Record Payment
                    </a>
                 @endif

                 {{-- Actions for SENT or PARTIAL or OVERDUE --}}
                  @if(in_array($invoice->status, ['sent', 'partial', 'overdue']))
                      {{-- Resend Email Button --}}
                     <a href="{{ route('accounting.invoices.email', $invoice) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                         Resend Email
                     </a>
                 @endif

                 {{-- Actions generally available once not draft (excluding void) --}}
                 @if($invoice->status != 'draft' && $invoice->status != 'void')
                     {{-- Download PDF Button --}}
                     <a href="{{ route('accounting.invoices.download', $invoice) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                         Download PDF
                     </a>
                     {{-- Print Button --}}
                     <a href="{{ route('accounting.invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Print
                     </a>
                 @endif

                {{-- Back Button --}}
                <a href="{{ route('accounting.invoices.index') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
             @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 bg-white border-b border-gray-200">

                    {{-- Status Banner --}}
                    {{-- (Your existing status banner code is good, keep it here) --}}
                    <div class="mb-6">
                         @if($invoice->status == 'paid')
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                                <p class="text-sm font-medium">Paid in full on {{ optional(optional($invoice->payments->sortByDesc('payment_date')->first())->payment_date)->format('M d, Y') }}.</p>
                            </div>
                        @elseif($invoice->status == 'partial')
                            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
                                <p class="text-sm font-medium">Partially paid. Balance due: {{ number_format($invoice->total - $invoice->amount_paid, 2) }}</p>
                            </div>
                        @elseif($invoice->status == 'draft')
                            <div class="bg-gray-100 border-l-4 border-gray-500 text-gray-700 p-4 rounded">
                                <p class="text-sm font-medium">This is a DRAFT invoice and has not been sent.</p>
                            </div>
                        @elseif($invoice->status == 'approved')
                             <div class="bg-cyan-100 border-l-4 border-cyan-500 text-cyan-700 p-4 rounded">
                                <p class="text-sm font-medium">Invoice approved. Ready to be sent.</p>
                            </div>
                         @elseif($invoice->status == 'void')
                             <div class="bg-gray-100 border-l-4 border-gray-500 text-gray-700 p-4 rounded">
                                <p class="text-sm font-medium">This invoice has been VOIDED.</p>
                            </div>
                        @elseif($invoice->due_date < now() && $invoice->status != 'paid')
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                                <p class="text-sm font-medium">OVERDUE by {{ now()->diffInDays($invoice->due_date) }} days. Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                            </div>
                        @elseif($invoice->status == 'sent')
                            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded">
                                <p class="text-sm font-medium">Invoice sent. Due date: {{ $invoice->due_date->format('M d, Y') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Invoice Header --}}
                    {{-- (Your existing header code is good, keep it here) --}}
                     <div class="flex flex-col md:flex-row justify-between mb-8">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">Invoice</h1>
                            <p class="text-gray-600">#{{ $invoice->invoice_number }}</p>
                            @if($invoice->reference)
                                <p class="text-gray-600 mt-1">Reference: {{ $invoice->reference }}</p>
                            @endif
                        </div>
                        <div class="mt-4 md:mt-0 md:text-right">
                            <p class="text-gray-600">Issue Date: {{ optional($invoice->issue_date)->format('M d, Y') }}</p>
                            <p class="text-gray-600">Due Date: {{ optional($invoice->due_date)->format('M d, Y') }}</p>
                        </div>
                    </div>

                    {{-- Bill To / From Section --}}
                    {{-- (Your existing bill to/from code is good, keep it here) --}}
                    <div class="flex flex-col md:flex-row justify-between mb-8">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-gray-600 font-medium mb-2">From:</h3>
                            {{-- Consider making company details dynamic from settings --}}
                            <p class="font-medium">{{ config('app.name', 'Your School/Company') }}</p>
                            <p>{{ config('accounting.company_address', '123 Main St') }}</p>
                            <p>{{ config('accounting.company_phone', '555-1234') }}</p>
                            <p>{{ config('accounting.company_email', 'info@example.com') }}</p>
                        </div>
                        <div>
                            <h3 class="text-gray-600 font-medium mb-2">Bill To:</h3>
                            @if($invoice->contact)
                                <p class="font-medium">{{ $invoice->contact->name }}</p>
                                {{-- Add company name if it exists and is relevant --}}
                                {{-- @if($invoice->contact->company_name) <p>{{ $invoice->contact->company_name }}</p> @endif --}}
                                <p>{{ $invoice->contact->address }}</p>
                                @if($invoice->contact->city || $invoice->contact->state || $invoice->contact->postal_code)
                                    <p>{{ $invoice->contact->city }} {{ $invoice->contact->state }} {{ $invoice->contact->postal_code }}</p>
                                @endif
                                @if($invoice->contact->country) <p>{{ $invoice->contact->country }}</p> @endif
                                <p>{{ $invoice->contact->email }}</p>
                                @if($invoice->contact->phone) <p>{{ $invoice->contact->phone }}</p> @endif
                            @else
                                <p class="text-red-500">Contact not found</p>
                            @endif
                        </div>
                    </div>

                    {{-- Items Table --}}
                    {{-- (Your existing items table code is good, keep it here) --}}
                     <div class="mb-8">
                        <div class="overflow-x-auto border border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if($invoice->items && $invoice->items->count() > 0)
                                        @foreach($invoice->items as $item)
                                            <tr>
                                                <td class="px-4 py-4 whitespace-normal text-sm text-gray-900">{{ $item->description }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ $item->quantity }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                    {{ number_format($item->tax_amount ?? 0, 2) }}
                                                    @if($item->taxRate) ({{ $item->taxRate->rate ?? 'N/A' }}%) @endif
                                                    </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->subtotal ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="5" class="text-center py-4 text-gray-500">No items found for this invoice.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Invoice Summary --}}
                    {{-- (Your existing summary code is good, keep it here) --}}
                    <div class="flex justify-end mb-8">
                        <div class="w-full max-w-xs">
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex justify-between py-1 text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium text-gray-900">{{ number_format($invoice->subtotal ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between py-1 text-sm">
                                    <span class="text-gray-600">Tax:</span>
                                    <span class="font-medium text-gray-900">{{ number_format($invoice->tax_amount ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-t mt-2">
                                    <span class="text-gray-800 font-bold">Total:</span>
                                    <span class="font-bold text-gray-900">{{ number_format($invoice->total ?? 0, 2) }}</span>
                                </div>
                                @if(isset($invoice->amount_paid) && $invoice->amount_paid > 0)
                                    <div class="flex justify-between py-1 text-sm mt-2">
                                        <span class="text-green-600">Amount Paid:</span>
                                        <span class="font-medium text-green-600">(-) {{ number_format($invoice->amount_paid, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-t mt-2">
                                        <span class="text-gray-800 font-bold">Balance Due:</span>
                                        <span class="font-bold {{ ($invoice->total - $invoice->amount_paid) > 0.005 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format($invoice->total - $invoice->amount_paid, 2) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>


                    {{-- Notes and Terms --}}
                    {{-- (Your existing notes/terms code is good, keep it here) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        @if($invoice->notes)
                            <div>
                                <h3 class="text-gray-600 font-medium mb-2">Notes:</h3>
                                <div class="bg-gray-50 p-4 rounded">
                                    <p class="text-sm whitespace-pre-line">{{ $invoice->notes }}</p>
                                </div>
                            </div>
                        @endif
                        @if($invoice->terms)
                            <div>
                                <h3 class="text-gray-600 font-medium mb-2">Terms & Conditions:</h3>
                                <div class="bg-gray-50 p-4 rounded">
                                    <p class="text-sm whitespace-pre-line">{{ $invoice->terms }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Payments Section --}}
                    {{-- (Your existing payments section code is good, keep it here) --}}
                     @if($invoice->payments && $invoice->payments->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment History</h3>
                            <div class="overflow-x-auto shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                            {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th> --}}
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($invoice->payments->sortByDesc('payment_date') as $payment)
                                            <tr>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($payment->payment_date)->format('M d, Y') }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($payment->amount, 2) }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->paymentMethod->name ?? $payment->payment_method ?? 'N/A' }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->reference_number ?: '-' }}</td>
                                                {{-- <td class="px-6 py-4 whitespace-normal text-sm text-gray-500">{{ $payment->notes ?: '-' }}</td> --}}
                                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-right">
                                                     <a href="{{ route('accounting.payments.show', $payment->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif


                </div>
            </div>
        </div>
    </div>
</x-app-layout>