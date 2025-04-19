<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Record Payment') }}
            </h2>
            <a href="{{ route('accounting.payments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Back to Payments
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <!-- Session Error Message (Keep this for controller specific errors) -->
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif
                     <!-- Session Warning Message (Keep this for non-blocking warnings like email failure) -->
                    @if(session('warning_email'))
                        <div class="mb-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                            <p>{{ session('warning_email') }}</p>
                        </div>
                    @endif

                    <!-- ***** START: Validation Error Summary Block ***** -->
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Whoops! Something went wrong.</strong>
                            <span class="block sm:inline">Please check the errors below.</span>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <!-- ***** END: Validation Error Summary Block ***** -->


                    <form method="POST" action="{{ route('accounting.payments.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Contact Information -->
                            <div>
                                <label for="contact_id" class="block text-sm font-medium text-gray-700">Contact *</label>
                                <select id="contact_id" name="contact_id" class="mt-1 block w-full py-2 px-3 border {{ $errors->has('contact_id') ? 'border-red-500' : 'border-gray-300' }} bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required {{ isset($selectedInvoice) ? 'disabled' : '' }}>
                                    <option value="">Select Contact</option>
                                    @foreach($contacts as $contact)
                                        <option value="{{ $contact->id }}" {{ (old('contact_id', isset($selectedInvoice) ? $selectedInvoice->contact_id : '')) == $contact->id ? 'selected' : '' }}>
                                            {{ $contact->name }} ({{ ucfirst($contact->contact_type ?? 'N/A') }}) {{-- Adjusted to handle potential null type --}}
                                        </option>
                                    @endforeach
                                </select>
                                @if(isset($selectedInvoice))
                                    <input type="hidden" name="contact_id" value="{{ $selectedInvoice->contact_id }}">
                                @endif
                                @error('contact_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Invoice Selection -->
                            <div>
                                <label for="invoice_id" class="block text-sm font-medium text-gray-700">Invoice (Optional)</label>
                                <select id="invoice_id" name="invoice_id" class="mt-1 block w-full py-2 px-3 border {{ $errors->has('invoice_id') ? 'border-red-500' : 'border-gray-300' }} bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" {{ isset($selectedInvoice) ? 'disabled' : '' }}>
                                    <option value="">No Invoice (General Payment)</option>
                                    @if(isset($contactInvoices) && count($contactInvoices) > 0)
                                        @foreach($contactInvoices as $invoice)
                                            <option value="{{ $invoice->id }}" {{ (old('invoice_id', isset($selectedInvoice) ? $selectedInvoice->id : '')) == $invoice->id ? 'selected' : '' }}
                                                data-balance="{{ max(0, $invoice->total - ($invoice->amount_paid ?? 0)) }}"> {{-- Calculate balance here --}}
                                                {{ $invoice->invoice_number }} - Due: {{ number_format(max(0, $invoice->total - ($invoice->amount_paid ?? 0)), 2) }} ({{ $invoice->due_date->format('M d, Y') }})
                                            </option>
                                        @endforeach
                                    @elseif(isset($selectedInvoice)) {{-- Ensure selected invoice shows even if no others exist --}}
                                         <option value="{{ $selectedInvoice->id }}" selected
                                                data-balance="{{ max(0, $selectedInvoice->total - ($selectedInvoice->amount_paid ?? 0)) }}">
                                                {{ $selectedInvoice->invoice_number }} - Due: {{ number_format(max(0, $selectedInvoice->total - ($selectedInvoice->amount_paid ?? 0)), 2) }} ({{ $selectedInvoice->due_date->format('M d, Y') }})
                                            </option>
                                    @endif
                                </select>
                                @if(isset($selectedInvoice))
                                    <input type="hidden" name="invoice_id" value="{{ $selectedInvoice->id }}">
                                @endif
                                @error('invoice_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label for="payment_method_id" class="block text-sm font-medium text-gray-700">Payment Method *</label>
                                <select id="payment_method_id" name="payment_method_id" class="mt-1 block w-full py-2 px-3 border {{ $errors->has('payment_method_id') ? 'border-red-500' : 'border-gray-300' }} bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="">Select Payment Method</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                                            {{ $method->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Bank Account -->
                            <div>
                                <label for="account_id" class="block text-sm font-medium text-gray-700">Deposit Account *</label>
                                <select id="account_id" name="account_id" class="mt-1 block w-full py-2 px-3 border {{ $errors->has('account_id') ? 'border-red-500' : 'border-gray-300' }} bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="">Select Account</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->account_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Payment Date -->
                            <div>
                                <label for="payment_date" class="block text-sm font-medium text-gray-700">Payment Date *</label>
                                <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm {{ $errors->has('payment_date') ? 'border-red-500' : 'border-gray-300' }} rounded-md" required>
                                @error('payment_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">Amount *</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        {{-- Assuming base currency symbol is $, adjust if needed --}}
                                        <span class="text-gray-500 sm:text-sm">
                                            {{ config('accounting.currency_symbol', '$') }}
                                        </span>
                                    </div>
                                    {{-- Changed type="text" to allow formatting, but ensure validation handles non-numeric --}}
                                    <input type="text" name="amount" id="amount" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm {{ $errors->has('amount') ? 'border-red-500' : 'border-gray-300' }} rounded-md" placeholder="0.00" value="{{ old('amount', isset($selectedInvoice) ? number_format(max(0, $selectedInvoice->total - ($selectedInvoice->amount_paid ?? 0)), 2, '.', '') : '') }}" required>
                                </div>
                                @error('amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p id="balance-warning" class="text-yellow-600 text-xs mt-1 hidden">Amount exceeds invoice balance.</p>
                            </div>

                            <!-- Reference Number -->
                            <div>
                                <label for="reference" class="block text-sm font-medium text-gray-700">Reference Number</label>
                                <input type="text" name="reference" id="reference" value="{{ old('reference') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm {{ $errors->has('reference') ? 'border-red-500' : 'border-gray-300' }} rounded-md" placeholder="Check #, Transaction ID, etc.">
                                @error('reference')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" id="notes" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm {{ $errors->has('notes') ? 'border-red-500' : 'border-gray-300' }} rounded-md">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Send Confirmation -->
                            <div class="md:col-span-2">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        {{-- Added value="1" which is standard practice for boolean checkboxes --}}
                                        <input id="send_confirmation" name="send_confirmation" type="checkbox" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('send_confirmation') ? 'checked' : '' }}>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="send_confirmation" class="font-medium text-gray-700">Send payment confirmation to contact</label>
                                    </div>
                                    {{-- Optional: Add error display for send_confirmation if needed --}}
                                     @error('send_confirmation')
                                         <p class="text-red-500 text-xs mt-1 ml-3">{{ $message }}</p>
                                     @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Record Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contactSelect = document.getElementById('contact_id');
            const invoiceSelect = document.getElementById('invoice_id');
            const amountInput = document.getElementById('amount');
            const balanceWarning = document.getElementById('balance-warning');

            // Function to fetch and populate invoices
            function fetchInvoices(contactId) {
                 // Clear current options first
                 invoiceSelect.innerHTML = '<option value="">Loading invoices...</option>';
                 if (!contactId) {
                     invoiceSelect.innerHTML = '<option value="">No Invoice (General Payment)</option>';
                     return;
                 }

                 fetch(`/accounting/payments/contact-invoices?contact_id=${contactId}`)
                    .then(response => {
                        if (!response.ok) { throw new Error('Network response was not ok'); }
                        return response.json();
                    })
                    .then(data => {
                        invoiceSelect.innerHTML = '<option value="">No Invoice (General Payment)</option>'; // Reset default
                         if (data.error) {
                            console.error('Error fetching invoices:', data.error);
                             // Optionally display error to user
                             return;
                         }
                        data.forEach(invoice => {
                            // Ensure balance is calculated correctly
                            const balance = Math.max(0, parseFloat(invoice.total || 0) - parseFloat(invoice.amount_paid || 0));
                            const option = document.createElement('option');
                            option.value = invoice.id;
                            // Format date safely
                            let dueDateFormatted = 'N/A';
                            try {
                                if (invoice.due_date) {
                                    dueDateFormatted = new Date(invoice.due_date).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
                                }
                             } catch (e) { console.error("Error formatting date", invoice.due_date, e); }

                            option.textContent = `${invoice.invoice_number} - Due: ${balance.toFixed(2)} (${dueDateFormatted})`;
                            option.dataset.balance = balance.toFixed(2); // Store balance with 2 decimals
                            invoiceSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                         console.error('Fetch error:', error);
                         invoiceSelect.innerHTML = '<option value="">Error loading invoices</option>';
                    });
            }

            // Load invoices when contact changes (if not disabled)
            if (contactSelect && !contactSelect.disabled) {
                contactSelect.addEventListener('change', function() {
                    fetchInvoices(this.value);
                    // Reset amount when contact changes
                    amountInput.value = '0.00';
                    checkAmount(); // Hide warning
                });
                // Initial load if a contact is pre-selected via old() on page refresh
                // if (contactSelect.value) {
                //    fetchInvoices(contactSelect.value);
                // }
            }

            // Function to check amount against selected invoice balance
            function checkAmount() {
                const selectedOption = invoiceSelect.options[invoiceSelect.selectedIndex];
                balanceWarning.classList.add('hidden'); // Hide by default

                if (invoiceSelect.value && selectedOption && selectedOption.dataset.balance) {
                    const balance = parseFloat(selectedOption.dataset.balance);
                    const amount = parseFloat(amountInput.value.replace(/[^\d.-]/g, '')); // Clean input

                    if (!isNaN(amount) && amount > balance && balance >= 0) { // Only show warning if balance is positive
                        balanceWarning.textContent = `Amount exceeds invoice balance of ${balance.toFixed(2)}.`;
                        balanceWarning.classList.remove('hidden');
                    }
                }
            }

            // Event listeners for invoice selection and amount input
            if (invoiceSelect && amountInput && balanceWarning) {
                invoiceSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    // If an invoice is selected, pre-fill the amount to the balance
                    if (this.value && selectedOption && selectedOption.dataset.balance) {
                        amountInput.value = parseFloat(selectedOption.dataset.balance).toFixed(2);
                    } else {
                         // If "No Invoice" selected, maybe clear amount or leave as is? Clear is safer.
                         // amountInput.value = '0.00';
                    }
                    checkAmount(); // Check warning status
                });

                amountInput.addEventListener('input', checkAmount); // Check on every input change
                amountInput.addEventListener('change', checkAmount); // Check on change (e.g., paste)

                // Optional: Format amount on blur
                amountInput.addEventListener('blur', function() {
                    const value = parseFloat(this.value.replace(/[^\d.-]/g, ''));
                    if (!isNaN(value)) {
                        this.value = value.toFixed(2);
                    } else {
                        // Handle case where input becomes non-numeric after cleaning
                        // this.value = '0.00'; // Or leave it, letting validation catch it
                    }
                     checkAmount(); // Check again after formatting
                });
            }

             // Initial check in case of old() data pre-filling fields on validation failure
             checkAmount();
        });
    </script>
    @endpush
</x-app-layout>