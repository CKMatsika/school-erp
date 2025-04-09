```blade
<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Payslip Details - {{ $payslip->staff->full_name }} ({{ \Carbon\Carbon::parse($payslip->pay_period.'-01')->format('M Y') }})
                 <span @class([
                    'ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    'bg-gray-100 text-gray-800' => $payslip->status == 'draft',
                    'bg-blue-100 text-blue-800' => $payslip->status == 'processed',
                    'bg-green-100 text-green-800' => $payslip->status == 'paid',
                    'bg-red-100 text-red-800' => $payslip->status == 'cancelled',
                 ])>
                    {{ ucfirst($payslip->status) }}
                </span>
            </h2>
             <div class="flex space-x-2">
                {{-- Add Print Button --}}
                 {{-- <button type="button" onclick="window.print();" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">Print</button> --}}
                 {{-- Post to Journal Button --}}
                 @if($payslip->status === 'processed' && !$payslip->journal_id)
                    {{-- @can('postPayroll', Payslip::class) --}}
                     <form action="{{ route('hr.payroll.payslips.post', $payslip) }}" method="POST" class="inline-block" onsubmit="return confirm('Post this payslip to the accounting journal?');">
                         @csrf
                         <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300">
                            Post to Journal
                         </button>
                     </form>
                     {{-- @endcan --}}
                 @endif
             </div>
        </div>
    </x-slot>
     <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
             @include('components.flash-messages')
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- Header Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm border-b pb-4">
                         <div><strong>Staff:</strong> <a href="{{ route('hr.staff.show', $payslip->staff_id)}}" class="text-indigo-600 hover:underline">{{ $payslip->staff->full_name ?? 'N/A' }}</a></div>
                         <div><strong>Staff #:</strong> {{ $payslip->staff->staff_number ?? 'N/A' }}</div>
                         <div><strong>Pay Period:</strong> {{ \Carbon\Carbon::parse($payslip->pay_period.'-01')->format('F Y') }}</div>
                         <div><strong>Status:</strong> {{ ucfirst($payslip->status) }}</div>
                         <div><strong>Processed:</strong> {{ $payslip->processed_at ? $payslip->processed_at->format('M d, Y H:i') : 'N/A' }}</div>
                         <div><strong>Journal Ref:</strong>
                            @if($payslip->journal_id)
                                <a href="{{ route('accounting.journals.show', $payslip->journal_id) }}" class="text-indigo-600 hover:underline">View Journal</a>
                            @else
                                Not Posted
                            @endif
                        </div>
                    </div>

                    {{-- Payroll Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-4">
                        {{-- Allowances --}}
                        <div>
                            <h4 class="font-semibold mb-2 border-b pb-1">Earnings / Allowances</h4>
                            <dl class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <dt>Basic Salary</dt>
                                    <dd class="text-right">{{ number_format($payslip->basic_salary, 2) }}</dd>
                                </div>
                                @foreach($payslip->items->where('type', 'allowance') as $item)
                                     <div class="flex justify-between">
                                        <dt>{{ $item->description }}</dt>
                                        <dd class="text-right">{{ number_format($item->amount, 2) }}</dd>
                                    </div>
                                @endforeach
                                 <div class="flex justify-between font-semibold border-t pt-1 mt-1">
                                    <dt>Gross Pay</dt>
                                    <dd class="text-right">{{ number_format($payslip->gross_pay, 2) }}</dd>
                                </div>
                            </dl>
                        </div>
                         {{-- Deductions --}}
                         <div>
                            <h4 class="font-semibold mb-2 border-b pb-1">Deductions</h4>
                            <dl class="space-y-1 text-sm">
                                @foreach($payslip->items->where('type', 'deduction') as $item)
                                     <div class="flex justify-between">
                                        <dt>{{ $item->description }}</dt>
                                        <dd class="text-right">({{ number_format($item->amount, 2) }})</dd>
                                    </div>
                                @endforeach
                                  <div class="flex justify-between font-semibold border-t pt-1 mt-1">
                                    <dt>Total Deductions</dt>
                                    <dd class="text-right">({{ number_format($payslip->total_deductions, 2) }})</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Net Pay Summary --}}
                    <div class="mt-6 pt-4 border-t text-right">
                         <div class="text-lg font-semibold">
                             <span>Net Pay:</span>
                             <span class="ml-4">{{ number_format($payslip->net_pay, 2) }}</span>
                         </div>
                    </div>
                </div>
                <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                    <a href="{{ route('hr.payroll.payslips.index', ['pay_period' => $payslip->pay_period]) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                        Back to Payslips
                    </a>
                 </div>
            </div>
        </div>
     </div>
</x-app-layout>