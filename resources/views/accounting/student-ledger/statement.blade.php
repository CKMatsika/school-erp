<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Statement for {{ $student->name }}</title>
    {{-- Basic Styling for HTML View - PDF requires different considerations --}}
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .header, .footer { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 20px; padding: 10px; border: 1px solid #eee; background-color: #f9f9f9;}
        tfoot td { font-weight: bold; border-top: 2px solid #aaa; }
        /* Add styles for print if needed */
        @media print {
            body { font-size: 10pt; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        @if($school)
            <h1>{{ $school->name ?? config('app.name', 'School/Company Name') }}</h1>
            {{-- Add school address/contact if available in $school object --}}
            {{-- <p>{{ $school->address ?? '' }}</p> --}}
        @else
             <h1>{{ config('app.name', 'School/Company Name') }}</h1>
        @endif
        <h2>Student Financial Statement</h2>
        <p>Generated on: {{ $generatedDate ?? now()->format('Y-m-d') }}</p> {{-- Use $generatedDate passed from controller --}}
    </div>

    <div class="info">
        <h3>Student Information</h3>
        <p><strong>Name:</strong> {{ $student->name }}</p>
        <p><strong>Contact ID:</strong> {{ $student->id }}</p>
        {{-- Add Student ID from student relationship if exists --}}
        {{-- <p><strong>Student ID:</strong> {{ $student->student?->student_number ?? 'N/A' }}</p> --}}
        <p><strong>Period:</strong> {{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</p>
    </div>

    <h3>Transaction History</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Reference</th>
                <th class="text-right">Debit (+)</th>
                <th class="text-right">Credit (-)</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            {{-- Starting Balance Row --}}
            <tr>
                <td colspan="6" class="text-right">Starting Balance as of {{ $startDate->format('Y-m-d') }}</td>
                <td class="text-right">{{ number_format($startingBalance ?? 0, 2) }}</td>
            </tr>

            {{-- Transactions --}}
            @isset($transactions)
                @forelse($transactions as $transaction)
                <tr>
                    {{-- Use array access and check if date is Carbon instance --}}
                    <td>{{ $transaction['date'] instanceof \Carbon\Carbon ? $transaction['date']->format(config('app.date_format', 'Y-m-d')) : ($transaction['date'] ?? 'N/A') }}</td>
                    <td>{{ $transaction['type'] ?? 'N/A' }}</td>
                    <td>{{ $transaction['description'] ?? 'N/A' }}</td>
                    <td>{{ $transaction['reference'] ?? '' }}</td>
                    <td class="text-right">{{ isset($transaction['debit']) && $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '' }}</td>
                    <td class="text-right">{{ isset($transaction['credit']) && $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '' }}</td>
                    {{-- Use running_balance calculated by controller --}}
                    <td class="text-right">{{ isset($transaction['running_balance']) ? number_format($transaction['running_balance'], 2) : 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No transactions found during this period.</td>
                </tr>
                @endforelse
            @else
                 <tr>
                     <td colspan="7" style="text-align: center;">Transaction data not available.</td>
                 </tr>
            @endisset
        </tbody>
        @isset($transactions)
             @if($transactions->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-right"><strong>Closing Balance as of {{ $endDate->format('Y-m-d') }}:</strong></td>
                        {{-- Get balance from the last transaction item --}}
                        <td class="text-right"><strong>{{ number_format($transactions->last()['running_balance'] ?? $startingBalance ?? 0, 2) }}</strong></td>
                    </tr>
                </tfoot>
            @endif
        @endisset
    </table>

    <div class="footer">
        <p>If you have any questions about this statement, please contact the finance office.</p>
    </div>
</body>
</html>