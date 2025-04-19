<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Statement for {{ $student->name }}</title>
    {{-- Simplified CSS for PDF rendering --}}
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; color: #333; line-height: 1.3; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 5px 7px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: top; font-size: 9pt; }
        th { background-color: #f2f2f2; font-weight: bold; border-bottom-width: 2px; border-color: #ccc;}
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .header-info { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .student-info { margin-bottom: 20px; }
        tfoot td { font-weight: bold; border-top: 1px solid #aaa; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 8pt; text-align: center; color: #777; }
        /* Basic table styling */
        table.items-table { border: 1px solid #ccc; }
        table.items-table th, table.items-table td { border: 1px solid #eee; }

    </style>
</head>
<body>
    <div> {{-- Main container --}}

        <div class="header-info">
             {{-- Use table for basic layout if needed --}}
             <table>
                 <tr>
                     <td style="width: 60%; border:none; padding: 0;">
                         @if($school)
                             <h1 style="font-size: 16pt; margin:0;">{{ $school->name ?? config('app.name', 'School/Company Name') }}</h1>
                             {{-- Add school address/contact if available in $school object --}}
                             {{-- <p>{{ $school->address ?? '' }}</p> --}}
                         @else
                              <h1 style="font-size: 16pt; margin:0;">{{ config('app.name', 'School/Company Name') }}</h1>
                         @endif
                     </td>
                      <td style="width: 40%; border:none; padding: 0; text-align: right; vertical-align: top;">
                         <h2 style="font-size: 14pt; margin:0;">Student Statement</h2>
                         <p>Generated: {{ $generatedDate ?? now()->format('Y-m-d') }}</p>
                      </td>
                 </tr>
             </table>
        </div>

        <div class="student-info">
            <h3>Student Information</h3>
            <p><strong>Name:</strong> {{ $student->name }}</p>
            <p><strong>Contact ID:</strong> {{ $student->id }}</p>
            {{-- <p><strong>Student ID:</strong> {{ $student->student?->student_number ?? 'N/A' }}</p> --}}
            <p><strong>Period:</strong> {{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</p>
        </div>

        <h3>Transaction History</h3>
        <table class="items-table">
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
                    <td colspan="6" class="text-right font-bold">Starting Balance as of {{ $startDate->format('Y-m-d') }}</td>
                    <td class="text-right font-bold">{{ number_format($startingBalance ?? 0, 2) }}</td>
                </tr>

                {{-- Transactions --}}
                @isset($transactions)
                    @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction['date'] instanceof \Carbon\Carbon ? $transaction['date']->format('Y-m-d') : ($transaction['date'] ?? 'N/A') }}</td>
                        <td>{{ $transaction['type'] ?? 'N/A' }}</td>
                        <td>{{ $transaction['description'] ?? 'N/A' }}</td>
                        <td>{{ $transaction['reference'] ?? '' }}</td>
                        <td class="text-right">{{ isset($transaction['debit']) && $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '' }}</td>
                        <td class="text-right">{{ isset($transaction['credit']) && $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '' }}</td>
                        <td class="text-right">{{ isset($transaction['running_balance']) ? number_format($transaction['running_balance'], 2) : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No transactions found during this period.</td>
                    </tr>
                    @endforelse
                @else
                     <tr>
                         <td colspan="7" class="text-center">Transaction data not available.</td>
                     </tr>
                @endisset
            </tbody>
             @isset($transactions)
                 @if($transactions->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-right"><strong>Closing Balance as of {{ $endDate->format('Y-m-d') }}:</strong></td>
                            <td class="text-right"><strong>{{ number_format($transactions->last()['running_balance'] ?? $startingBalance ?? 0, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                @endif
             @endisset
        </table>

        <div class="footer">
            Thank you!
        </div>

    </div> {{-- End Main container --}}
</body>
</html>