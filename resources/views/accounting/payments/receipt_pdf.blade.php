<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payment Receipt #{{ $payment->payment_number }}</title>
    <style>
        /* Basic Styling - Reuse/Adapt from invoice PDF styles */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
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
            margin-bottom: 15px;
        }
        th, td {
            padding: 5px 8px; /* Slightly reduced padding for receipt */
            text-align: left;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-lg {
            font-size: 14pt;
        }
        .text-xl {
            font-size: 18pt;
        }
        .mb-4 {
            margin-bottom: 16px;
        }
         .mt-4 {
            margin-top: 16px;
        }
        .border-t {
             border-top: 1px solid #ddd;
         }
         .company-logo {
             max-width: 150px;
             max-height: 75px;
             margin-bottom: 15px;
         }
         .header-section table, .received-from-section table {
             border: none;
         }
         .header-section td, .received-from-section td {
             border: none;
             padding: 2px 0;
         }
         .details-table td {
            border-bottom: 1px solid #eee;
         }
         .details-table td:first-child {
             font-weight: bold;
             color: #555;
             width: 150px; /* Fixed width for labels */
         }
         .footer {
             margin-top: 30px;
             padding-top: 10px;
             border-top: 1px solid #ccc;
             font-size: 8pt;
             text-align: center;
             color: #777;
         }
    </style>
</head>
<body>
    <div class="container">

        {{-- Optional: Company Logo --}}
        {{-- @if(config('accounting.company_logo_path'))
            <img src="{{ public_path(config('accounting.company_logo_path')) }}" alt="{{ config('app.name') }} Logo" class="company-logo">
        @endif --}}

        {{-- Receipt Header Table --}}
        <table class="header-section">
            <tr>
                <td style="width: 60%;">
                    <h1 class="text-xl font-bold">{{ config('app.name', 'Your Company') }}</h1>
                    <p>{{ config('accounting.company_address', '123 Main St, City, Country') }}</p>
                    <p>Phone: {{ config('accounting.company_phone', '555-1234') }}</p>
                    <p>Email: {{ config('accounting.company_email', 'info@example.com') }}</p>
                    {{-- Add Tax ID if needed --}}
                    {{-- <p>Tax ID: {{ config('accounting.company_tax_id', 'Your Tax ID') }}</p> --}}
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right;">
                    <h2 class="text-lg font-bold">PAYMENT RECEIPT</h2>
                    <p><strong>Receipt No:</strong> {{ $payment->payment_number }}</p>
                    <p><strong>Payment Date:</strong> {{ optional($payment->payment_date)->format('Y-m-d') }}</p>
                     <p><strong>Status:</strong> <span style="text-transform: uppercase;">{{ $payment->status }}</span></p> {{-- Usually 'completed' --}}
                </td>
            </tr>
        </table>

        {{-- Received From Section --}}
        <div class="received-from-section mb-4">
            <h3 class="font-bold mb-4">Received From:</h3>
            @if($payment->contact)
                <p>{{ $payment->contact->name }}</p>
                @if($payment->contact->address) <p>{{ $payment->contact->address }}</p> @endif
                @if($payment->contact->city || $payment->contact->state || $payment->contact->postal_code)
                    <p>{{ $payment->contact->city }} {{ $payment->contact->state }} {{ $payment->contact->postal_code }}</p>
                @endif
                @if($payment->contact->country) <p>{{ $payment->contact->country }}</p> @endif
                 @if($payment->contact->email) <p>{{ $payment->contact->email }}</p> @endif
            @else
                <p>N/A</p>
            @endif
        </div>

        {{-- Payment Details Table --}}
        <h3 class="font-bold mb-4">Payment Details:</h3>
        <table class="details-table">
             <tr>
                <td>Amount Received:</td>
                <td class="font-bold">{{ number_format($payment->amount, 2) }}</td> {{-- Make amount prominent --}}
            </tr>
            <tr>
                <td>Payment Method:</td>
                <td>{{ $payment->paymentMethod->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Payment Date:</td>
                <td>{{ optional($payment->payment_date)->format('F j, Y') }}</td>
            </tr>
             @if($payment->reference)
                 <tr>
                    <td>Reference:</td>
                    <td>{{ $payment->reference }}</td>
                </tr>
             @endif
             @if($payment->invoice)
                <tr>
                    <td>Applied to Invoice:</td>
                    <td>#{{ $payment->invoice->invoice_number }}</td>
                </tr>
                 <tr>
                    <td>Invoice Balance After Payment:</td>
                    <td>{{ number_format($payment->invoice->total - $payment->invoice->amount_paid, 2) }}</td>
                </tr>
             @else
                 <tr>
                    <td>Applied to:</td>
                    <td>Account Balance / General Payment</td>
                </tr>
             @endif
             @if($payment->notes)
                 <tr>
                    <td>Notes:</td>
                    <td>{{ $payment->notes }}</td>
                </tr>
             @endif
             <tr>
                <td>Received Into Account:</td>
                 {{-- account relation comes from the Payment model --}}
                <td>{{ $payment->account->name ?? 'N/A' }} {{ $payment->account ? '(' . $payment->account->account_code . ')' : '' }}</td>
            </tr>
        </table>


        {{-- Footer --}}
        <div class="footer mt-8">
            Thank you for your payment!
        </div>

    </div> {{-- End Container --}}
</body>
</html>