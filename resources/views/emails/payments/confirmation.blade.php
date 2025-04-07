<x-mail::message>
# Payment Received - Thank You!

Dear {{ $payment->contact->name ?? 'Valued Customer' }},

This email confirms we have received your payment on **{{ optional($payment->payment_date)->format('F j, Y') }}**.

**Payment Details:**
*   **Payment Reference:** {{ $payment->payment_number }}
*   **Amount Received:** {{ number_format($payment->amount, 2) }}
*   **Payment Method:** {{ $payment->paymentMethod->name ?? 'N/A' }}
@if($payment->reference)
*   **Your Reference:** {{ $payment->reference }}
@endif

@if($payment->invoice)
**Applied To Invoice:**
*   **Invoice Number:** {{ $payment->invoice->invoice_number }}
*   **Invoice Amount:** {{ number_format($payment->invoice->total, 2) }}
@if(isset($invoiceBalance)) {{-- Check if balance was passed --}}
*   **Remaining Invoice Balance:** {{ number_format($invoiceBalance, 2) }}
@endif
@endif

{{-- Placeholder for Overall Account Balance - requires calculation in Mailable/Controller --}}
{{-- @if(isset($contactBalance) && $contactBalance > 0)

**Your current account balance is {{ number_format($contactBalance, 2) }}.** Please ensure timely payment for any outstanding amounts.
@elseif(isset($contactBalance))

Your account balance is now {{ number_format($contactBalance, 2) }}.
@endif --}}


{{-- Placeholder for Next Due Date - requires calculation in Mailable/Controller --}}
{{-- @if($nextDueDate ?? false)

**Reminder:** Your next installment is due on {{ $nextDueDate->format('F j, Y') }}.
@endif --}}


If you have any questions regarding this payment, please contact us.

{{-- Optional: Link to view payment online --}}
{{-- @if($paymentUrl ?? false)
<x-mail::button :url="$paymentUrl">
View Payment Details
</x-mail::button>
@endif --}}


Thanks,<br>
{{ config('app.name') }}
</x-mail::message>