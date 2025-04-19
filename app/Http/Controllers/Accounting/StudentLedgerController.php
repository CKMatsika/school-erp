<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Contact; // Use this for students
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentPlan; // Keep if used
use App\Models\School; // For school info on statement
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon; // For date handling
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse; // Aliased
// Required facade if using PDF generation helper
use Barryvdh\DomPDF\Facade\Pdf;


class StudentLedgerController extends Controller
{
    /**
     * Display the student ledger overview.
     */
    public function index(Request $request): View
    {
        $school_id = Auth::user()?->school_id;
        $hasContactSchoolId = Schema::hasColumn('contacts', 'school_id');
        $hasInvoiceSchoolId = Schema::hasColumn('invoices', 'school_id');
        $hasPaymentSchoolId = Schema::hasColumn('payments', 'school_id');
        $hasPlanSchoolId = Schema::hasColumn('payment_plans', 'school_id');

        // Get all active student contacts for the school
        $students = Contact::where('contact_type', 'student') // Filter by type
                         ->where('is_active', true)
                         ->when($school_id && $hasContactSchoolId, fn($q) => $q->where('school_id', $school_id))
                         ->orderBy('name')
                         ->get(['id', 'name']); // Select only needed fields

        $selectedStudentId = $request->query('student_id');
        $selectedStudent = null;
        $transactions = collect();
        $currentBalance = 0;

        if ($selectedStudentId) {
            $selectedStudent = Contact::where('id', $selectedStudentId)
                                   ->where('contact_type', 'student')
                                   ->when($school_id && $hasContactSchoolId, fn($q) => $q->where('school_id', $school_id))
                                   ->first();

            if ($selectedStudent) {
                // Fetch Invoices
                $invoices = Invoice::where('contact_id', $selectedStudentId)
                                 ->when($school_id && $hasInvoiceSchoolId, fn($q) => $q->where('school_id', $school_id))
                                 ->whereNotIn('status', ['draft', 'void'])
                                 ->orderBy('issue_date', 'asc')->orderBy('id', 'asc')
                                 ->get();

                // Fetch Payments
                $payments = Payment::with('paymentMethod')
                                 ->where('contact_id', $selectedStudentId)
                                 ->when($school_id && $hasPaymentSchoolId, fn($q) => $q->where('school_id', $school_id))
                                 ->where('status', 'completed')
                                 ->orderBy('payment_date', 'asc')->orderBy('id', 'asc')
                                 ->get();

                // Transform to Transactions
                foreach ($invoices as $invoice) {
                    $transactions->push([
                        'date' => $invoice->issue_date,
                        'sort_key' => $invoice->issue_date->timestamp . '-I' . str_pad($invoice->id, 10, '0', STR_PAD_LEFT),
                        'type' => 'invoice',
                        'description' => "Invoice #{$invoice->invoice_number}" . ($invoice->reference ? " (Ref: {$invoice->reference})" : ""),
                        'amount' => $invoice->total, 'debit' => $invoice->total, 'credit' => 0,
                        'balance_impact' => (float) $invoice->total, 'reference_id' => $invoice->id, 'status' => $invoice->status
                    ]);
                }
                foreach ($payments as $payment) {
                    $transactions->push([
                        'date' => $payment->payment_date,
                        'sort_key' => $payment->payment_date->timestamp . '-P' . str_pad($payment->id, 10, '0', STR_PAD_LEFT),
                        'type' => 'payment',
                        'description' => "Payment Received (#{$payment->payment_number})" . ($payment->paymentMethod ? " via {$payment->paymentMethod->name}" : '') . ($payment->reference ? " (Ref: {$payment->reference})" : ""),
                        'amount' => $payment->amount, 'debit' => 0, 'credit' => $payment->amount,
                        'balance_impact' => - (float) $payment->amount, 'reference_id' => $payment->id, 'status' => $payment->status
                    ]);
                }

                // Sort and Calculate Running Balance
                $transactions = $transactions->sortBy('sort_key');
                $runningBalance = 0;
                $transactions = $transactions->map(function ($item) use (&$runningBalance) {
                    $runningBalance += $item['balance_impact'];
                    $item['running_balance'] = $runningBalance;
                    return $item;
                });
                $currentBalance = $runningBalance;
            }
        }

        // Pass all required variables to the view
        return view('accounting.student-ledger.index', compact(
            'students',
            'selectedStudent',
            'transactions',
            'currentBalance',
            'selectedStudentId'
        ));
    } // End of index method


