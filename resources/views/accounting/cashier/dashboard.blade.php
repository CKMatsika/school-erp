<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cashier Dashboard') }}
            </h2>
            <div class="mt-2 sm:mt-0 flex flex-wrap gap-2">
                @if($activeSession)
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                    Session Active
                </span>
                @else
                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                    No Active Session
                </span>
                @endif
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                    {{ Auth::user()->name }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('components.flash-messages')
            
            <!-- Session Start/Status Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 bg-white border-b border-gray-200">
                    @if($activeSession)
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Active Session</h3>
                            <p class="text-sm text-gray-500">Started: {{ $activeSession->opening_time->format('M d, h:i A') }}</p>
                        </div>
                        <div class="mt-2 sm:mt-0 flex gap-2 flex-wrap">
                            <a href="{{ route('accounting.pos.z-reading', ['session' => $activeSession->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs sm:text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                                Session Report
                            </a>
                            <a href="{{ route('accounting.pos.sessions.end', $activeSession) }}" class="inline-flex items-center px-3 py-1.5 text-xs sm:text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                                End Session
                            </a>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-gray-50 p-2 rounded-md text-center">
                            <span class="text-xs text-gray-500">Transactions</span>
                            <p class="text-lg font-semibold">{{ $stats['transactions_count'] ?? 0 }}</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-md text-center">
                            <span class="text-xs text-gray-500">Total Amount</span>
                            <p class="text-lg font-semibold text-green-600">{{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-md text-center">
                            <span class="text-xs text-gray-500">Cash</span>
                            <p class="text-lg font-semibold">{{ number_format($stats['cash_amount'] ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-md text-center">
                            <span class="text-xs text-gray-500">Card</span>
                            <p class="text-lg font-semibold">{{ number_format($stats['card_amount'] ?? 0, 2) }}</p>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Session</h3>
                        <p class="text-sm text-gray-500 mb-4">Start a new session to begin processing payments</p>
                        <a href="{{ route('accounting.pos.sessions.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                            Start New Session
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            
            @if($activeSession)
            <!-- Quick Actions -->
            <div class="mb-4 grid grid-cols-2 gap-3">
                <a href="#payment-section" class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-4 rounded shadow flex flex-col items-center justify-center text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>New Payment</span>
                </a>
                
                <a href="{{ route('accounting.payments.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 px-4 rounded shadow flex flex-col items-center justify-center text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Recent Payments</span>
                </a>
            </div>
            
            <!-- Payment Processing Section -->
            <div id="payment-section" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Process Payment</h3>
                    
                    <form id="mobile-payment-form" method="POST" action="{{ route('accounting.pos.payment.process') }}">
                        @csrf
                        <input type="hidden" name="pos_session_id" value="{{ $activeSession->id ?? '' }}">
                        
                        <!-- Student Search -->
                        <div class="mb-4">
                            <label for="student-search" class="block text-sm font-medium text-gray-700 mb-1">Student</label>
                            <div class="relative">
                                <input type="text" id="student-search" placeholder="Name or admission number..." class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="hidden" name="contact_id" id="selected-student-id">
                            </div>
                            
                            <!-- Search Results Dropdown -->
                            <div id="search-results" class="hidden mt-1 max-h-60 w-full overflow-auto bg-white border border-gray-300 rounded-md shadow-lg z-10">
                                <!-- Results will be populated here via JavaScript -->
                            </div>
                        </div>
                        
                        <!-- Selected Student Info (Hidden until selection) -->
                        <div id="student-info" class="hidden mb-4 p-3 bg-gray-50 rounded-md border border-gray-200">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 id="student-name" class="font-medium"></h4>
                                    <p id="student-class" class="text-sm text-gray-500"></p>
                                </div>
                                <span id="student-balance" class="text-red-600 font-medium"></span>
                            </div>
                        </div>
                        
                        <!-- Payment Type Tabs -->
                        <div class="mb-4">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                                    <button type="button" id="tab-fees" class="tab-button active border-indigo-500 text-indigo-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                        Fees
                                    </button>
                                    <button type="button" id="tab-other" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                        Other Payments
                                    </button>
                                </nav>
                            </div>
                        </div>
                        
                        <!-- Fees Tab Content -->
                        <div id="fees-content" class="tab-content">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Outstanding Invoices</label>
                                <div id="invoices-list" class="border border-gray-200 rounded-md overflow-y-auto max-h-48 p-2 bg-gray-50">
                                    <p class="text-sm text-gray-500 text-center py-4">Select a student to view invoices</p>
                                    <!-- Invoices will be populated here -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Other Payments Tab Content -->
                        <div id="other-content" class="tab-content hidden">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Category</label>
                                <select name="other_category" id="other-category" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Category</option>
                                    <option value="uniform">School Uniform</option>
                                    <option value="books">Books & Stationery</option>
                                    <option value="trips">School Trips</option>
                                    <option value="exams">Examination Fees</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="payment-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <input type="text" id="payment-description" name="description" placeholder="e.g., Grade 7 Math Textbook" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <!-- Common Payment Fields -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="payment-method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <select id="payment-method" name="payment_method" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card Payment</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>
                            <div>
                                <label for="payment-amount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">KES</span>
                                    </div>
                                    <input type="number" name="amount" id="payment-amount" step="0.01" class="pl-12 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="payment-notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea id="payment-notes" name="notes" rows="2" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Optional payment notes..."></textarea>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex justify-between items-center">
                            <button type="button" id="reset-form" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                Reset
                            </button>
                            <button type="submit" id="submit-payment" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Process Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                        <a href="{{ route('accounting.payments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                    </div>
                    
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @forelse($recentTransactions ?? [] as $transaction)
                        <div class="border-b border-gray-200 pb-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium">{{ $transaction->payment->contact->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $transaction->created_at->format('M d, h:i A') }} &bull; {{ ucfirst($transaction->payment_method) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-green-600">KES {{ number_format($transaction->amount, 2) }}</p>
                                    <a href="{{ route('accounting.payments.receipt', $transaction->payment_id) }}" class="text-xs text-indigo-600">Receipt</a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-gray-500">
                            <p>No recent transactions found.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Card Payment Processing Modal -->
    <div id="card-payment-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg max-w-sm w-full mx-4 p-5">
            <div class="text-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Processing Card Payment</h3>
                <p class="text-sm text-gray-500 mt-1">Please follow the instructions on your card terminal</p>
            </div>
            
            <div class="animate-pulse flex flex-col items-center py-4">
                <svg class="w-16 h-16 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="mt-4 h-2 w-32 bg-indigo-200 rounded"></div>
            </div>
            
            <div class="mt-2 text-center">
                <p class="text-sm text-gray-600">
                    Amount: <span class="font-medium" id="modal-amount">KES 0.00</span>
                </p>
            </div>
            
            <div class="mt-5 flex justify-between">
                <button type="button" id="cancel-card-payment" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </button>
                
                <!-- For demonstration/testing only -->
                <button type="button" id="simulate-success" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    Simulate Success
                </button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons and hide all contents
                    tabButtons.forEach(b => b.classList.remove('active', 'border-indigo-500', 'text-indigo-600'));
                    tabButtons.forEach(b => b.classList.add('border-transparent', 'text-gray-500'));
                    tabContents.forEach(c => c.classList.add('hidden'));
                    
                    // Add active class to clicked button and show corresponding content
                    button.classList.add('active', 'border-indigo-500', 'text-indigo-600');
                    button.classList.remove('border-transparent', 'text-gray-500');
                    
                    const contentId = button.id.replace('tab-', '') + '-content';
                    document.getElementById(contentId).classList.remove('hidden');
                });
            });
            
            // Student search functionality
            const studentSearch = document.getElementById('student-search');
            const searchResults = document.getElementById('search-results');
            const selectedStudentId = document.getElementById('selected-student-id');
            const studentInfo = document.getElementById('student-info');
            const studentName = document.getElementById('student-name');
            const studentClass = document.getElementById('student-class');
            const studentBalance = document.getElementById('student-balance');
            const invoicesList = document.getElementById('invoices-list');
            
            studentSearch.addEventListener('input', function() {
                const query = this.value.trim();
                
                if (query.length < 2) {
                    searchResults.classList.add('hidden');
                    return;
                }
                
                // Simulate AJAX request to search for students
                // In a real implementation, this would make an API call
                setTimeout(() => {
                    // Mock data for demonstration
                    const mockResults = [
                        { id: 1, name: 'John Smith', class: 'Grade 8A', balance: 25000 },
                        { id: 2, name: 'Jane Doe', class: 'Grade 7B', balance: 15000 },
                        { id: 3, name: 'Bob Johnson', class: 'Grade 9C', balance: 5000 }
                    ];
                    
                    let resultsHtml = '';
                    
                    mockResults.forEach(student => {
                        resultsHtml += `
                            <div class="student-result p-2 hover:bg-gray-100 cursor-pointer" data-id="${student.id}" data-name="${student.name}" data-class="${student.class}" data-balance="${student.balance}">
                                <div class="font-medium">${student.name}</div>
                                <div class="text-xs text-gray-500">${student.class}</div>
                            </div>
                        `;
                    });
                    
                    searchResults.innerHTML = resultsHtml;
                    searchResults.classList.remove('hidden');
                    
                    // Add click event to results
                    document.querySelectorAll('.student-result').forEach(result => {
                        result.addEventListener('click', function() {
                            const id = this.dataset.id;
                            const name = this.dataset.name;
                            const className = this.dataset.class;
                            const balance = this.dataset.balance;
                            
                            // Set the selected student
                            selectedStudentId.value = id;
                            studentSearch.value = name;
                            studentName.textContent = name;
                            studentClass.textContent = className;
                            studentBalance.textContent = `KES ${parseFloat(balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                            
                            // Show student info
                            studentInfo.classList.remove('hidden');
                            
                            // Hide search results
                            searchResults.classList.add('hidden');
                            
                            // Load invoices for the selected student
                            loadInvoicesForStudent(id);
                        });
                    });
                }, 300);
            });
            
            // Function to load invoices for a student
            function loadInvoicesForStudent(studentId) {
                // Simulate AJAX request to load invoices
                // In a real implementation, this would make an API call
                setTimeout(() => {
                    // Mock data for demonstration
                    const mockInvoices = [
                        { id: 101, number: 'INV-2023-101', date: '2023-01-15', amount: 15000, paid: 5000, balance: 10000 },
                        { id: 102, number: 'INV-2023-102', date: '2023-02-10', amount: 8000, paid: 0, balance: 8000 },
                        { id: 103, number: 'INV-2023-103', date: '2023-03-05', amount: 12000, paid: 5000, balance: 7000 }
                    ];
                    
                    if (mockInvoices.length > 0) {
                        let invoicesHtml = '';
                        
                        mockInvoices.forEach(invoice => {
                            invoicesHtml += `
                                <div class="p-2 border-b last:border-b-0">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="invoice_ids[]" value="${invoice.id}" id="invoice-${invoice.id}" class="invoice-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="invoice-${invoice.id}" class="ml-2 block flex-grow">
                                            <div class="flex justify-between">
                                                <span class="font-medium text-sm">${invoice.number}</span>
                                                <span class="text-sm text-red-600">KES ${invoice.balance.toLocaleString()}</span>
                                            </div>
                                            <span class="text-xs text-gray-500">Due: ${new Date(invoice.date).toLocaleDateString()}</span>
                                        </label>
                                    </div>
                                </div>
                            `;
                        });
                        
                        invoicesList.innerHTML = invoicesHtml;
                        
                        // Add change event to invoice checkboxes to update amount
                        document.querySelectorAll('.invoice-checkbox').forEach(checkbox => {
                            checkbox.addEventListener('change', updateTotalAmount);
                        });
                    } else {
                        invoicesList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No outstanding invoices found</p>';
                    }
                }, 300);
            }
            
            // Function to update total amount based on selected invoices
            function updateTotalAmount() {
                const selectedInvoices = document.querySelectorAll('.invoice-checkbox:checked');
                let total = 0;
                
                selectedInvoices.forEach(checkbox => {
                    // This is just a mock implementation
                    // In reality, you would get the invoice amount from your data
                    const invoiceId = checkbox.value;
                    const mockAmounts = {
                        '101': 10000,
                        '102': 8000,
                        '103': 7000
                    };
                    
                    total += mockAmounts[invoiceId] || 0;
                });
                
                document.getElementById('payment-amount').value = total;
            }
            
            // Reset form
            document.getElementById('reset-form').addEventListener('click', function() {
                document.getElementById('mobile-payment-form').reset();
                studentInfo.classList.add('hidden');
                invoicesList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">Select a student to view invoices</p>';
            });
            
            // Card payment modal
            const paymentMethod = document.getElementById('payment-method');
            const submitPayment = document.getElementById('submit-payment');
            const cardModal = document.getElementById('card-payment-modal');
            const modalAmount = document.getElementById('modal-amount');
            const cancelCardPayment = document.getElementById('cancel-card-payment');
            const simulateSuccess = document.getElementById('simulate-success');
            const paymentForm = document.getElementById('mobile-payment-form');
            
            submitPayment.addEventListener('click', function(e) {
                if (paymentMethod.value === 'card') {
                    e.preventDefault();
                    const amount = document.getElementById('payment-amount').value;
                    modalAmount.textContent = `KES ${parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                    cardModal.classList.remove('hidden');
                }
                // For other payment methods, the form submits normally
            });
            
            cancelCardPayment.addEventListener('click', function() {
                cardModal.classList.add('hidden');
            });
            
            simulateSuccess.addEventListener('click', function() {
                // In a real implementation, this would be triggered by the POS terminal's response
                cardModal.classList.add('hidden');
                paymentForm.submit();
            });
            
            // Close search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#student-search') && !e.target.closest('#search-results')) {
                    searchResults.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>