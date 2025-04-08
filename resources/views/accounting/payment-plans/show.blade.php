<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
             <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payment Plan') }}: {{ $paymentPlan->name }}
             </h2>
              <div class="flex space-x-2">
                @if($paymentPlan->status == 'draft')
                    <a href="{{ route('accounting.payment-plans.edit', $paymentPlan) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-yellow-600 hover:bg-yellow-700">Edit Plan</a>
                    {{-- Form to Regenerate Schedule --}}
                     <form method="POST" action="{{ route('accounting.payment-plans.generate-schedule', $paymentPlan) }}" onsubmit="return confirm('Regenerate schedule? This will replace existing draft installments.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700">Regenerate Schedule</button>
                    </form>
                    {{-- TODO: Add Approve Button/Form here --}}

                @endif
                 <a href="{{ route('accounting.payment-plans.index') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Back to List</a>
              </div>
         </div>
    </x-slot>

     <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
             @include('components.flash-messages')

            {{-- Plan Summary Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800 mb-4">Plan Summary</h3>
                     <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                        <div><dt class="text-sm font-medium text-gray-500">Plan Name</dt><dd class="mt-1 text-sm text-gray-900">{{ $paymentPlan->name }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500">Student/Contact</dt><dd class="mt-1 text-sm text-gray-900">{{ $paymentPlan->contact->name ?? 'N/A' }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500">Related Invoice</dt><dd class="mt-1 text-sm text-indigo-600 hover:text-indigo-900">@if($paymentPlan->invoice)<a href="{{route('accounting.invoices.show', $paymentPlan->invoice_id)}}">#{{$paymentPlan->invoice->invoice_number}}</a>@else N/A @endif</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500">Total Amount</dt><dd class="mt-1 text-sm text-gray-900">{{ number_format($paymentPlan->total_amount, 2) }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500">Amount Paid</dt><dd class="mt-1 text-sm text-green-600">{{ number_format($paymentPlan->paid_amount, 2) }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500">Remaining Amount</dt><dd class="mt-1 text-sm font-semibold {{ $paymentPlan->remaining_amount > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($paymentPlan->remaining_amount, 2) }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500">Installments</dt><dd class="mt-1 text-sm text-gray-900">{{ $paymentPlan->number_of_installments }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500">Interval</dt><dd class="mt-1 text-sm text-gray-900">{{ ucfirst($paymentPlan->interval) }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500">Status</dt><dd class="mt-1 text-sm text-gray-900"><span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full @switch($paymentPlan->status) @case('completed') bg-green-100 text-green-800 @break @case('active') bg-blue-100 text-blue-800 @break @case('cancelled') bg-red-100 text-red-800 @break @default bg-gray-100 text-gray-800 @endswitch">{{ ucfirst($paymentPlan->status) }}</span></dd></div>
                        @if($paymentPlan->notes)
                        <div class="md:col-span-3"><dt class="text-sm font-medium text-gray-500">Notes</dt><dd class="mt-1 text-sm text-gray-900">{{ $paymentPlan->notes }}</dd></div>
                        @endif
                     </dl>
                </div>
            </div>

             {{-- Payment Schedule Card --}}
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Payment Schedule</h3>
                         {{-- Button to view full schedule if needed --}}
                         {{-- <a href="{{ route('accounting.payment-plans.schedule', $paymentPlan) }}" class="text-sm text-indigo-600 hover:text-indigo-800">View Full Schedule</a> --}}
                    </div>

                     <div class="overflow-x-auto shadow border border-gray-200 sm:rounded-lg">
                         <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount Due</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount Paid</th>
                                     <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                             </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($paymentPlan->paymentSchedules as $schedule)
                                    <tr>
                                        <td class="px-4 py-4 text-sm text-gray-500">{{ $schedule->installment_number }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-900">{{ optional($schedule->due_date)->format('M d, Y') }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-900 text-right">{{ number_format($schedule->amount, 2) }}</td>
                                        <td class="px-4 py-4 text-sm text-green-600 text-right">{{ number_format($schedule->paid_amount ?? 0, 2) }}</td>
                                        <td class="px-4 py-4 text-sm font-medium text-right {{ $schedule->remaining_amount > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($schedule->remaining_amount ?? $schedule->amount, 2) }}</td>
                                        <td class="px-4 py-4 text-center text-xs">
                                             {{-- Status Badge --}}
                                              <span class="px-2 py-0.5 inline-flex leading-5 font-semibold rounded-full @switch($schedule->status) @case('paid') bg-green-100 text-green-800 @break @case('partial') bg-yellow-100 text-yellow-800 @break @case('overdue') bg-red-100 text-red-800 @break @default bg-blue-100 text-blue-800 {{-- pending --}} @endswitch">
                                                {{ ucfirst($schedule->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-right">
                                             @if($schedule->status != 'paid')
                                                 {{-- Link to record payment for this specific installment? Complex. --}}
                                                 {{-- Easier to link to general payment page for the contact --}}
                                                 <a href="{{ route('accounting.payments.create', ['contact_id' => $paymentPlan->contact_id, /* 'invoice_id' => $paymentPlan->invoice_id (optional) */ ]) }}" class="text-green-600 hover:text-green-800 text-xs">Record Pmt</a>
                                             @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-4 text-gray-500">No schedule generated yet.</td></tr>
                                @endforelse
                            </tbody>
                         </table>
                     </div>
                 </div>
            </div>

        </div>
    </div>
</x-app-layout>