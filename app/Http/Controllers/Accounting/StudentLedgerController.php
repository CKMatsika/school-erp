<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Accounting\Contact;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentLedgerController extends Controller
{
    /**
     * Display the student ledger overview.
     */
    public function index(Request $request)
    {
        $school_id = Auth::user()?->school_id;
        
        // Get all student contacts
        $students = Contact::where('contact_type', 'student')
                         ->where('is_active', true)
                         ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                         ->orderBy('name')
                         ->get(['id', 'name', 'student_id']);
        
        // Get selected student if any
        $selectedStudentId = $request->query('student_id');
        $selectedStudent = null;
        $transactions = collect();
        
        if ($selectedStudentId) {
            $selectedStudent = Contact::where('id', $selectedStudentId)
                                   ->where('contact_type', 'student')
                                   ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                   ->first();
            
            if ($selectedStudent) {
                // Get invoices
                $invoices = Invoice::where('contact_id', $selectedStudentId)
                                 ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                 ->get();
                
                // Get payments
                $payments = Payment::where('contact_id', $selectedStudentId)
                                 ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                 ->get();
                
                // Get payment plans
                $paymentPlans = PaymentPlan::where('contact_id', $selectedStudentId)
                                         ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                         ->get();
                
                // Merge and transform into transaction records
                foreach ($invoices as $invoice) {
                    $transactions->push([
                        'date' => $invoice->invoice_date,
                        'type' => 'invoice',
                        'description' => "Invoice #{$invoice->invoice_number}: {$invoice->description}",
                        'amount' => $invoice->total,
                        'balance_impact' => $invoice->total,
                        'reference' => $invoice->id,
                        'status' => $invoice->status
                    ]);
                }
                
                foreach ($payments as $payment) {
                    $transactions->push([
                        'date' => $payment->payment_date,
                        'type' => 'payment',
                        'description' => "Payment: {$payment->payment_method} - {$payment->reference}",
                        'amount' => $payment->amount,
                        'balance_impact' => -$payment->amount, // Negative as it reduces balance
                        'reference' => $payment->id,
                        'status' => $payment->status
                    ]);
                }
                
                // Sort by date
                $transactions = $transactions->sortBy('date');
                
                // Calculate running balance
                $runningBalance = 0;
                $transactions = $transactions->map(function ($item) use (&$runningBalance) {
                    $runningBalance += $item['balance_impact'];
                    $item['running_balance'] = $runningBalance;
                    return $item;
                });
            }
        }
        
        return view('accounting.student-ledger.index', compact('students', 'selectedStudent', 'transactions'));
    }
    
    /**
     * Display the transaction details
     */
    public function show($type, $id)
    {
        $school_id = Auth::user()?->school_id;
        
        switch ($type) {
            case 'invoice':
                $transaction = Invoice::where('id', $id)
                                    ->when($school_id, fn($q, $sid) => $q->where('school_id', $sid))
                                    ->with(['items', 'payments'])
                                    ->firstOrFail();
                return view('accounting.student-ledger.invoice-details', compact('transaction'));
                
            case 'payment':
                $transaction = Payment::where('id', $id)
                                    ->when($school_id, fn($q, $sid) => $q->where('school_id', $sid))
                                    ->with(['invoices'])
                                    ->firstOrFail();
                return view('accounting.student-ledger.payment-details', compact('transaction'));
                
            case 'plan':
                $transaction = PaymentPlan::where('id', $id)
                                        ->when($school_id, fn($q, $sid) => $q->where('school_id', $sid))
                                        ->with(['paymentSchedules'])
                                        ->firstOrFail();
                return view('accounting.student-ledger.plan-details', compact('transaction'));
                
            default:
                return redirect()->route('accounting.student-ledger.index')
                                ->with('error', 'Invalid transaction type.');
        }
    }
    
    /**
     * Generate a printable statement
     */
    public function generateStatement(Request $request, $studentId)
    {
        $school_id = Auth::user()?->school_id;
        
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $student = Contact::where('id', $studentId)
                         ->where('contact_type', 'student')
                         ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                         ->firstOrFail();
        
        // Get transactions within date range
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        
        // Get invoices in range
        $invoices = Invoice::where('contact_id', $studentId)
                         ->whereBetween('invoice_date', [$startDate, $endDate])
                         ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                         ->get();
        
        // Get payments in range
        $payments = Payment::where('contact_id', $studentId)
                         ->whereBetween('payment_date', [$startDate, $endDate])
                         ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                         ->get();
        
        // Combine into transactions
        $transactions = collect();
        
        foreach ($invoices as $invoice) {
            $transactions->push([
                'date' => $invoice->invoice_date,
                'type' => 'invoice',
                'description' => "Invoice #{$invoice->invoice_number}: {$invoice->description}",
                'amount' => $invoice->total,
                'balance_impact' => $invoice->total,
            ]);
        }
        
        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date,
                'type' => 'payment',
                'description' => "Payment: {$payment->payment_method} - {$payment->reference}",
                'amount' => $payment->amount,
                'balance_impact' => -$payment->amount,
            ]);
        }
        
        // Sort by date
        $transactions = $transactions->sortBy('date');
        
        // Calculate starting balance (all transactions before start date)
        $startingBalance = 0;
        
        $previousInvoicesTotal = Invoice::where('contact_id', $studentId)
                                      ->where('invoice_date', '<', $startDate)
                                      ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                      ->sum('total');
        
        $previousPaymentsTotal = Payment::where('contact_id', $studentId)
                                       ->where('payment_date', '<', $startDate)
                                       ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                       ->sum('amount');
        
        $startingBalance = $previousInvoicesTotal - $previousPaymentsTotal;
        
        // Calculate running balance
        $runningBalance = $startingBalance;
        $transactions = $transactions->map(function ($item) use (&$runningBalance) {
            $runningBalance += $item['balance_impact'];
            $item['running_balance'] = $runningBalance;
            return $item;
        });
        
        // Get school information for statement header
        $school = null;
        if ($school_id) {
            $school = DB::table('schools')->find($school_id);
        }
        
        return view('accounting.student-ledger.statement', compact(
            'student', 
            'transactions', 
            'startDate', 
            'endDate', 
            'startingBalance', 
            'school'
        ));
    }
}