    /**
     * Display the transaction details (Show Invoice or Payment)
     */
    public function show($type, $id): View | RedirectResponse
    {
        $school_id = Auth::user()?->school_id;
        $hasInvoiceSchoolId = Schema::hasColumn('invoices', 'school_id');
        $hasPaymentSchoolId = Schema::hasColumn('payments', 'school_id');
        $hasPlanSchoolId = Schema::hasColumn('payment_plans', 'school_id');

        try {
            switch (strtolower($type)) {
                case 'invoice':
                    $transaction = Invoice::where('id', $id)
                                        ->when($school_id && $hasInvoiceSchoolId, fn($q) => $q->where('school_id', $school_id))
                                        ->with(['items.account', 'items.taxRate', 'contact', 'payments', 'journal.entries.account'])
                                        ->firstOrFail();
                    break;

                case 'payment':
                    $transaction = Payment::where('id', $id)
                                        ->when($school_id && $hasPaymentSchoolId, fn($q) => $q->where('school_id', $school_id))
                                        ->with(['contact', 'invoice', 'paymentMethod', 'account', 'journal.entries.account'])
                                        ->firstOrFail();
                    break;

                case 'plan':
                    $transaction = PaymentPlan::where('id', $id)
                                        ->when($school_id && $hasPlanSchoolId, fn($q) => $q->where('school_id', $school_id))
                                        ->with(['paymentSchedules', 'contact'])
                                        ->firstOrFail();
                     break;

                default:
                    Log::warning("Invalid transaction type '{$type}' requested for ledger details.");
                    return redirect()->route('accounting.student-ledger.index')
                                    ->with('error', 'Invalid transaction type requested.');
            }
            return view('accounting.student-ledger.details', ['transaction' => $transaction, 'type' => strtolower($type)]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             Log::error("Transaction not found for ledger details.", ['type' => $type, 'id' => $id, 'school_id' => $school_id]);
             return redirect()->route('accounting.student-ledger.index')
                            ->with('error', 'Transaction not found or you do not have permission to view it.');
        }
    }

    /**
     * Generate a printable statement PDF (or HTML view)
     */
    public function generateStatement(Request $request, $studentContactId): View | HttpResponse | RedirectResponse
    {
        $school_id = Auth::user()?->school_id;
        $hasContactSchoolId = Schema::hasColumn('contacts', 'school_id');
        $hasInvoiceSchoolId = Schema::hasColumn('invoices', 'school_id');
        $hasPaymentSchoolId = Schema::hasColumn('payments', 'school_id');

        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'output' => 'nullable|in:html,pdf'
        ]);

        try {
            $student = Contact::where('id', $studentContactId)
                             ->where('contact_type', 'student')
                             ->when($school_id && $hasContactSchoolId, fn($q) => $q->where('school_id', $school_id))
                             ->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             Log::error("Student contact not found for statement generation.", ['contact_id' => $studentContactId, 'school_id' => $school_id]);
             return redirect()->route('accounting.student-ledger.index')
                             ->with('error', 'Student not found or you do not have permission to view their records.');
        }

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();
        $outputType = $validated['output'] ?? 'html';

        // --- Calculate Starting Balance ---
        $previousInvoicesTotal = (float) Invoice::where('contact_id', $studentContactId)
                                        ->where('issue_date', '<', $startDate)
                                        ->whereNotIn('status', ['draft', 'void'])
                                        ->when($school_id && $hasInvoiceSchoolId, fn($q) => $q->where('school_id', $school_id))
                                        ->sum('total');
        $previousPaymentsTotal = (float) Payment::where('contact_id', $studentContactId)
                                        ->where('payment_date', '<', $startDate)
                                        ->where('status', 'completed')
                                        ->when($school_id && $hasPaymentSchoolId, fn($q) => $q->where('school_id', $school_id))
                                        ->sum('amount');
        $startingBalance = $previousInvoicesTotal - $previousPaymentsTotal;

