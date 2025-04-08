<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Payment Plan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('components.flash-messages')
                    @include('components.form-errors')

                    <form method="POST" action="{{ route('accounting.payment-plans.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Student/Contact Selection -->
                            <div>
                                <x-input-label for="contact_id" :value="__('Student')" />
                                <select id="contact_id" name="contact_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">{{ __('Select Student') }}</option>
                                    @foreach($studentContacts as $contact)
                                        <option value="{{ $contact->id }}" {{ old('contact_id', $selectedContactId) == $contact->id ? 'selected' : '' }}>
                                            {{ $contact->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Invoice Selection -->
                            <div>
                                <x-input-label for="invoice_id" :value="__('Invoice')" />
                                <select id="invoice_id" name="invoice_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">{{ __('Select Invoice') }}</option>
                                    @foreach($invoices as $invoice)
                                        <option value="{{ $invoice->id }}" {{ old('invoice_id', $selectedInvoiceId) == $invoice->id ? 'selected' : '' }}
                                                data-contact="{{ $invoice->contact_id }}" 
                                                data-balance="{{ number_format($invoice->total - $invoice->amount_paid, 2) }}">
                                            #{{ $invoice->invoice_number }} - ${{ number_format($invoice->total - $invoice->amount_paid, 2) }} {{ __('due') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Plan Name -->
                            <div>
                                <x-input-label for="name" :value="__('Plan Name')" />
                                <x-text-input id="name" type="text" name="name" :value="old('name')" class="mt-1 block w-full" required />
                            </div>

                            <!-- Total Amount -->
                            <div>
                                <x-input-label for="total_amount" :value="__('Total Amount')" />
                                <x-text-input id="total_amount" type="number" name="total_amount" :value="old('total_amount')" step="0.01" min="0.01" class="mt-1 block w-full" required />
                            </div>

                            <!-- Number of Installments -->
                            <div>
                                <x-input-label for="number_of_installments" :value="__('Number of Installments')" />
                                <x-text-input id="number_of_installments" type="number" name="number_of_installments" :value="old('number_of_installments', 2)" min="2" class="mt-1 block w-full" required />
                            </div>

                            <!-- Start Date -->
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date')" />
                                <x-text-input id="start_date" type="date" name="start_date" :value="old('start_date', date('Y-m-d'))" class="mt-1 block w-full" required />
                            </div>

                            <!-- Interval -->
                            <div>
                                <x-input-label for="interval" :value="__('Payment Interval')" />
                                <select id="interval" name="interval" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="daily" {{ old('interval') == 'daily' ? 'selected' : '' }}>{{ __('Daily') }}</option>
                                    <option value="weekly" {{ old('interval') == 'weekly' ? 'selected' : '' }}>{{ __('Weekly') }}</option>
                                    <option value="biweekly" {{ old('interval', 'biweekly') == 'biweekly' ? 'selected' : '' }}>{{ __('Bi-Weekly') }}</option>
                                    <option value="monthly" {{ old('interval') == 'monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                                    <option value="quarterly" {{ old('interval') == 'quarterly' ? 'selected' : '' }}>{{ __('Quarterly') }}</option>
                                </select>
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Notes')" />
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('accounting.payment-plans.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Create Payment Plan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter invoices based on selected contact
            const contactSelect = document.getElementById('contact_id');
            const invoiceSelect = document.getElementById('invoice_id');
            const totalAmountInput = document.getElementById('total_amount');
            
            // Filter invoices when contact changes
            contactSelect.addEventListener('change', function() {
                const selectedContactId = this.value;
                
                // Reset invoice selection
                invoiceSelect.selectedIndex = 0;
                
                // Hide all options first
                Array.from(invoiceSelect.options).forEach(option => {
                    if (option.value === '') return; // Skip the placeholder option
                    
                    const invoiceContactId = option.getAttribute('data-contact');
                    if (invoiceContactId == selectedContactId) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
            });
            
            // Set total amount when invoice changes
            invoiceSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const balance = selectedOption.getAttribute('data-balance');
                    totalAmountInput.value = balance;
                }
            });
            
            // Trigger initial filtering if contact is pre-selected
            if (contactSelect.value) {
                contactSelect.dispatchEvent(new Event('change'));
                
                // If invoice is also pre-selected, set the total amount
                if (invoiceSelect.value) {
                    invoiceSelect.dispatchEvent(new Event('change'));
                }
            }
        });
    </script>
    @endpush
</x-app-layout>