<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Invoices') }}
            </h2>
            <a href="{{ route('accounting.invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                Create Invoice
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
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

                    {{-- Optional Filters can go here --}}

                    <div class="overflow-x-auto shadow border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Date</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance Due</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($invoices as $invoice)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                            <a href="{{ route('accounting.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->contact->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($invoice->issue_date)->format('M d, Y') }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 {{ $invoice->due_date < now() && $invoice->status != 'paid' && $invoice->status != 'void' ? 'text-red-600 font-semibold' : '' }}">{{ optional($invoice->due_date)->format('M d, Y') }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($invoice->total ?? 0, 2) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($invoice->total - $invoice->amount_paid, 2) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                             {{-- Status Badge Logic (reuse from show view) --}}
                                             <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @switch($invoice->status)
                                                    @case('paid') bg-green-100 text-green-800 @break
                                                    @case('partial') bg-yellow-100 text-yellow-800 @break
                                                    @case('sent') bg-blue-100 text-blue-800 @break
                                                    @case('approved') bg-cyan-100 text-cyan-800 @break
                                                    @case('overdue') bg-red-100 text-red-800 @break
                                                    @case('void') bg-gray-100 text-gray-500 @break
                                                    @default bg-gray-100 text-gray-800 {{-- Draft or other --}}
                                                @endswitch">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-center">
                                            {{-- Simple Links (replace with dropdown for more actions) --}}
                                            <a href="{{ route('accounting.invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 mr-2" title="View">View</a>

                                            @if($invoice->status == 'draft')
                                                <a href="{{ route('accounting.invoices.edit', $invoice) }}" class="text-yellow-600 hover:text-yellow-900 mr-2" title="Edit">Edit</a>
                                                 {{-- Approve form - simplified for index --}}
                                                <form action="{{ route('accounting.invoices.approve', $invoice) }}" method="POST" class="inline-block" onsubmit="return confirm('Approve this invoice?');">
                                                    @csrf @method('POST')
                                                    <button type="submit" class="text-cyan-600 hover:text-cyan-900 mr-2" title="Approve">Approve</button>
                                                </form>
                                                {{-- Delete form --}}
                                                <form action="{{ route('accounting.invoices.destroy', $invoice) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this draft?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">Delete</button>
                                                </form>
                                            @elseif(in_array($invoice->status, ['approved']))
                                                 {{-- Send form - simplified --}}
                                                <form action="{{ route('accounting.invoices.send', $invoice) }}" method="POST" class="inline-block">
                                                    @csrf @method('POST')
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 mr-2" title="Mark as Sent">Send</button>
                                                </form>
                                                <a href="{{ route('accounting.invoices.download', $invoice) }}" class="text-gray-600 hover:text-gray-900 mr-2" title="Download PDF">PDF</a>

                                            @elseif(in_array($invoice->status, ['sent', 'partial', 'overdue']))
                                                <a href="{{ route('accounting.payments.create', ['invoice_id' => $invoice->id]) }}" class="text-green-600 hover:text-green-900 mr-2" title="Record Payment">Pay</a>
                                                <a href="{{ route('accounting.invoices.download', $invoice) }}" class="text-gray-600 hover:text-gray-900 mr-2" title="Download PDF">PDF</a>
                                                <a href="{{ route('accounting.invoices.email', $invoice) }}" class="text-purple-600 hover:text-purple-900 mr-2" title="Email">Email</a>
                                            @elseif(in_array($invoice->status, ['paid', 'void']))
                                                 <a href="{{ route('accounting.invoices.download', $invoice) }}" class="text-gray-600 hover:text-gray-900 mr-2" title="Download PDF">PDF</a>
                                            @endif
                                            {{-- Add more actions or use a dropdown component --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No invoices found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    {{-- Uncomment and use if you implement pagination in the controller
                    <div class="mt-4">
                        {{ $invoices->links() }}
                    </div>
                    --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>