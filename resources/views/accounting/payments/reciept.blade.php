<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt #{{ $payment->receipt_number ?? $payment->payment_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .school-logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .receipt-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .receipt-number {
            font-size: 16px;
            margin-bottom: 20px;
            color: #666;
        }
        .receipt-date {
            text-align: right;
            margin-bottom: 20px;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-box {
            width: 48%;
        }
        .info-box h3 {
            margin-top: 0;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .details-section {
            margin-bottom: 30px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table th, .details-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .details-table th {
            background-color: #f8f8f8;
        }
        .amount-section {
            text-align: right;
            margin-bottom: 30px;
        }
        .total-amount {
            font-size: 20px;
            font-weight: bold;
        }
        .notes-section {
            margin-bottom: 30px;
            border: 1px solid #eee;
            padding: 10px;
            background-color: #fafafa;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-box {
            width: 40%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body {
                font-size: 12pt;
            }
            .container {
                border: none;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Button (hidden when printing) -->
        <div class="no-print" style="text-align: right; margin-bottom: 20px;">
            <button onclick="window.print();" style="padding: 8px 16px; background-color: #4f46e5; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Print Receipt
            </button>
            <button onclick="window.close();" style="padding: 8px 16px; background-color: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                Close
            </button>
        </div>
        
        <!-- Receipt Header -->
        <div class="receipt-header">
            @if(config('app.logo'))
                <img src="{{ config('app.logo') }}" alt="School Logo" class="school-logo">
            @endif
            <h1 class="receipt-title">Payment Receipt</h1>
            <div class="receipt-number">Receipt #: {{ $payment->receipt_number ?? $payment->payment_number }}</div>
        </div>
        
        <div class="receipt-date">
            <strong>Date:</strong> {{ $payment->payment_date->format('F d, Y') }}
        </div>
        
        <!-- Info Section -->
        <div class="info-section">
            <div class="info-box">
                <h3>Received From</h3>
                <p>
                    <strong>{{ $payment->contact->name }}</strong><br>
                    @if($payment->contact->company_name)
                        {{ $payment->contact->company_name }}<br>
                    @endif
                    @if($payment->contact->address)
                        {{ $payment->contact->address }}<br>
                    @endif
                    @if($payment->contact->phone)
                        Phone: {{ $payment->contact->phone }}<br>
                    @endif
                    @if($payment->contact->email)
                        Email: {{ $payment->contact->email }}
                    @endif
                </p>
            </div>
            
            <div class="info-box">
                <h3>Received By</h3>
                <p>
                    <strong>{{ config('app.name') }}</strong><br>
                    @if(config('accounting.company_address'))
                        {{ config('accounting.company_address') }}<br>
                    @endif
                    @if(config('accounting.company_phone'))
                        Phone: {{ config('accounting.company_phone') }}<br>
                    @endif
                    @if(config('accounting.company_email'))
                        Email: {{ config('accounting.company_email') }}
                    @endif
                </p>
            </div>
        </div>
        
        <!-- Payment Details -->
        <div class="details-section">
            <h3>Payment Details</h3>
            <table class="details-table">
                <tr>
                    <th>Description</th>
                    <th>Reference</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>
                        @if($payment->invoice)
                            Payment for Invoice #{{ $payment->invoice->invoice_number }}
                        @else
                            General Payment
                        @endif
                    </td>
                    <td>{{ $payment->reference ?? 'N/A' }}</td>
                    <td>{{ $payment->paymentMethod->name }}</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Amount Section -->
        <div class="amount-section">
            <div class="total-amount">
                <strong>Total Amount Paid:</strong> {{ number_format($payment->amount, 2) }}
            </div>
            
            @if($payment->invoice)
                <div style="margin-top: 10px;">
                    <strong>Invoice Balance:</strong> 
                    {{ number_format(max(0, $payment->invoice->total - $payment->invoice->amount_paid), 2) }}
                </div>
            @endif
        </div>
        
        <!-- Notes Section -->
        @if($payment->notes)
            <div class="notes-section">
                <h3>Notes:</h3>
                <p>{{ $payment->notes }}</p>
            </div>
        @endif
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Received By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Customer Signature</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your payment!</p>
            @if($payment->created_at)
                <p>This receipt was generated on {{ $payment->created_at->format('F d, Y \a\t h:i A') }}</p>
            @endif
        </div>
    </div>
</body>
</html>