        // --- Get Transactions Within Date Range ---
        $invoices = Invoice::where('contact_id', $studentContactId)
                         ->whereBetween('issue_date', [$startDate, $endDate])
                         ->whereNotIn('status', ['draft', 'void'])
                         ->when($school_id && $hasInvoiceSchoolId, fn($q) => $q->where('school_id', $school_id))
                         ->orderBy('issue_date', 'asc')->orderBy('id', 'asc')
                         ->get();
        $payments = Payment::with('paymentMethod')
                         ->where('contact_id', $studentContactId)
                         ->whereBetween('payment_date', [$startDate, $endDate])
                         ->where('status', 'completed')
                         ->when($school_id && $hasPaymentSchoolId, fn($q) => $q->where('school_id', $school_id))
                         ->orderBy('payment_date', 'asc')->orderBy('id', 'asc')
                         ->get();

        // --- Combine into transactions ---
        $transactions = collect();
        foreach ($invoices as $invoice) {
            $transactions->push([
                'date' => $invoice->issue_date,
                'sort_key' => $invoice->issue_date->timestamp . '-I' . str_pad($invoice->id, 10, '0', STR_PAD_LEFT),
                'type' => 'Invoice', 'description' => "Invoice #{$invoice->invoice_number}",
                'reference' => $invoice->reference, 'debit' => $invoice->total, 'credit' => 0,
                'balance_impact' => (float) $invoice->total,
            ]);
        }
        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date,
                'sort_key' => $payment->payment_date->timestamp . '-P' . str_pad($payment->id, 10, '0', STR_PAD_LEFT),
                'type' => 'Payment', 'description' => "Payment Received" . ($payment->paymentMethod ? " via {$payment->paymentMethod->name}" : ''),
                'reference' => $payment->reference, 'debit' => 0, 'credit' => $payment->amount,
                'balance_impact' => - (float) $payment->amount,
            ]);
        }

        // Sort and Calculate Running Balance
        $transactions = $transactions->sortBy('sort_key');
        $runningBalance = $startingBalance;
        $transactions = $transactions->map(function ($item) use (&$runningBalance) {
            $runningBalance += $item['balance_impact'];
            $item['running_balance'] = $runningBalance;
            return $item;
        });

        // Get school info
        $school = $school_id ? School::find($school_id) : null;

        // *** DEFINE generatedDate HERE ***
        $generatedDate = now()->format(config('app.date_format', 'Y-m-d')); // Use app's date format config

        // Prepare data for the view/PDF
        $data = compact(
            'student',
            'transactions',
            'startDate',
            'endDate',
            'startingBalance',
            'school',
            'generatedDate' // *** ADD generatedDate TO compact() ***
        );

        // --- Render Output ---
        if ($outputType === 'pdf') {
            try {
                // Ensure view exists: resources/views/accounting/student-ledger/statement_pdf.blade.php
                $pdf = Pdf::loadView('accounting.student-ledger.statement_pdf', $data);
                $filename = 'statement-' . $student->id . '-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.pdf';
                return $pdf->download($filename);
            } catch (\Exception $e) {
                 Log::error("Failed to generate PDF statement for student {$student->id}: " . $e->getMessage(), ['exception' => $e]);
                 // Redirect back to the ledger for that student
                 return redirect()->route('accounting.student-ledger.index', ['student_id' => $student->id])
                                ->with('error', 'Could not generate PDF statement. Please ensure the PDF template view exists and check logs.');
            }
        } else {
            // Ensure view exists: resources/views/accounting/student-ledger/statement.blade.php
            return view('accounting.student-ledger.statement', $data);
        }
    } // End of generateStatement method

} // End of class