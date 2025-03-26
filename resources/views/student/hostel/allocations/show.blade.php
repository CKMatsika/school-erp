{{-- resources/views/student/hostel/allocations/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Allocation Details') }}
            </h2>
            <div>
                <a href="{{ route('student.hostel.allocations.edit', $allocation->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    Edit Allocation
                </a>
                <a href="{{ route('student.hostel.allocations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to Allocations
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Banner -->
            <div class="mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200 
                        @if($allocation->status === 'active') bg-green-50 
                        @elseif($allocation->status === 'pending') bg-yellow-50 
                        @else bg-gray-50 @endif">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="@if($allocation->status === 'active') text-green-800 
                                    @elseif($allocation->status === 'pending') text-yellow-800 
                                    @else text-gray-800 @endif font-semibold">
                                    Allocation Status: {{ ucfirst($allocation->status) }}
                                </div>
                                
                                <span class="mx-2 text-gray-400">|</span>
                                
                                <div class="@if($allocation->payment_status === 'paid') text-green-800 
                                    @elseif($allocation->payment_status === 'partial') text-blue-800 
                                    @else text-red-800 @endif font-semibold">
                                    Payment Status: {{ ucfirst($allocation->payment_status) }}
                                </div>
                            </div>
                            
                            <div>
                                @if($allocation->status === 'active')
                                    <form method="POST" action="{{ route('student.hostel.allocations.terminate', $allocation->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to terminate this allocation?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            Terminate Allocation
                                        </button>
                                    </form>
                                @elseif($allocation->status === 'pending')
                                    <form method="POST" action="{{ route('student.hostel.allocations.activate', $allocation->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Activate Allocation
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Allocation Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Allocation Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dl>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                    <dt class="text-sm font-medium text-gray-500">Allocation ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $allocation->id }}</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $allocation->start_date->format('M d, Y') }}</dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $allocation->end_date->format('M d, Y') }}</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Created On</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $allocation->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $allocation->updated_at->format('M d, Y H:i') }}</dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div>
                            <dl>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-t-md">
                                    <dt class="text-sm font-medium text-gray-500">Academic Year</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $allocation->academic_year }}-{{ $allocation->academic_year + 1 }}</dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                                    <dd class="mt-1 sm:mt-0 sm:col-span-2">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($allocation->payment_status === 'paid') bg-green-100 text-green-800 
                                            @elseif($allocation->payment_status === 'partial') bg-blue-100 text-blue-800 
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($allocation->payment_status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Cost</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        ${{ number_format($allocation->cost, 2) }}
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $allocation->notes ?: 'No notes provided' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Student Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Student Information</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-xl font-semibold text-gray-600">{{ substr($allocation->student->name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="text-lg font-medium">{{ $allocation->student->name }}</div>
                                <div class="text-sm text-gray-500">ID: {{ $allocation->student->student_id }}</div>
                            </div>
                            <div>
                                <a href="{{ route('student.hostel.students.show', $allocation->student->id) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:border-indigo-300 focus:ring ring-indigo-300 active:bg-indigo-200 transition ease-in-out duration-150">
                                    View Student Profile
                                </a>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Program/Course</div>
                                <div>{{ $allocation->student->program }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Year of Study</div>
                                <div>{{ $allocation->student->year_of_study }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Contact</div>
                                <div>{{ $allocation->student->email }}</div>
                                <div>{{ $allocation->student->phone }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bed Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Accommodation Details</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">House</div>
                                <div class="font-medium">{{ $allocation->bed->room->house->name }}</div>
                                <div class="text-xs text-gray-500">{{ $allocation->bed->room->house->code }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Room</div>
                                <div class="font-medium">{{ $allocation->bed->room->room_number }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst($allocation->bed->room->room_type) }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Bed</div>
                                <div class="font-medium">{{ $allocation->bed->bed_number }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $allocation->bed->bed_type)) }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Floor</div>
                                <div class="font-medium">{{ $allocation->bed->room->floor }}</div>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('student.hostel.beds.show', $allocation->bed->id) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:border-indigo-300 focus:ring ring-indigo-300 active:bg-indigo-200 transition ease-in-out duration-150 mr-2">
                                View Bed Details
                            </a>
                            <a href="{{ route('student.hostel.rooms.show', $allocation->bed->room->id) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:border-indigo-300 focus:ring ring-indigo-300 active:bg-indigo-200 transition ease-in-out duration-150">
                                View Room Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Payment History</h3>
                        <a href="{{ route('student.hostel.payments.create', ['allocation_id' => $allocation->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Record Payment
                        </a>
                    </div>
                    
                    @if($payments->isEmpty())
                        <div class="text-gray-500 text-center py-4">No payment records found for this allocation.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt Number</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $payment->payment_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">${{ number_format($payment->amount, 2) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ ucfirst($payment->payment_method) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $payment->receipt_number ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $payment->notes ?: '-' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td class="px-6 py-3 text-right text-sm font-medium text-gray-900" colspan="1">Total Paid:</td>
                                        <td class="px-6 py-3 text-sm font-medium text-gray-900" colspan="4">${{ number_format($totalPaid, 2) }} of ${{ number_format($allocation->cost, 2) }} ({{ $paymentPercentage }}%)</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>