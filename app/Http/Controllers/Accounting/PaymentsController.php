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
use App\Models\Accounting\AccountType; // Needed for AR account helper
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;   // For logging errors
use Illuminate\Support\Facades\Mail;  // For optional email
use App\Mail\PaymentConfirmationMail; // Assuming you created this
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;     // For PDF Generation

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        $payments = Payment::with(['contact', 'invoice', 'paymentMethod'])
            ->when($school_id, function ($query, $school_id) {
                return $query->where('school_id', $school_id);
            })
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('accounting.payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $school_id = Auth::user()?->school_id;

        $contacts = Contact::where('is_active', true)
            ->when($school_id, function ($query, $school_id) {
                return $query->where('school_id', $school_id);
            })
            ->orderBy('name')->get();

        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('name')->get();

        $bankAccounts = ChartOfAccount::where('is_bank_account', true)
            ->where('is_active', true)
            ->when($school_id, function ($query, $school_id) {
                // Assuming bank accounts might be linked to school? If not, remove when()
                return $query->where('school_id', $school_id);
            })
            ->orderBy('name')->get();

        $selectedInvoice = null;
        $contactInvoices = collect();

        if ($request->has('invoice_id')) {
            $selectedInvoice = Invoice::with('contact')
                                ->when($school_id, function ($query, $school_id) {
                                    return $query->where('school_id', $school_id);
                                })
                                ->find($request->invoice_id);

            if ($selectedInvoice?->contact_id) {
                 $contactInvoices = Invoice::where('contact_id', $selectedInvoice->contact_id)
                                        ->whereIn('status', ['sent', 'partial', 'overdue'])
                                        ->when($school_id, function ($query, $school_id) {
                                            return $query->where('school_id', $school_id);
                                        })
                                        ->orderBy('due_date')
                                        ->get();
            }
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
     * Get outstanding invoices for a specific contact (AJAX)
     */
    public function getContactInvoices(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|integer|exists:accounting_contacts,id'
        ]);
        $school_id = Auth::user()?->school_id;
        $contactId = $validated['contact_id'];

        if ($school_id) {
            $contactExists = Contact::where('id', $contactId)->where('school_id', $school_id)->exists();
            if (!$contactExists) return response()->json(['error' => 'Contact not found or access denied.'], 403);
        }

        $invoices = Invoice::where('contact_id', $contactId)
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
            ->select('id', 'invoice_number', 'total', 'amount_paid', 'due_date')
            ->orderBy('due_date')
            ->get()
            ->map(function($invoice) {
                $invoice->balance_due = max(0, $invoice->total - $invoice->amount_paid);
                return $invoice;
            });

        return response()->json($invoices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'contact_id' => 'required|exists:accounting_contacts,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'send_confirmation' => 'nullable|boolean',
        ]);

        $schoolId = Auth::user()?->school_id;

        $bankAccount = ChartOfAccount::where('id', $validatedData['account_id'])
                                     ->where('is_bank_account', true)
                                     // ->when($schoolId, fn($q, $id) => $q->where('school_id', $id)) // CoA might be global
                                     ->first();
        if (!$bankAccount) return redirect()->back()->withInput()->with('error', 'Invalid deposit account selected.');

        if ($validatedData['invoice_id']) {
             $invoiceValid = Invoice::where('id', $validatedData['invoice_id'])
                                    ->where('contact_id', $validatedData['contact_id'])
                                    ->when($schoolId, fn($q, $id) => $q->where('school_id', $id))
                                    ->exists();
             if (!$invoiceValid) return redirect()->back()->withInput()->with('error', 'Selected invoice does not match the selected contact.');
        }

        $prefix = 'PMT-' . date('Ymd') . '-';
        $lastPaymentNum = Payment::where('payment_number', 'LIKE', $prefix . '%')->when($schoolId, fn($q, $id) => $q->where('school_id', $id))->orderBy('payment_number', 'desc')->value('payment_number');
        $nextNum = $lastPaymentNum ? (intval(substr($lastPaymentNum, -4)) + 1) : 1;
        $paymentNumber = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $paymentData = $validatedData;
            $paymentData['school_id'] = $schoolId;
            $paymentData['payment_number'] = $paymentNumber;
            $paymentData['status'] = 'completed';
            $paymentData['is_pos_transaction'] = false;
            $paymentData['created_by'] = auth()->id();

            $payment = Payment::create($paymentData);

            if ($payment->invoice_id) {
                $invoice = Invoice::find($payment->invoice_id);
                if ($invoice) {
                    $invoice->amount_paid = ($invoice->amount_paid ?? 0) + $payment->amount;
                    $invoice->status = (($invoice->total - $invoice->amount_paid) < 0.001) ? 'paid' : 'partial';
                    $invoice->save();
                } else {
                     throw new \Exception("Invoice with ID {$payment->invoice_id} not found.");
                }
            }

            // Create Journal Entry using the CORRECTED helper below
            $this->createJournalForPayment($payment);

            DB::commit();

            $emailSent = false;
            if ($request->boolean('send_confirmation') && $payment->contact?->email) {
                 try {
                    Mail::to($payment->contact->email)->send(new PaymentConfirmationMail($payment));
                    $emailSent = true;
                } catch (\Exception $mailException) {
                    Log::error("Failed to send payment confirmation email for Payment ID {$payment->id}: " . $mailException->getMessage());
                }
            }

            // Redirect to the new download receipt route instead of the index
            return redirect()->route('accounting.payments.download-receipt', $payment)
                ->with('success', 'Payment recorded successfully.' . ($emailSent ? ' Confirmation email sent.' : ''))
                ->with('warning_email', !$emailSent && $request->boolean('send_confirmation') ? 'Failed to send confirmation email.' : null);


        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error recording payment: " . $e->getMessage() . "\n" . $e->getTraceAsString());
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
        // $this->authorizeSchoolAccess($payment); // Implement if needed
        $payment->load(['contact', 'invoice', 'paymentMethod', 'account', 'creator', 'journal.entries']);
        return view('accounting.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment); // Implement if needed
        if ($payment->status !== 'pending') {
            return redirect()->route('accounting.payments.show', $payment)
                ->with('error', 'Only pending payments can be edited.');
        }
        $payment->load(['contact', 'invoice', 'paymentMethod', 'account']);
        $school_id = Auth::user()?->school_id;
        $contacts = Contact::where('is_active', true)->when($school_id, fn($q, $id) => $q->where('school_id', $id))->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();
        $bankAccounts = ChartOfAccount::where('is_bank_account', true)->where('is_active', true)->when($school_id, fn($q, $id) => $q->where('school_id', $id))->orderBy('name')->get();
        $contactInvoices = collect();
        if ($payment->contact_id) {
            $contactInvoices = Invoice::where('contact_id', $payment->contact_id)
                ->where(function($query) use ($payment) {
                     $query->whereIn('status', ['sent', 'partial', 'overdue'])->orWhere('id', $payment->invoice_id);
                })
                ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                ->orderBy('due_date')
                ->get();
        }
        $selectedInvoice = $payment->invoice;
        return view('accounting.payments.edit', compact('payment', 'contacts', 'paymentMethods', 'bankAccounts', 'contactInvoices', 'selectedInvoice'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment); // Implement if needed
        if ($payment->status !== 'pending') {
            return redirect()->route('accounting.payments.show', $payment)
                ->with('error', 'Only pending payments can be edited.');
        }
        $validatedData = $request->validate([ /* ... validation rules ... */
             'contact_id' => 'required|exists:accounting_contacts,id', 'invoice_id' => 'nullable|exists:invoices,id',
             'payment_method_id' => 'required|exists:payment_methods,id', 'account_id' => 'required|exists:chart_of_accounts,id',
             'payment_date' => 'required|date', 'amount' => 'required|numeric|min:0.01',
             'reference' => 'nullable|string|max:255', 'notes' => 'nullable|string', 'status' => 'required|in:pending,completed,failed',
        ]);
        $schoolId = Auth::user()?->school_id;
        // ... additional validation ...
        DB::beginTransaction();
        try {
            $originalInvoiceId = $payment->invoice_id; $originalAmount = $payment->amount; $originalStatus = $payment->status;
            $payment->update($validatedData);
            // ... reverse old invoice ...
            if ($originalInvoiceId && $originalStatus === 'completed') {
                $oldInvoice = Invoice::find($originalInvoiceId);
                if ($oldInvoice) {
                    $oldInvoice->amount_paid = max(0, $oldInvoice->amount_paid - $originalAmount);
                    if (($oldInvoice->total - $oldInvoice->amount_paid) < 0.001) { $oldInvoice->status = 'paid'; }
                    elseif ($oldInvoice->amount_paid > 0) { $oldInvoice->status = 'partial'; }
                    else { $oldInvoice->status = 'sent'; }
                    $oldInvoice->save();
                }
             }
            // ... apply new invoice ...
             if ($payment->invoice_id && $payment->status === 'completed') {
                $newInvoice = Invoice::find($payment->invoice_id);
                 if ($newInvoice) {
                     $newInvoice->amount_paid = ($newInvoice->amount_paid ?? 0) + $payment->amount;
                    if (($newInvoice->total - $newInvoice->amount_paid) < 0.001) { $newInvoice->status = 'paid'; }
                    else { $newInvoice->status = 'partial'; }
                    $newInvoice->save();
                 } else { throw new \Exception("Newly associated invoice ID {$payment->invoice_id} not found."); }
              }
            // ... Update Journal Entry ...
             if ($payment->journal) { $payment->journal->entries()->delete(); $payment->journal->delete(); $payment->journal_id = null; }
             if ($payment->status === 'completed') { $this->createJournalForPayment($payment); } else { $payment->saveQuietly(); }

            DB::commit();
            return redirect()->route('accounting.payments.show', $payment)->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) { DB::rollBack(); Log::error("Error updating payment {$payment->id}: " . $e->getMessage()); return redirect()->back()->withInput()->with('error', 'Error updating payment: ' . $e->getMessage()); }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment); // Implement if needed
        if ($payment->status === 'completed') {
            return redirect()->route('accounting.payments.index')
                ->with('error', 'Completed payments cannot be deleted.');
        }
        DB::beginTransaction();
        try {
            $paymentNumber = $payment->payment_number;
            if ($payment->journal) { $payment->journal->entries()->delete(); $payment->journal->delete(); }
            $payment->delete();
            DB::commit();
            return redirect()->route('accounting.payments.index')->with('success', "Payment #{$paymentNumber} deleted successfully.");
        } catch (\Exception $e) { DB::rollBack(); Log::error("Error deleting payment {$payment->id}: " . $e->getMessage()); return redirect()->route('accounting.payments.index')->with('error', 'Error deleting payment: ' . $e->getMessage()); }
    }

    /**
     * Generate and download a PDF receipt for the payment.
     */
    public function downloadReceipt(Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment); // Implement if needed
        $payment->loadMissing(['contact', 'invoice', 'paymentMethod', 'account']);

        try {
            $data = ['payment' => $payment];
            $pdf = Pdf::loadView('accounting.payments.receipt_pdf', $data);
            $filename = 'payment-receipt-' . preg_replace('/[^A-Za-z0-9\-]/', '', $payment->payment_number) . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error("Failed to generate PDF receipt for Payment {$payment->id}: " . $e->getMessage());
            return redirect()->route('accounting.payments.show', $payment)->with('error', 'Could not generate PDF receipt.');
        }
    }

    /**
     * Show a printer-friendly HTML receipt (Original receipt method).
     */
    public function receipt(Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment); // Implement if needed
        $payment->load(['contact', 'invoice', 'paymentMethod', 'account', 'creator']);
        return view('accounting.payments.receipt', compact('payment'));
    }

    // --- HELPER METHODS ---

    /**
     * Create or update the journal entry for a payment.
     */
    private function createJournalForPayment(Payment $payment)
    {
        $payment->loadMissing(['contact', 'account', 'invoice']); // Ensure relations loaded

        // Delete existing journal for this payment (handles updates cleanly)
        if ($payment->journal) {
            $payment->journal->entries()->delete();
            $payment->journal->delete();
            $payment->journal_id = null; // Dissociate first
        }

        // Get the AR account using the corrected helper
        $arAccount = $this->findOrCreateArAccount($payment->school_id); // Pass school_id if needed for creation
        if (!$arAccount) { // Add check
             throw new \Exception('Failed to find or create Accounts Receivable account.');
        }

        $bankOrCashAccount = $payment->account;
        if (!$bankOrCashAccount) { Log::critical("Bank/Cash account ID {$payment->account_id} not found."); throw new \Exception("Deposit account not found."); }

        $journal = Journal::create([
            'school_id' => $payment->school_id,
            'reference' => 'Payment #' . $payment->payment_number . ($payment->invoice ? ' (Inv #' . $payment->invoice->invoice_number . ')' : ''),
            'journal_date' => $payment->payment_date,
            'description' => 'Payment received from ' . ($payment->contact->name ?? 'Unknown Contact'),
            'status' => 'posted',
            'created_by' => $payment->created_by ?? auth()->id(),
            'document_type' => 'payment',
            'document_id' => $payment->id,
        ]);

        $totalDebit = 0; $totalCredit = 0;

        // Debit Bank/Cash Account
        JournalEntry::create([
            'journal_id' => $journal->id, 'account_id' => $bankOrCashAccount->id, 'debit' => $payment->amount, 'credit' => 0,
            'description' => 'Payment received from ' . ($payment->contact->name ?? 'Unknown Contact'), 'contact_id' => $payment->contact_id,
        ]); $totalDebit += $payment->amount;

        // Credit Accounts Receivable
        JournalEntry::create([
            'journal_id' => $journal->id, 'account_id' => $arAccount->id, 'debit' => 0, 'credit' => $payment->amount,
            'description' => 'Payment applied by ' . ($payment->contact->name ?? 'Unknown Contact'), 'contact_id' => $payment->contact_id,
        ]); $totalCredit += $payment->amount;

        if (abs($totalDebit - $totalCredit) > 0.001) { Log::error("Journal unbalanced for Payment {$payment->id}."); throw new \Exception("Journal unbalanced for Payment #{$payment->payment_number}."); }

        $payment->journal_id = $journal->id;
        $payment->saveQuietly(); // Save the link

        return $journal;
    }

    /**
     * Helper to find or create the Accounts Receivable system account.
     * CORRECTED VERSION - Does not filter ChartOfAccount by school_id directly.
     */
     private function findOrCreateArAccount($schoolId) // Keep $schoolId if needed for *creation*
    {
        $arCode = config('accounting.ar_account_code', '1100');

        // Query WITHOUT school_id filter directly on chart_of_accounts
        $arAccount = ChartOfAccount::where('account_code', $arCode)
            ->whereHas('accountType', fn($q) => $q->where('code', 'ASSET'))
            ->first(); // Assuming AR code is globally unique

        if ($arAccount) return $arAccount;

        // Fallback: Search by name WITHOUT school_id filter
         $arAccount = ChartOfAccount::where('name', 'LIKE', 'Accounts Receivable%')
             ->whereHas('accountType', fn($q) => $q->where('code', 'ASSET'))
            ->first();

         if ($arAccount) return $arAccount;

         // If still not found, create it globally (or link to school if CoA table has school_id)
         Log::warning("Accounts Receivable account not found. Creating default global AR account (Code: {$arCode}).");
         $assetType = AccountType::where('code', 'ASSET')->first();
         if (!$assetType) throw new \Exception('Asset Account Type not found. Cannot create AR account.');

         return ChartOfAccount::create([
            // 'school_id' => $schoolId, // Only include if your CoA table has this column
            'account_type_id' => $assetType->id,
            'account_code' => $arCode,
            'name' => 'Accounts Receivable',
            'description' => 'Default Accounts Receivable (System Created)',
            'is_active' => true,
            'is_system' => true,
            // 'is_system_ar' => true, // Consider adding this flag via migration
        ]);
    } // *** ENSURE THIS BRACE IS PRESENT ***


    /**
     * Basic authorization check helper (Example)
     */
    private function authorizeSchoolAccess($model)
    {
        $userSchoolId = Auth::user()?->school_id;
        if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
            abort(403, 'Unauthorized action.');
        }
    }

} // End of PaymentsController class