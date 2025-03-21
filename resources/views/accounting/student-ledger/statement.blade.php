<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Statement</title>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 12px;
            color: #333;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .info { 
            margin-bottom: 20px; 
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 5px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-right { 
            text-align: right; 
        }
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 10px;
            color: #6b7280;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .negative { color: #ef4444; }
        .positive { color: #10b981; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Financial Statement</h1>
        <p>Generated on: {{ $generatedDate }}</p>
    </div>
    
    <div class="info">
        <h2>Student Information</h2>
        <p><strong>Name:</strong> {{ $student->name }}</p>
        <p><strong>ID:</strong> {{ $student->id }}</p>
        <p><strong>Class:</strong> {{ $student->class->name ?? 'N/A' }}</p>
        <p><strong>Period:</strong> {{ $startDate ?? 'All time' }} to {{ $endDate }}</p>
    </div>
    
    <h2>Transaction History</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Type</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->date }}</td>
                <td>{{ $transaction->reference }}</td>
                <td>{{ $transaction->description }}</td>
                <td>{{ $transaction->type }}</td>
                <td class="text-right {{ $transaction->amount < 0 ? 'positive' : 'negative' }}">{{ number_format(abs($transaction->amount), 2) }}</td>
                <td class="text-right">{{ number_format($transaction->balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>Current Balance:</strong></td>
                <td class="text-right"><strong>{{ number_format(end($transactions)->balance ?? 0, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>This is an official financial statement from {{ config('app.name') }}.</p>
        <p>For any queries, please contact the accounts department.</p>
    </div>
</body>
</html>