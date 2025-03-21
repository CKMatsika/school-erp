<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentLedgerController extends Controller
{
    public function index()
    {
        $students = Student::all();
        return view('accounting.student-ledger.index', compact('students'));
    }
    
    public function show(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = DB::table(function ($query) use ($studentId, $startDate, $endDate) {
            // Get invoices
            $query->select(
                'id',
                'invoice_number as reference',
                'date',
                'total as amount',
                DB::raw("'Invoice' as type"),
                'description'
            )
            ->from('invoices')
            ->where('student_id', $studentId);
            
            // Union with payments
            $query->union(
                DB::table('payments')
                ->select(
                    'id',
                    'payment_number as reference',
                    'date',
                    DB::raw('amount * -1 as amount'), // Negative for payments
                    DB::raw("'Payment' as type"),
                    'notes as description'
                )
                ->where('student_id', $studentId)
            );
        }, 'transactions');
        
        // Apply date filters if provided
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        
        $transactions = $query->orderBy('date')->get();
        
        // Calculate running balance
        $balance = 0;
        foreach ($transactions as $transaction) {
            $balance += $transaction->amount;
            $transaction->balance = $balance;
        }
        
        return view('accounting.student-ledger.show', compact('student', 'transactions', 'startDate', 'endDate'));
    }
    
    public function generateStatement(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date') ?: date('Y-m-d');
        
        // Similar query as above, but formatted for PDF
        $transactions = $this->getTransactions($studentId, $startDate, $endDate);
        
        // Generate PDF statement
        $pdf = \PDF::loadView('accounting.student-ledger.statement', [
            'student' => $student,
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedDate' => date('Y-m-d'),
        ]);
        
        return $pdf->download("statement-{$student->id}-{$endDate}.pdf");
    }
    
    private function getTransactions($studentId, $startDate, $endDate)
    {
        // Implementation of transaction retrieval for statement
        // Similar to show method but formatted for statement
        // ...
    }
}