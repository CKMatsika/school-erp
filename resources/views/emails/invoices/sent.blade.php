<x-mail::message>
# Invoice {{ $invoice->invoice_number }}

Dear {{ $invoice->contact->name ?? 'Customer' }},

Please find attached your invoice ({{ $invoice->invoice_number }}) for the amount of **{{ number_format($invoice->total, 2) }}**.

The due date for this invoice is {{ optional($invoice->due_date)->format('M d, Y') }}.

@if($invoiceUrl ?? false)
<x-mail::button :url="$invoiceUrl">
View Invoice Online
</x-mail::button>
@endif

Thank you for your business.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>