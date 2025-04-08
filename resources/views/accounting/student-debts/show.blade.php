<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Debt Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Debt Information') }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('accounting.student-debts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                {{ __('Back to List') }}
                            </a>
                        </div>
                    </div>
                    
                    @include('components.flash-messages')
                    
                    <!-- Student Information -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-2">{{ __('Student Information') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Name') }}</p>
                                <p class="text-md font-medium">{{ $studentAccount->student_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Student ID') }}</p>
                                <p class="text-md font-medium">#{{ $studentAccount->student_id }}</p>
                            </div>
                            <!-- Add more student info as needed -->
                        </div>
                    </div>
                    
                    <!-- Debt Details -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-500">{{ __('Amount Due') }}</p>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($studentAccount->amount, 2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-500">{{ __('Due Date') }}</p>
                            <p class="text-xl font-bold text-gray-900">{{ \Carbon\Carbon::parse($studentAccount->due_date)->format('M d, Y') }}</p>
                            
                            @php
                                $daysOverdue = \Carbon\Carbon::parse($studentAccount->due_date)->isPast() 
                                    ? \Carbon\Carbon::parse($studentAccount->due_date)->diffInDays(\Carbon\Carbon::now()) 
                                    : 0;
                            @endphp
                            
                            @if($daysOverdue > 0)
                                <p class="text-sm text-red-500 mt-1">{{ $daysOverdue }} {{ __('days overdue') }}</p>
                            @elseif(!$studentAccount->paid_at)
                                <p class="text-sm text-green-500 mt-1">{{ \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($studentAccount->due_date)) }} {{ __('days remaining') }}</p>
                            @endif
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-500">{{ __('Status') }}</p>
                            @if($studentAccount->paid_at)
                                <p class="text-xl font-bold text-green-600">{{ __('Paid') }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ \Carbon\Carbon::parse($studentAccount->paid_at)->format('M d, Y') }}</p>
                            @elseif(\Carbon\Carbon::parse($studentAccount->due_date)->isPast())
                                <p class="text-xl font-bold text-red-600">{{ __('Overdue') }}</p>
                            @else
                                <p class="text-xl font-bold text-yellow-600">{{ __('Outstanding') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Transaction Details -->
                    <div class="bg-white rounded-lg border border-gray-200 mb-6">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h4 class="text-md font-medium text-gray-700">{{ __('Transaction Details') }}</h4>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Description') }}</p>
                                <p class="text-md">{{ $studentAccount->description }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Reference Number') }}</p>
                                <p class="text-md">{{ $studentAccount->reference_number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Created At') }}</p>
                                <p class="text-md">{{ \Carbon\Carbon::parse($studentAccount->created_at)->format('M d, Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Last Updated') }}</p>
                                <p class="text-md">{{ \Carbon\Carbon::parse($studentAccount->updated_at)->format('M d, Y H:i') }}</p>
                            </div>
                            <!-- Add more fields as needed -->
                        </div>
                    </div>
                    
                    <!-- Payment History -->
                    <div class="bg-white rounded-lg border border-gray-200 mb-6">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h4 class="text-md font-medium text-gray-700">{{ __('Payment History') }}</h4>
                        </div>
                        <div class="overflow-x-auto">
                            @if(isset($payments) && count($payments) > 0)
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Method') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Reference') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($payments as $payment)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($payment->amount, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_method }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->reference_number }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ __('Completed') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="p-4 text-sm text-gray-700">
                                    {{ __('No payment records found for this debt.') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-4">
                        @if(!$studentAccount->paid_at)
                            <a href="#" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                {{ __('Record Payment') }}
                            </a>
                        @endif
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            {{ __('Send Reminder') }}
                        </a>
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            {{ __('Print Invoice') }}
                        </a>
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('Edit') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>