```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Generated Payslips') }}
            </h2>
             <a href="{{ route('hr.payroll.process.form') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Process New Payroll
            </a>
        </div>
    </x-slot>

     <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('components.flash-messages')

            {{-- Filters --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('hr.payroll.payslips.index') }}" class="flex flex-wrap items-end gap-4">
                        {{-- Pay Period --}}
                        <div class="w-full sm:w-auto">
                            <x-input-label for="pay_period" :value="__('Pay Period')" />
                             <select id="pay_period" name="pay_period" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Periods</option>
                                {{-- Ensure controller passes $payPeriods --}}
                                @foreach($payPeriods as $period)
                                    <option value="{{ $period }}" {{ request('pay_period', now()->format('Y-m')) == $period ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($period.'-01')->format('M Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Staff Member --}}
                        <div class="w-full sm:w-auto">
                            <x-input-label for="staff_id" :value="__('Staff Member')" />
                            <select id="staff_id" name="staff_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Staff</option>
                                {{-- Ensure controller passes $staffList --}}
                                @foreach($staffList as $staff)
                                    <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>{{ $staff->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Status --}}
                        <div class="w-full sm:w-auto">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Statuses</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                         {{-- Action Buttons --}}
                        <div class="flex space-x-2">
                             <x-primary-button type="submit">
                                {{ __('Filter') }}
                            </x-primary-button>
                             @if(request()->hasAny(['pay_period', 'staff_id', 'status']))
                                <a href="{{ route('hr.payroll.payslips.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    {{ __('Clear') }}
                                </a>
                            @endif
                        </div>
                    </form>
                 </div>
            </div>

            {{-- Results Table --}}
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-0 md:p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pay Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Pay</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Journal Ref</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                             <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payslips as $payslip)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($payslip->pay_period.'-01')->format('M Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                             <a href="{{ route('hr.staff.show', $payslip->staff_id) }}" class="text-indigo-600 hover:underline">
                                                {{ $payslip->staff->full_name ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($payslip->gross_pay, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($payslip->total_deductions, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-700">{{ number_format($payslip->net_pay, 2) }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-center">
                                             <span @class([
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                'bg-gray-100 text-gray-800' => $payslip->status == 'draft',
                                                'bg-blue-100 text-blue-800' => $payslip->status == 'processed',
                                                'bg-green-100 text-green-800' => $payslip->status == 'paid',
                                                'bg-red-100 text-red-800' => $payslip->status == 'cancelled',
                                            ])>
                                                {{ ucfirst($payslip->status) }}
                                            </span>
                                        </td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                            @if($payslip->journal_id)
                                                <a href="{{ route('accounting.journals.show', $payslip->journal_id) }}" class="text-indigo-600 hover:underline">View</a>
                                            @else
                                                -
                                            @endif
                                         </td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                             <a href="{{ route('hr.payroll.payslips.show', $payslip) }}" class="text-blue-600 hover:text-blue-900 mr-3" title="View Payslip">
                                                 <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                             </a>
                                             {{-- Add Post to Journal button --}}
                                             @if($payslip->status === 'processed' && !$payslip->journal_id)
                                                {{-- @can('postPayroll', Payslip::class) --}}
                                                 <form action="{{ route('hr.payroll.payslips.post', $payslip) }}" method="POST" class="inline-block" onsubmit="return confirm('Post this payslip to the accounting journal?');">
                                                     @csrf
                                                     <button type="submit" class="text-green-600 hover:text-green-900" title="Post to Journal">
                                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                     </button>
                                                 </form>
                                                 {{-- @endcan --}}
                                             @endif
                                             {{-- Add other actions like Print, Cancel --}}
                                        </td>
                                    </tr>
                                @empty
                                     <tr>
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No payslips found for the selected criteria.</td>
                                    </tr>
                                @endforelse
                             </tbody>
                        </table>
                    </div>
                      {{-- Pagination --}}
                    <div class="mt-4 px-6 pb-4">
                        {{ $payslips->links() }}
                    </div>
                </div>
            </div>
        </div>
     </div>
</x-app-layout>