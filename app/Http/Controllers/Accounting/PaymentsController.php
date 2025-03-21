<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Contact;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payment::with(['contact', 'invoice', 'paymentMethod'])
            ->orderBy('created_at', 'desc')->get();
        return view('accounting.payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $contacts = Contact::where('is_active', true)->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $bankAccounts = ChartOfAccount::where('is_bank_account', true)
            ->where('is_active', true)
            ->get();
        
        // If invoice_id is provided, pre-select that invoice
        $selectedInvoice = null;
        $contactInvoices = [];
        
        if ($request->has('invoice_id')) {
            $selectedInvoice = Invoice::findOrFail($request->invoice_id);
            $contactInvoices = [$selectedInvoice];
        }
        
        return view('accounting.payments.create', compact(
            'contacts', 
            'paymentMethods', 
            'bankAccounts', 
            'selectedInvoice',
            'contactInvoices'
        ));
    }

    /**
     * Get invoices for a specific contact (AJAX)
     */
    public function getContactInvoices(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:accounting_contacts,id'
        ]);
        
        $invoices = Invoice::where('contact_id', $request->contact_id)
            ->whereIn('status', ['sent', 'partial'])
            ->get();
            
        return response()->json($invoices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:accounting_contacts,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Get school ID if available
        $schoolId = null;
        if (auth()->user()->school) {
            $schoolId = auth()->user()->school->id;
        }

        // Generate payment number
        $paymentNumber = 'PMT-' . date('Ymd') . '-' . Payment::count() + 1;

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the payment
            $payment = Payment::create([
                'school_id' => $schoolId,
                'contact_id' => $request->contact_id,
                'invoice_id' => $request->invoice_id,
                'payment_number' => $paymentNumber,
                'payment_method_id' => $request->payment_method_id,
                'reference' => $request->reference,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'notes' => $request->notes,
                'status' => 'completed', // Default status
                'account_id' => $request->account_id,
                'is_pos_transaction' => false,
                'created_by' => auth()->id(),
            ]);

            // If payment is linked to an invoice, update the invoice
            if ($request->invoice_id) {
                $invoice = Invoice::find($request->invoice_id);
                $invoice->amount_paid += $request->amount;
                
                if ($invoice->amount_paid >= $invoice->total) {
                    $invoice->status = 'paid';
                } else {
                    $invoice->status = 'partial';
                }
                
                $invoice->save();
            }

            // Create the journal entry for this payment
            $this->createJournalForPayment($payment);

            // Check if we need to send a payment confirmation
            if ($request->has('send_confirmation') && $payment->contact->email) {
                // Will implement email functionality later
            }

            DB::commit();

            return redirect()->route('accounting.payments.index')
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error recording payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load(['contact', 'invoice', 'paymentMethod', 'account', 'creator']);
        return view('accounting.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return redirect()->route('accounting.payments.show', $payment)
                ->with('error', 'Only pending payments can be edited.');
        }

        $payment->load(['contact', 'invoice', 'paymentMethod', 'account']);
        
        $contacts = Contact::where('is_active', true)->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $bankAccounts = ChartOfAccount::where('is_bank_account', true)
            ->where('is_active', true)
            ->get();
            
        if ($payment->contact) {
            $contactInvoices = Invoice::where('contact_id', $payment->contact_id)
                ->whereIn('status', ['sent', 'partial'])
                ->get();
        } else {
            $contactInvoices = [];
        }
        
        return view('accounting.payments.edit', compact(
            'payment',
            'contacts', 
            'paymentMethods', 
            'bankAccounts', 
            'contactInvoices'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return redirect()->route('accounting.payments.show', $payment)
                ->with('error', 'Only pending payments can be edited.');
        }

        $request->validate([
            'contact_id' => 'required|exists:accounting_contacts,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // If the payment was previously linked to an invoice, restore the original amount
            if ($payment->invoice_id) {
                $oldInvoice = Invoice::find($payment->invoice_id);
                $oldInvoice->amount_paid -= $payment->amount;
                
                if ($oldInvoice->amount_paid <= 0) {
                    $oldInvoice->amount_paid = 0;
                    $oldInvoice->status = 'sent';
                } elseif ($oldInvoice->amount_paid < $oldInvoice->total) {
                    $oldInvoice->status = 'partial';
                }
                
                $oldInvoice->save();
            }

            // Update the payment
            $payment->update([
                'contact_id' => $request->contact_id,
                'invoice_id' => $request->invoice_id,
                'payment_method_id' => $request->payment_method_id,
                'reference' => $request->reference,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'notes' => $request->notes,
                'status' => $request->status,
                'account_id' => $request->account_id,
            ]);

            // If the payment is now completed and linked to an invoice, update the invoice
            if ($request->status === 'completed' && $request->invoice_id) {
                $invoice = Invoice::find($request->invoice_id);
                $invoice->amount_paid += $request->amount;
                
                if ($invoice->amount_paid >= $invoice->total) {
                    $invoice->status = 'paid';
                } else {
                    $invoice->status = 'partial';
                }
                
                $invoice->save();
            }

            // Update the journal entry if the payment is completed
            if ($request->status === 'completed') {
                $this->createJournalForPayment($payment);
            } else if ($payment->journal) {
                // If payment is not completed, delete any existing journal
                $payment->journal->entries()->delete();
                $payment->journal->delete();
            }

            DB::commit();

            return redirect()->route('accounting.payments.show', $payment)
                ->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        if ($payment->status === 'completed') {
            return redirect()->route('accounting.payments.index')
                ->with('error', 'Completed payments cannot be deleted. Please void the payment instead.');
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // If the payment has a journal, delete it
            if ($payment->journal) {
                $payment->journal->entries()->delete();
                $payment->journal->delete();
            }
            
            // Delete the payment
            $payment->delete();

            DB::commit();

            return redirect()->route('accounting.payments.index')
                ->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('accounting.payments.index')
                ->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }

    /**
     * Print the payment receipt.
     */
    public function receipt(Payment $payment)
    {
        $payment->load(['contact', 'invoice', 'paymentMethod', 'account', 'creator']);
        return view('accounting.payments.receipt', compact('payment'));
    }

    /**
     * Create a journal entry for a payment.
     */
    private function createJournalForPayment(Payment $payment)
    {
        // If there's already a journal for this payment, delete it
        if ($payment->journal) {
            $payment->journal->entries()->delete();
            $payment->journal->delete();
        }

        // Create a new journal entry
        $journal = Journal::create([
            'school_id' => $payment->school_id,
            'reference' => 'Payment #' . $payment->payment_number,
            'journal_date' => $payment->payment_date,
            'description' => 'Payment received from ' . $payment->contact->name,
            'status' => 'posted',
            'created_by' => auth()->id(),
            'document_type' => 'payment',
            'document_id' => $payment->id,
        ]);

        // Debit Bank Account
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $payment->account_id,
            'debit' => $payment->amount,
            'credit' => 0,
            'description' => 'Payment received',
            'contact_id' => $payment->contact_id,
        ]);

        // Credit Accounts Receivable
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => ChartOfAccount::where('account_code', '1100')->first()->id, // Accounts Receivable
            'debit' => 0,
            'credit' => $payment->amount,
            'description' => 'Payment received',
            'contact_id' => $payment->contact_id,
        ]);

        return $journal;
    }
}