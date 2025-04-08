<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
             <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Payment Plan') }}: {{ $paymentPlan->name }}
             </h2>
             <a href="{{ route('accounting.payment-plans.show', $paymentPlan) }}" class="text-sm ...">Back to Plan</a>
         </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('components.form-errors')

                    <form method="POST" action="{{ route('accounting.payment-plans.update', $paymentPlan) }}" id="payment-plan-form">
                        @csrf
                        @method('PUT')

                         <p class="text-sm text-yellow-700 bg-yellow-100 p-3 rounded mb-4">Note: Changing amount, installments, start date, or interval for an active plan is generally discouraged and may require manual adjustments or plan cancellation.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Contact (Student) - Usually not changeable after creation --}}
                            <div>
                                <x-input-label :value="__('Student')" />
                                <p class="mt-1 block w-full text-sm text-gray-600 p-2 border rounded bg-gray-50">
                                    {{ $paymentPlan->contact->name ?? 'N/A' }}
                                </p>
                                <input type="hidden" name="contact_id" value="{{ $paymentPlan->contact_id }}">
                            </div>

                            {{-- Invoice - Usually not changeable --}}
                            <div>
                                <x-input-label :value="__('Applied to Invoice')" />
                                <p class="mt-1 block w-full text-sm text-gray-600 p-2 border rounded bg-gray-50">
                                     #{{ $paymentPlan->invoice->invoice_number ?? 'N/A' }}
                                </p>
                                <input type="hidden" name="invoice_id" value="{{ $paymentPlan->invoice_id }}">
                            </div>

                             {{-- Plan Name --}}
                            <div class="md:col-span-2">
                                <x-input-label for="name" :value="__('Plan Name / Description *')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $paymentPlan->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                             {{-- Total Amount --}}
                            <div>
                                <x-input-label for="total_amount" :value="__('Total Amount for Plan *')" />
                                <x-text-input id="total_amount" name="total_amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('total_amount', number_format($paymentPlan->total_amount, 2, '.', ''))" required {{ $paymentPlan->status != 'draft' ? 'readonly' : '' }} />
                                <x-input-error :messages="$errors->get('total_amount')" class="mt-2" />
                                @if($paymentPlan->status != 'draft') <p class="text-xs text-gray-500 mt-1">Cannot change amount on active plan.</p> @endif
                            </div>

                             {{-- Number of Installments --}}
                             <div>
                                <x-input-label for="number_of_installments" :value="__('Number of Installments *')" />
                                <x-text-input id="number_of_installments" name="number_of_installments" type="number" min="2" step="1" class="mt-1 block w-full" :value="old('number_of_installments', $paymentPlan->number_of_installments)" required {{ $paymentPlan->status != 'draft' ? 'readonly' : '' }}/>
                                <x-input-error :messages="$errors->get('number_of_installments')" class="mt-2" />
                                 @if($paymentPlan->status != 'draft') <p class="text-xs text-gray-500 mt-1">Cannot change installments on active plan.</p> @endif
                            </div>

                             {{-- Start Date --}}
                             <div>
                                <x-input-label for="start_date" :value="__('First Installment Due Date *')" />
                                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', optional($paymentPlan->start_date)->format('Y-m-d'))" required {{ $paymentPlan->status != 'draft' ? 'readonly' : '' }}/>
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                 @if($paymentPlan->status != 'draft') <p class="text-xs text-gray-500 mt-1">Cannot change start date on active plan.</p> @endif
                            </div>

                             {{-- Interval --}}
                             <div>
                                <x-input-label for="interval" :value="__('Installment Interval *')" />
                                <select id="interval" name="interval" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm..." {{ $paymentPlan->status != 'draft' ? 'disabled' : '' }}>
                                     <option value="monthly" {{ old('interval', $paymentPlan->interval) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                     <option value="weekly" {{ old('interval', $paymentPlan->interval) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                     <option value="biweekly" {{ old('interval', $paymentPlan->interval) == 'biweekly' ? 'selected' : '' }}>Bi-Weekly (Every 2 Weeks)</option>
                                     <option value="quarterly" {{ old('interval', $paymentPlan->interval) == 'quarterly' ? 'selected' : '' }}>Quarterly (Every 3 Months)</option>
                                     <option value="daily" {{ old('interval', $paymentPlan->interval) == 'daily' ? 'selected' : '' }}>Daily</option>
                                </select>
                                <x-input-error :messages="$errors->get('interval')" class="mt-2" />
                                 @if($paymentPlan->status != 'draft') <p class="text-xs text-gray-500 mt-1">Cannot change interval on active plan.</p> @endif
                            </div>

                             {{-- Status (Allow modification?) --}}
                              <div>
                                <x-input-label for="status" :value="__('Plan Status *')" />
                                <select id="status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm...">
                                    <option value="draft" {{ old('status', $paymentPlan->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="active" {{ old('status', $paymentPlan->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ old('status', $paymentPlan->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $paymentPlan->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                              {{-- Notes --}}
                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full ...">{{ old('notes', $paymentPlan->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>

                        </div>
                         <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('accounting.payment-plans.show', $paymentPlan) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <x-primary-button>Update Plan</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>