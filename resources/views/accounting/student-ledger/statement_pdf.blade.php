<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Statement for {{ $contact->name }}</title>
    <style>
        /* Basic Styling - Reuse/Adapt from invoice/receipt PDF styles */
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
        .container { width: 100%; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: top; }
        th { background-color: #f8f8f8; font-weight: bold; text-transform: uppercase; font-size: 8pt; color: #555; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .mb-4 { margin-bottom: 16px; }
        .header-section table, .bill-to-section table { border: none; }
        .header-section td, .bill-to-section td { border: none; padding: 2px 0; }
        .items-table thead th { background-color: #eee; }
        .items-table td { font-size: 9pt; }
        .total-row td { font-weight: bold; border-top: 1px solid #aaa; padding-top: 8px; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 8pt; text-align: center; color: #777; }
    </style>
</head>
<body>
    <div class="container">

        {{-- Header Table --}}
        <table class="header-section">
            <tr>
                <td style="width: 60%;">
                    <h1 style="font-size: 16pt; font-weight: bold;">{{ config('app.name', 'Your Company') }}</h1>
                    <p>{{ config('accounting.company_address', '123 Main St, City, Country') }}</p>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right;">
                    <h2 style="font-size: 14pt; font-weight: bold;">Account Statement</h2>
                    <p><strong>Date Issued:</strong> {{ $statementDate->format('Y-m-d') }}</p>
                </td>
            </tr>
        </table>

        {{-- Bill To Section Table --}}
        <table class="bill-to-section mb-4">
             <tr>
                <td style="width: 60%;">{{-- Spacer --}}</td>
                <td style="width: 40%; vertical-align: top;">
                    <strong>To:</strong><br>
                    {{ $contact->name }}<br>
                    @if($contact->student?->student_number) Student ID: {{ $contact->student->student_number }}<br> @endif
                    @if($contact->address) {{ $contact->address }}<br> @endif
                    {{-- Add city/state/etc if needed --}}
                    @if($contact->email) {{ $contact->email }}<br> @endif
                </td>
            </tr>
        </table>


        {{-- Items Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Reference #</th>
                    <th class="text-right">Debit (+)</th>
                    <th class="text-right">Credit (-)</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                 {{-- Opening Balance Row - Placeholder --}}
                <tr>
                    <td>{{ $ledgerItems->first()?->date ? $ledgerItems->first()->date->copy()->subDay()->format('Y-m-d') : 'Start' }}</td>
                    <td colspan="4">Opening Balance</td>
                    <td class="text-right">{{ number_format($openingBalance ?? 0, 2) }}</td>
                </tr>

                @forelse($ledgerItems as $item)
                    <tr>
                        <td>{{ optional($item->date)->format('Y-m-d') }}</td>
                        <td>{{ ucfirst($item->type) }}</td>
                        <td>{{ $item->number }}</td>
                        <td class="text-right">{{ $item->debit > 0 ? number_format($item->debit, 2) : '' }}</td>
                        <td class="text-right">{{ $item->credit > 0 ? number_format($item->credit, 2) : '' }}</td>
                        <td class="text-right">{{ number_format($item->balance, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No transactions found in this period.</td></tr>
                @endforelse
            </tbody>
             <tfoot >
                <tr class="total-row">
                    <td colspan="5" class="text-right font-bold">Closing Balance:</td>
                    <td class="text-right font-bold">{{ number_format($ledgerItems->last()->balance ?? $openingBalance ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Footer --}}
        <div class="footer">
            Please contact us if you have any questions about this statement.
        </div>

    </div> {{-- End Container --}}
</body>
</html>