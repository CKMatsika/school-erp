<!-- resources/views/student/students/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('student.students.edit', $student) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('student.students.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alerts -->
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

            <!-- Student Status Alert -->
            @if($student->status !== 'active')
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Guardian Modal -->
    <div class="fixed inset-0 z-10 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" id="add-guardian-modal">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('student.guardians.addToStudent', $student) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Add Guardian for {{ $student->first_name }} {{ $student->last_name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Fill in the guardian details below.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" name="first_name" id="first_name" required 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" name="last_name" id="last_name" required 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="relationship" class="block text-sm font-medium text-gray-700">Relationship</label>
                                <select name="relationship" id="relationship" required 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select Relationship</option>
                                    <option value="father">Father</option>
                                    <option value="mother">Mother</option>
                                    <option value="guardian">Guardian</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="phone_primary" class="block text-sm font-medium text-gray-700">Primary Phone</label>
                                <input type="text" name="phone_primary" id="phone_primary" required 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email" 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label for="phone_secondary" class="block text-sm font-medium text-gray-700">Secondary Phone</label>
                                <input type="text" name="phone_secondary" id="phone_secondary" 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 mb-4">
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea name="address" id="address" rows="2" 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="occupation" class="block text-sm font-medium text-gray-700">Occupation</label>
                                <input type="text" name="occupation" id="occupation" 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_primary_contact" name="is_primary_contact" type="checkbox" 
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_primary_contact" class="font-medium text-gray-700">Primary Contact</label>
                                    <p class="text-gray-500">This guardian is the primary contact for the student.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="receives_communication" name="receives_communication" type="checkbox" 
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="receives_communication" class="font-medium text-gray-700">Receives Communication</label>
                                    <p class="text-gray-500">This guardian will receive school communications.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_emergency_contact" name="is_emergency_contact" type="checkbox" 
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_emergency_contact" class="font-medium text-gray-700">Emergency Contact</label>
                                    <p class="text-gray-500">Contact in case of emergency.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Add Guardian
                        </button>
                        <button type="button" onclick="closeAddGuardianModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Tab navigation
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('[id$="-content"]').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Deactivate all tabs
            document.querySelectorAll('[id$="-tab"]').forEach(tab => {
                tab.classList.remove('border-indigo-500', 'text-indigo-600');
                tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Activate selected tab
            document.getElementById(tabName + '-tab').classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById(tabName + '-tab').classList.add('border-indigo-500', 'text-indigo-600');
        }

        // Guardian modal functions
        function openAddGuardianModal() {
            document.getElementById('add-guardian-modal').classList.remove('hidden');
        }

        function closeAddGuardianModal() {
            document.getElementById('add-guardian-modal').classList.add('hidden');
        }
    </script>
    @endpush
                    </div>

                    <!-- Financial Records Tab (Hidden by default) -->
                    <div id="finances-content" class="p-6 bg-white border-b border-gray-200 hidden">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-700">Financial Records</h3>
                            <a href="{{ route('finance.invoices.create', ['student_id' => $student->id]) }}" 
                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Invoice
                            </a>
                        </div>

                        <div class="mb-6">
                            <h4 class="text-base font-medium text-gray-700 mb-3">Invoice Summary</h4>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm text-gray-500">Total Invoiced</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ number_format($student->invoices->sum('amount'), 2) }}
                                    </p>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm text-gray-500">Total Paid</p>
                                    <p class="text-2xl font-bold text-green-600">
                                        {{ number_format($student->payments->sum('amount'), 2) }}
                                    </p>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm text-gray-500">Outstanding Balance</p>
                                    <p class="text-2xl font-bold {{ $student->invoices->sum('amount') - $student->payments->sum('amount') > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ number_format($student->invoices->sum('amount') - $student->payments->sum('amount'), 2) }}
                                    </p>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm text-gray-500">Payment Status</p>
                                    @php
                                        $balance = $student->invoices->sum('amount') - $student->payments->sum('amount');
                                        $status = 'Paid';
                                        $statusClass = 'bg-green-100 text-green-800';
                                        
                                        if ($balance > 0) {
                                            $status = 'Outstanding';
                                            $statusClass = 'bg-red-100 text-red-800';
                                        } elseif ($balance < 0) {
                                            $status = 'Credit';
                                            $statusClass = 'bg-blue-100 text-blue-800';
                                        }
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ $status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Invoices Table -->
                        <h4 class="text-base font-medium text-gray-700 mb-3">Invoices</h4>
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($student->invoices as $invoice)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $invoice->invoice_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->invoice_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($invoice->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->getStatusColorClass() }}">
                                                {{ $invoice->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('finance.invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                            @if($invoice->status !== 'paid')
                                                <a href="{{ route('finance.payments.create', ['invoice_id' => $invoice->id]) }}" class="text-green-600 hover:text-green-900">Record Payment</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No invoices found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Payments Table -->
                        <h4 class="text-base font-medium text-gray-700 mb-3">Payment History</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($student->payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $payment->receipt_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $payment->payment_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $payment->payment_method }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $payment->reference_number ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($payment->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('finance.payments.show', $payment) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View Receipt</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No payment records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                This student is currently <strong>{{ ucfirst($student->status) }}</strong>
                                @if($student->status === 'graduated' && $student->graduation_date)
                                    (Graduated on {{ $student->graduation_date->format('F j, Y') }})
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Student Information Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Student Information</h3>
                        <div class="flex flex-col items-center mb-6">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->first_name }}" 
                                    class="h-32 w-32 rounded-full object-cover border-4 border-gray-200">
                            @else
                                <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 border-4 border-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                            <h2 class="mt-4 text-xl font-bold text-gray-800">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </h2>
                            <p class="text-gray-500">{{ $student->admission_number }}</p>
                        </div>

                        <!-- Student Info Grid -->
                        <div class="grid grid-cols-2 gap-y-3 gap-x-4">
                            <div class="text-sm font-medium text-gray-500">Current Class:</div>
                            <div class="text-sm text-gray-900">{{ $student->class->name ?? 'N/A' }}</div>

                            <div class="text-sm font-medium text-gray-500">Gender:</div>
                            <div class="text-sm text-gray-900">{{ ucfirst($student->gender) }}</div>

                            <div class="text-sm font-medium text-gray-500">Date of Birth:</div>
                            <div class="text-sm text-gray-900">
                                {{ $student->date_of_birth->format('M d, Y') }}
                                ({{ $student->date_of_birth->diffInYears(now()) }} years)
                            </div>

                            <div class="text-sm font-medium text-gray-500">Status:</div>
                            <div class="text-sm text-gray-900">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $student->getStatusColorClass() }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </div>

                            <div class="text-sm font-medium text-gray-500">Boarding Status:</div>
                            <div class="text-sm text-gray-900">
                                {{ $student->is_boarder ? 'Boarder' : 'Day Scholar' }}
                            </div>

                            <div class="text-sm font-medium text-gray-500">Enrollment Date:</div>
                            <div class="text-sm text-gray-900">{{ $student->enrollment_date->format('M d, Y') }}</div>

                            @if($student->status === 'graduated' && $student->graduation_date)
                                <div class="text-sm font-medium text-gray-500">Graduation Date:</div>
                                <div class="text-sm text-gray-900">{{ $student->graduation_date->format('M d, Y') }}</div>
                            @endif

                            <div class="col-span-2 border-t border-gray-200 pt-3 mt-3"></div>

                            <div class="text-sm font-medium text-gray-500">Email:</div>
                            <div class="text-sm text-gray-900">{{ $student->email ?? 'N/A' }}</div>

                            <div class="text-sm font-medium text-gray-500">Phone:</div>
                            <div class="text-sm text-gray-900">{{ $student->phone ?? 'N/A' }}</div>

                            <div class="text-sm font-medium text-gray-500">Address:</div>
                            <div class="text-sm text-gray-900">{{ $student->address ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Student Details Tab Section (spans 2 columns) -->
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex">
                            <a href="#" onclick="showTab('guardians')" id="guardians-tab" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                Guardians
                            </a>
                            <a href="#" onclick="showTab('enrollments')" id="enrollments-tab" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                Enrollment History
                            </a>
                            <a href="#" onclick="showTab('documents')" id="documents-tab" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                Documents
                            </a>
                            <a href="#" onclick="showTab('finances')" id="finances-tab" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                Financial Records
                            </a>
                        </nav>
                    </div>

                    <!-- Guardians Tab -->
                    <div id="guardians-content" class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-700">Guardians & Contacts</h3>
                            <button onclick="openAddGuardianModal()" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Guardian
                            </button>
                        </div>

                        <div class="space-y-4">
                            @forelse($student->guardians as $guardian)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex justify-between">
                                        <div>
                                            <h4 class="text-base font-medium text-gray-900">
                                                {{ $guardian->first_name }} {{ $guardian->last_name }}
                                            </h4>
                                            <p class="text-sm text-gray-500">{{ ucfirst($guardian->relationship) }}</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('student.guardians.edit', $guardian) }}" 
                                                class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                                            <a href="{{ route('student.guardians.show', $guardian) }}" 
                                                class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-4">
                                        <div>
                                            <span class="text-xs text-gray-500">Phone:</span>
                                            <p class="text-sm">{{ $guardian->phone_primary }}</p>
                                        </div>
                                        @if($guardian->email)
                                        <div>
                                            <span class="text-xs text-gray-500">Email:</span>
                                            <p class="text-sm">{{ $guardian->email }}</p>
                                        </div>
                                        @endif
                                        @if($guardian->phone_secondary)
                                        <div>
                                            <span class="text-xs text-gray-500">Secondary Phone:</span>
                                            <p class="text-sm">{{ $guardian->phone_secondary }}</p>
                                        </div>
                                        @endif
                                        @if($guardian->occupation)
                                        <div>
                                            <span class="text-xs text-gray-500">Occupation:</span>
                                            <p class="text-sm">{{ $guardian->occupation }}</p>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if($guardian->pivot->is_emergency_contact)
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                Emergency Contact
                                            </span>
                                        @endif
                                        @if($guardian->is_primary_contact)
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                Primary Contact
                                            </span>
                                        @endif
                                        @if($guardian->receives_communication)
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                Receives Communication
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No guardians</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by adding a guardian for this student.</p>
                                    <div class="mt-6">
                                        <button onclick="openAddGuardianModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Add Guardian
                                        </button>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Enrollment History Tab (Hidden by default) -->
                    <div id="enrollments-content" class="p-6 bg-white border-b border-gray-200 hidden">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-700">Enrollment History</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($student->documents as $document)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $document->file_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $document->documentType->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $document->upload_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($document->is_verified)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Verified
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('student.documents.download', $document) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Download</a>
                                            <a href="{{ route('student.documents.show', $document) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No documents found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>500 uppercase tracking-wider">Academic Year</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollment Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($student->enrollments as $enrollment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrollment->academicYear->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $enrollment->class->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $enrollment->enrollment_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $enrollment->getStatusColorClass() }}">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ Str::limit($enrollment->notes, 50) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No enrollment records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Documents Tab (Hidden by default) -->
                    <div id="documents-content" class="p-6 bg-white border-b border-gray-200 hidden">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-700">Student Documents</h3>
                            <a href="{{ route('student.documents.create', ['documentable_type' => 'student', 'documentable_id' => $student->id]) }}" 
                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Upload Document
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-