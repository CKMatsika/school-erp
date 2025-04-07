<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        /* Basic Styling - Adjust as needed */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif; /* Common PDF-safe fonts */
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: top; /* Align content top */
        }
        th {
            background-color: #f8f8f8;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            color: #555;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-lg {
            font-size: 14pt; /* Example */
        }
        .text-xl {
            font-size: 18pt; /* Example */
        }
        .mb-4 {
            margin-bottom: 16px;
        }
         .mb-8 {
            margin-bottom: 32px;
        }
        .mt-4 {
            margin-top: 16px;
        }
        .mt-8 {
             margin-top: 32px;
        }
        .py-2 {
            padding-top: 8px;
            padding-bottom: 8px;
        }
         .border-t {
             border-top: 1px solid #ddd;
         }
         .whitespace-pre-line {
             white-space: pre-line; /* Respect newlines in notes/terms */
         }
         .company-logo {
             max-width: 150px; /* Adjust max width */
             max-height: 75px; /* Adjust max height */
             margin-bottom: 15px;
         }
         .header-section table, .bill-to-section table {
             border: none; /* Remove borders for layout tables */
         }
         .header-section td, .bill-to-section td {
             border: none;
             padding: 2px 0;
         }
         .items-table th, .items-table td {
             border: 1px solid #ddd; /* Add borders to items table */
         }
         .items-table thead th {
              background-color: #eee; /* Slightly darker header for items */
         }
         .summary-section {
             width: 40%; /* Adjust width */
             margin-left: 60%; /* Push to the right */
         }
         .summary-section td {
             border: none;
             padding: 4px 10px;
         }
         .summary-section .total-row td {
             font-weight: bold;
             font-size: 11pt;
             border-top: 2px solid #333;
             padding-top: 8px;
         }
         .footer {
             margin-top: 30px;
             padding-top: 10px;
             border-top: 1px solid #ccc;
             font-size: 8pt;
             text-align: center;
             color: #777;
         }
         .page-break { /* For multi-page invoices if needed */
            page-break-after: always;
         }
    </style>
</head>
<body>
    <div class="container">

        {{-- Optional: Company Logo --}}
        {{-- @if(config('accounting.company_logo_path'))
            <img src="{{ public_path(config('accounting.company_logo_path')) }}" alt="{{ config('app.name') }} Logo" class="company-logo">
        @endif --}}

        {{-- Invoice Header Table --}}
        <table class="header-section">
            <tr>
                <td style="width: 60%;">
                    <h1 class="text-xl font-bold">{{ config('app.name', 'Your Company') }}</h1>
                     {{-- Add your company address/details here if not using logo section --}}
                    <p>{{ config('accounting.company_address', '123 Main St, City, Country') }}</p>
                    <p>Phone: {{ config('accounting.company_phone', '555-1234') }}</p>
                    <p>Email: {{ config('accounting.company_email', 'info@example.com') }}</p>
                    {{-- Add Tax ID if needed --}}
                    {{-- <p>Tax ID: {{ config('accounting.company_tax_id', 'Your Tax ID') }}</p> --}}
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right;">
                    <h2 class="text-lg font-bold">INVOICE</h2>
                    <p><strong>Number:</strong> {{ $invoice->invoice_number }}</p>
                    <p><strong>Date:</strong> {{ optional($invoice->issue_date)->format('Y-m-d') }}</p>
                    <p><strong>Due Date:</strong> {{ optional($invoice->due_date)->format('Y-m-d') }}</p>
                    @if($invoice->reference)
                        <p><strong>Reference:</strong> {{ $invoice->reference }}</p>
                    @endif
                     <p><strong>Status:</strong> <span style="text-transform: uppercase;">{{ $invoice->status }}</span></p>

                </td>
            </tr>
        </table>

        {{-- Bill To Section Table --}}
        <table class="bill-to-section mb-8">
            <tr>
                <td style="width: 60%;">{{-- Spacer or From Address if different --}}</td>
                <td style="width: 40%; vertical-align: top;">
                    <strong>Bill To:</strong><br>
                    @if($invoice->contact)
                        {{ $invoice->contact->name }}<br>
                        @if($invoice->contact->address) {{ $invoice->contact->address }}<br> @endif
                        @if($invoice->contact->city || $invoice->contact->state || $invoice->contact->postal_code)
                            {{ $invoice->contact->city }} {{ $invoice->contact->state }} {{ $invoice->contact->postal_code }}<br>
                        @endif
                        @if($invoice->contact->country) {{ $invoice->contact->country }}<br> @endif
                        @if($invoice->contact->email) {{ $invoice->contact->email }}<br> @endif
                        @if($invoice->contact->phone) {{ $invoice->contact->phone }}<br> @endif
                        @if($invoice->contact->tax_number) Tax/ID: {{ $invoice->contact->tax_number }} @endif
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        </table>


        {{-- Items Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->items && $invoice->items->count() > 0)
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="text-right">{{ number_format($item->quantity, 2) }}</td> {{-- Adjust decimal places if needed --}}
                            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">
                                {{ number_format($item->tax_amount ?? 0, 2) }}
                                @if($item->taxRate) <br><span style="font-size: 8pt;">({{ $item->taxRate->rate + 0 }}%)</span> @endif {{-- +0 forces number display --}}
                            </td>
                            <td class="text-right">{{ number_format($item->subtotal + ($item->tax_amount ?? 0), 2) }}</td> {{-- Show line total including tax --}}
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="5" class="text-center">No items.</td></tr>
                @endif
            </tbody>
        </table>

        {{-- Summary Section --}}
        <table class="summary-section">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ number_format($invoice->subtotal ?? 0, 2) }}</td>
            </tr>
             <tr>
                <td>Total Tax:</td>
                <td class="text-right">{{ number_format($invoice->tax_amount ?? 0, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total:</td>
                <td class="text-right">{{ number_format($invoice->total ?? 0, 2) }}</td>
            </tr>
             @if(isset($invoice->amount_paid) && $invoice->amount_paid > 0)
                 <tr>
                    <td>Amount Paid:</td>
                    <td class="text-right">(-) {{ number_format($invoice->amount_paid, 2) }}</td>
                </tr>
                 <tr class="total-row">
                    <td>Balance Due:</td>
                    <td class="text-right">{{ number_format($invoice->total - $invoice->amount_paid, 2) }}</td>
                </tr>
             @endif
        </table>

        {{-- Notes --}}
        @if($invoice->notes)
            <div class="mt-8">
                <h4 class="font-bold">Notes:</h4>
                <p class="whitespace-pre-line">{{ $invoice->notes }}</p>
            </div>
        @endif

         {{-- Terms --}}
        @if($invoice->terms)
            <div class="mt-8">
                <h4 class="font-bold">Terms & Conditions:</h4>
                 <p class="whitespace-pre-line">{{ $invoice->terms }}</p>
            </div>
        @endif


        {{-- Footer --}}
        <div class="footer">
            Thank you for your business!
            {{-- Add payment instructions or other footer info here --}}
        </div>

    </div> {{-- End Container --}}
</body>
</html>