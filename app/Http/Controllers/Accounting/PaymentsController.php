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
use App\Mail\PaymentConfirmationMail; // Assuming you created this: php artisan make:mail PaymentConfirmationMail --markdown=emails.payments.confirmation
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema; // Schema facade might not be needed unless checking table structures dynamically
use Barryvdh\DomPDF\Facade\Pdf;     // For PDF Generation (ensure installed: composer require barryvdh/laravel-dompdf)

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

        // Contacts specific to the school
        $contacts = Contact::where('is_active', true)
            ->when($school_id, function ($query, $school_id) {
                return $query->where('school_id', $school_id);
            })
            ->orderBy('name')->get();

        // Payment methods (often global, but could be filtered if needed)
        $paymentMethods = PaymentMethod::where('is_active', true)
            // ->when($school_id, ...) // Add if payment methods are school-specific
            ->orderBy('name')->get();

        // Bank/Deposit Accounts (ChartOfAccount) - Should likely be filtered by school
        $bankAccounts = ChartOfAccount::where('is_bank_account', 1)
            ->where('is_active', 1)
            // ->when($school_id, function ($query, $school_id) { // Uncomment if CoA are school-specific
            //     return $query->where('school_id', $school_id);
            // })
            ->orderBy('name')
            ->get();

        $selectedInvoice = null;
        $contactInvoices = collect(); // Initialize as empty collection

        if ($request->has('invoice_id')) {
            $selectedInvoice = Invoice::with('contact')
                                ->when($school_id, function ($query, $school_id) {
                                    return $query->where('school_id', $school_id);
                                })
                                ->find($request->invoice_id);

            // If an invoice is selected, find other outstanding invoices for the SAME contact
            if ($selectedInvoice?->contact_id) {
                 $contactInvoices = Invoice::where('contact_id', $selectedInvoice->contact_id)
                                        ->whereIn('status', ['sent', 'partial', 'overdue']) // Only show unpaid/partially paid
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
     * Get outstanding invoices for a specific contact (Used for AJAX calls).
     */
    public function getContactInvoices(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|integer|exists:accounting_contacts,id'
        ]);
        $school_id = Auth::user()?->school_id;
        $contactId = $validated['contact_id'];

        // Security check: Ensure the contact belongs to the user's school if applicable
        if ($school_id) {
            $contactExists = Contact::where('id', $contactId)->where('school_id', $school_id)->exists();
            if (!$contactExists) {
                 return response()->json(['error' => 'Contact not found or access denied.'], 403);
            }
        }

        $invoices = Invoice::where('contact_id', $contactId)
            ->whereIn('status', ['sent', 'partial', 'overdue']) // Only show unpaid/partially paid
            ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
            ->select('id', 'invoice_number', 'total', 'amount_paid', 'due_date') // Select only needed fields
            ->orderBy('due_date')
            ->get()
            ->map(function($invoice) {
                // Calculate balance due on the server-side for accuracy
                $invoice->balance_due = max(0, $invoice->total - ($invoice->amount_paid ?? 0));
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
            'invoice_id' => 'nullable|exists:invoices,id', // Nullable if payment is not linked to a specific invoice initially
            'payment_method_id' => 'required|exists:payment_methods,id',
            'account_id' => 'required|exists:chart_of_accounts,id', // The bank/cash account receiving the payment
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'send_confirmation' => 'nullable|boolean',
        ]);

        $schoolId = Auth::user()?->school_id;

        // --- Additional Validation ---
        // 1. Verify the selected account_id is actually a bank account
        $bankAccount = ChartOfAccount::where('id', $validatedData['account_id'])
                                     ->where('is_bank_account', true)
                                     // ->when($schoolId, fn($q, $id) => $q->where('school_id', $id)) // Add if CoA are school-specific
                                     ->first();
        if (!$bankAccount) {
            return redirect()->back()->withInput()->with('error', 'Invalid deposit account selected.');
        }

        // 2. Verify contact belongs to the school (if multi-tenant)
        if ($schoolId) {
            $contactValid = Contact::where('id', $validatedData['contact_id'])
                                   ->where('school_id', $schoolId)
                                   ->exists();
            if (!$contactValid) {
                 return redirect()->back()->withInput()->with('error', 'Selected contact is invalid.');
            }
        }

        // 3. If invoice_id is provided, verify it belongs to the selected contact AND school
        if (!empty($validatedData['invoice_id'])) {
             $invoiceValid = Invoice::where('id', $validatedData['invoice_id'])
                                    ->where('contact_id', $validatedData['contact_id'])
                                    ->when($schoolId, fn($q, $id) => $q->where('school_id', $id))
                                    ->exists();
             if (!$invoiceValid) {
                 return redirect()->back()->withInput()->with('error', 'Selected invoice does not match the selected contact or school.');
             }
             // Optional: Check if invoice is already fully paid
             $invoice = Invoice::find($validatedData['invoice_id']);
             if ($invoice && $invoice->total - ($invoice->amount_paid ?? 0) < 0.01) {
                 // Allow overpayment or handle as needed, maybe show warning?
                 // return redirect()->back()->withInput()->with('warning', 'Selected invoice is already fully paid.');
             }
        }
        // --- End Additional Validation ---


        // Generate Payment Number (Example: PMT-YYYYMMDD-XXXX)
        $prefix = 'PMT-' . date('Ymd') . '-';
        $lastPayment = Payment::where('payment_number', 'LIKE', $prefix . '%')
                                ->when($schoolId, fn($q, $id) => $q->where('school_id', $id))
                                ->orderBy('payment_number', 'desc')
                                ->first();
        $nextNum = $lastPayment ? (intval(substr($lastPayment->payment_number, -4)) + 1) : 1;
        $paymentNumber = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);


        DB::beginTransaction();
        try {
            $paymentData = $validatedData;
            $paymentData['school_id'] = $schoolId;
            $paymentData['payment_number'] = $paymentNumber;
            $paymentData['status'] = 'completed'; // Assume payment is completed upon manual entry
            $paymentData['is_pos_transaction'] = false; // Assuming not from POS
            $paymentData['created_by'] = auth()->id();

            $payment = Payment::create($paymentData);

            // If linked to an invoice, update the invoice's paid amount and status
            if ($payment->invoice_id) {
                $invoice = Invoice::find($payment->invoice_id);
                if ($invoice) {
                    $invoice->amount_paid = ($invoice->amount_paid ?? 0) + $payment->amount;
                    // Use small tolerance for float comparison
                    $balance = $invoice->total - $invoice->amount_paid;
                    if ($balance < 0.001) {
                        $invoice->status = 'paid';
                    } else {
                        $invoice->status = 'partial';
                    }
                    $invoice->save();
                } else {
                     // This should ideally not happen due to validation, but good to handle
                     Log::error("Invoice with ID {$payment->invoice_id} not found during payment creation for Payment #{$payment->payment_number}.");
                     throw new \Exception("Associated invoice not found.");
                }
            }

            // Create the Accounting Journal Entry
            $this->createJournalForPayment($payment); // This helper handles the debit/credit logic

            DB::commit(); // Commit only if everything succeeded

            // Send Confirmation Email (Optional)
            $emailSent = false;
            $emailError = null;
            if ($request->boolean('send_confirmation')) {
                 $payment->loadMissing('contact'); // Ensure contact is loaded
                 if ($payment->contact?->email) {
                    try {
                        Mail::to($payment->contact->email)->send(new PaymentConfirmationMail($payment));
                        $emailSent = true;
                    } catch (\Exception $mailException) {
                        Log::error("Failed to send payment confirmation email for Payment ID {$payment->id}: " . $mailException->getMessage());
                        $emailError = 'Failed to send confirmation email. Please check logs.';
                    }
                 } else {
                    $emailError = 'Confirmation email not sent: Contact has no email address.';
                 }
            }

            $successMessage = 'Payment recorded successfully.';
            if ($emailSent) $successMessage .= ' Confirmation email sent.';

            // Redirect to the receipt download instead of index for immediate access
            return redirect()->route('accounting.payments.download-receipt', $payment)
                ->with('success', $successMessage)
                ->with('warning_email', $emailError); // Use a specific key for email warnings

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            Log::error("Error recording payment: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Error recording payment: ' . $e->getMessage()) // Show specific error if safe, otherwise generic message
                ->withInput(); // Retain user input
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment); // Implement if using policies or more complex auth
        $payment->load(['contact', 'invoice', 'paymentMethod', 'account', 'creator', 'journal.entries.account']); // Eager load relations
        return view('accounting.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     * NOTE: Editing completed financial transactions is generally discouraged.
     * Consider if a reversal/credit note process is more appropriate than direct editing.
     * This example allows editing 'pending' payments.
     */
    public function edit(Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment);

        // Typically, only allow editing if the payment is not yet fully processed or 'completed'
        if ($payment->status !== 'pending' && $payment->status !== 'failed') { // Adjust allowed statuses if needed
            return redirect()->route('accounting.payments.show', $payment)
                ->with('error', 'Only pending or failed payments can be directly edited. Consider reversing completed payments.');
        }

        $payment->load(['contact', 'invoice', 'paymentMethod', 'account']);
        $school_id = Auth::user()?->school_id;

        $contacts = Contact::where('is_active', true)->when($school_id, fn($q, $id) => $q->where('school_id', $id))->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();
        $bankAccounts = ChartOfAccount::where('is_bank_account', true)->where('is_active', true)
                        // ->when($school_id, fn($q, $id) => $q->where('school_id', $id)) // Filter if needed
                        ->orderBy('name')->get();

        $contactInvoices = collect();
        if ($payment->contact_id) {
            $contactInvoices = Invoice::where('contact_id', $payment->contact_id)
                ->where(function($query) use ($payment) {
                     // Show outstanding invoices OR the currently linked invoice (even if paid)
                     $query->whereIn('status', ['sent', 'partial', 'overdue'])
                           ->orWhere('id', $payment->invoice_id);
                })
                ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                ->orderBy('due_date')
                ->get()
                 ->map(function($invoice) {
                    $invoice->balance_due = max(0, $invoice->total - ($invoice->amount_paid ?? 0));
                    return $invoice;
                 });
        }

        $selectedInvoice = $payment->invoice; // Pre-select the currently associated invoice

        return view('accounting.payments.edit', compact('payment', 'contacts', 'paymentMethods', 'bankAccounts', 'contactInvoices', 'selectedInvoice'));
    }


    /**
     * Update the specified resource in storage.
     * WARNING: Updating financial records requires careful handling of related data (invoices, journals).
     */
    public function update(Request $request, Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment);

        if ($payment->status !== 'pending' && $payment->status !== 'failed') { // Same check as edit
            return redirect()->route('accounting.payments.show', $payment)
                ->with('error', 'Only pending or failed payments can be updated.');
        }

        // Use similar validation as store, adjust as needed for updates
        $validatedData = $request->validate([
             'contact_id' => 'required|exists:accounting_contacts,id',
             'invoice_id' => 'nullable|exists:invoices,id',
             'payment_method_id' => 'required|exists:payment_methods,id',
             'account_id' => 'required|exists:chart_of_accounts,id',
             'payment_date' => 'required|date',
             'amount' => 'required|numeric|min:0.01',
             'reference' => 'nullable|string|max:255',
             'notes' => 'nullable|string',
             // Potentially allow updating status if applicable (e.g., from pending to completed/failed)
             'status' => 'required|in:pending,completed,failed',
        ]);

        $schoolId = Auth::user()?->school_id;

        // --- Additional Validation (similar to store) ---
        $bankAccount = ChartOfAccount::where('id', $validatedData['account_id'])->where('is_bank_account', true)->first();
        if (!$bankAccount) return redirect()->back()->withInput()->with('error', 'Invalid deposit account selected.');

        if ($schoolId) {
             $contactValid = Contact::where('id', $validatedData['contact_id'])->where('school_id', $schoolId)->exists();
             if (!$contactValid) return redirect()->back()->withInput()->with('error', 'Selected contact is invalid.');
        }

        if (!empty($validatedData['invoice_id'])) {
             $invoiceValid = Invoice::where('id', $validatedData['invoice_id'])
                                ->where('contact_id', $validatedData['contact_id'])
                                ->when($schoolId, fn($q, $id) => $q->where('school_id', $id))
                                ->exists();
             if (!$invoiceValid) return redirect()->back()->withInput()->with('error', 'Selected invoice does not match the selected contact or school.');
        }
        // --- End Validation ---

        DB::beginTransaction();
        try {
            // Store original values before update for potential reversal
            $originalInvoiceId = $payment->invoice_id;
            $originalAmount = $payment->amount;
            $originalStatus = $payment->status; // Status before update

            // Update the payment record itself
            $payment->update($validatedData);

            // --- Adjust related records ---

            // 1. Reverse effect on the ORIGINAL invoice if it existed and the ORIGINAL payment was 'completed'
            if ($originalInvoiceId && $originalStatus === 'completed') {
                $oldInvoice = Invoice::find($originalInvoiceId);
                if ($oldInvoice) {
                    $oldInvoice->amount_paid = max(0, ($oldInvoice->amount_paid ?? 0) - $originalAmount); // Subtract old amount
                    // Recalculate status based on new amount_paid
                    $balance = $oldInvoice->total - $oldInvoice->amount_paid;
                    if ($balance < 0.001 && $oldInvoice->amount_paid > 0) { $oldInvoice->status = 'paid'; } // Check amount > 0 for $0 invoices
                    elseif ($oldInvoice->amount_paid > 0) { $oldInvoice->status = 'partial'; }
                    else { $oldInvoice->status = 'sent'; } // Or whatever the status should be if amount_paid becomes 0
                    $oldInvoice->save();
                }
            }

            // 2. Apply effect to the NEW (or potentially same) invoice if the UPDATED payment is 'completed'
             if ($payment->invoice_id && $payment->status === 'completed') {
                $newInvoice = Invoice::find($payment->invoice_id);
                 if ($newInvoice) {
                     $newInvoice->amount_paid = ($newInvoice->amount_paid ?? 0) + $payment->amount; // Add NEW amount
                    // Recalculate status
                    $balance = $newInvoice->total - $newInvoice->amount_paid;
                    if ($balance < 0.001) { $newInvoice->status = 'paid'; }
                    else { $newInvoice->status = 'partial'; }
                    $newInvoice->save();
                 } else {
                    // Should be caught by validation, but handle defensively
                    throw new \Exception("Newly associated invoice ID {$payment->invoice_id} not found during update.");
                 }
              }

            // 3. Update Journal Entry: Delete old, create new if status is 'completed'
             if ($payment->journal) {
                 $payment->journal->entries()->delete(); // Delete old entries
                 $payment->journal->delete();          // Delete old journal header
                 $payment->journal_id = null;          // Dissociate
                 // Note: We save the null journal_id below based on status
             }

             if ($payment->status === 'completed') {
                 // Create a new journal entry reflecting the updated payment details
                 $this->createJournalForPayment($payment); // This helper handles creation and linking
             } else {
                 // If status is not completed (e.g., pending, failed), ensure no journal is linked
                 $payment->journal_id = null;
                 $payment->saveQuietly(); // Save the potentially null journal_id without triggering events
             }

            DB::commit();
            return redirect()->route('accounting.payments.show', $payment)->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating payment {$payment->id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Error updating payment: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * WARNING: Deleting financial records is highly discouraged. Implement soft deletes or a reversal process instead.
     * This example implements hard delete but only for non-completed payments.
     */
    public function destroy(Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment);

        // CRITICAL: Prevent deleting completed transactions. Require a reversal instead.
        if ($payment->status === 'completed') {
            return redirect()->route('accounting.payments.index')
                ->with('error', 'Completed payments cannot be deleted. Please create a reversal or refund.');
        }

        // Allow deletion of pending/failed payments? Decide based on workflow.
        // if ($payment->status !== 'pending' && $payment->status !== 'failed') {
        //     return redirect()->route('accounting.payments.index')
        //         ->with('error', 'Only pending or failed payments can be deleted.');
        // }


        DB::beginTransaction();
        try {
            $paymentNumber = $payment->payment_number; // Get identifier before deletion

            // 1. Delete associated Journal (if any) first to avoid foreign key constraints
            if ($payment->journal) {
                 $payment->journal->entries()->delete(); // Delete entries
                 $payment->journal->delete();          // Delete journal header
            }

            // 2. Reverse impact on Invoice if the payment was previously linked (even if not 'completed')
            //    This might be needed if a 'pending' payment was mistakenly linked.
            if ($payment->invoice_id) {
                $invoice = Invoice::find($payment->invoice_id);
                 if ($invoice) {
                    // Only reverse if the payment *had* contributed (e.g., if status was ever completed/partial before becoming pending/failed)
                    // This part needs careful consideration based on your status flows.
                    // A simple approach for pending/failed might be to NOT reverse invoice amount here,
                    // assuming it was never applied if the payment wasn't 'completed'.
                    // If you *do* apply amounts for pending, you MUST reverse here.
                    // Let's assume non-completed didn't affect invoice amount_paid yet.
                 }
            }

            // 3. Delete the payment itself
            $payment->delete();

            DB::commit();
            return redirect()->route('accounting.payments.index')->with('success', "Payment #{$paymentNumber} deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting payment {$payment->id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('accounting.payments.index')->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }

    /**
     * Generate and download a PDF receipt for the payment.
     * Requires: composer require barryvdh/laravel-dompdf
     */
    public function downloadReceipt(Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment);
        $payment->loadMissing(['contact', 'invoice', 'paymentMethod', 'account', 'creator']); // Ensure needed data is loaded

        try {
            // Data to pass to the PDF view
            $data = ['payment' => $payment];

            // Ensure you have the view: resources/views/accounting/payments/receipt_pdf.blade.php
            $pdf = Pdf::loadView('accounting.payments.receipt_pdf', $data);

            // Generate a safe filename
            $filename = 'payment-receipt-' . preg_replace('/[^A-Za-z0-9\-]/', '', $payment->payment_number) . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error("Failed to generate PDF receipt for Payment {$payment->id}: " . $e->getMessage());
            // Redirect back to the payment details page with an error
            return redirect()->route('accounting.payments.show', $payment)->with('error', 'Could not generate PDF receipt. Please check system logs.');
        }
    }

    /**
     * Show a printer-friendly HTML receipt (Alternative to PDF).
     */
    public function receipt(Payment $payment)
    {
        // $this->authorizeSchoolAccess($payment);
        $payment->load(['contact', 'invoice', 'paymentMethod', 'account', 'creator']);
        // Ensure you have the view: resources/views/accounting/payments/receipt.blade.php
        return view('accounting.payments.receipt', compact('payment'));
    }

    // --- HELPER METHODS ---

    /**
     * Create the journal entry for a *completed* payment.
     * Assumes standard double-entry: Debit Bank/Cash, Credit Accounts Receivable.
     */
    private function createJournalForPayment(Payment $payment)
    {
        // Ensure necessary relationships are loaded for details
        $payment->loadMissing(['contact', 'account', 'invoice']);

        // Defensive check: Only create journal for completed payments
        if ($payment->status !== 'completed') {
             Log::warning("Attempted to create journal for non-completed payment ID {$payment->id} with status {$payment->status}.");
             return null; // Or throw exception if this shouldn't happen
        }

        // --- Get Accounts ---
        // 1. Bank/Cash Account (where money was deposited)
        $bankOrCashAccount = $payment->account; // Relation loaded via loadMissing
        if (!$bankOrCashAccount) {
             Log::critical("Bank/Cash account (ChartOfAccount ID: {$payment->account_id}) not found for Payment ID {$payment->id}. Cannot create journal.");
             throw new \Exception("Deposit account not found for Payment #{$payment->payment_number}.");
        }
        if (!$bankOrCashAccount->is_bank_account && !$bankOrCashAccount->is_cash_account) { // Add is_cash_account check if applicable
             Log::warning("Account ID {$bankOrCashAccount->id} used for payment {$payment->id} is not marked as a bank or cash account.");
             // Decide whether to proceed or throw error
        }

        // 2. Accounts Receivable Account (System Account)
        $arAccount = $this->findOrCreateArAccount($payment->school_id); // Pass school_id if needed for creation context
        if (!$arAccount) {
             Log::critical("Accounts Receivable account could not be found or created for school_id {$payment->school_id}. Cannot create journal for Payment ID {$payment->id}.");
             throw new \Exception('Failed to find or create Accounts Receivable account.');
        }

        // --- Delete Existing Journal (if any) ---
        // This handles cases where payment is updated (e.g., amount change) and needs a new journal
        if ($payment->journal) {
            try {
                $payment->journal->entries()->delete();
                $payment->journal->delete();
                $payment->journal_id = null; // Dissociate first before creating new one
                // No need to save payment here, will be saved after new journal is linked
            } catch (\Exception $e) {
                 Log::error("Error deleting existing journal for Payment ID {$payment->id}: " . $e->getMessage());
                 // Decide if this is critical enough to stop the process
            }
        }

        // --- Create New Journal Header ---
        $journal = Journal::create([
            'school_id' => $payment->school_id,
            'reference' => 'Payment #' . $payment->payment_number . ($payment->invoice ? ' / Inv #' . $payment->invoice->invoice_number : ''),
            'journal_date' => $payment->payment_date,
            'description' => 'Payment received from ' . ($payment->contact->name ?? 'Unknown Contact') . ($payment->reference ? ' Ref: '.$payment->reference : ''),
            'status' => 'posted', // Assume journals for completed payments are posted
            'created_by' => $payment->created_by ?? auth()->id(),
            'document_type' => 'payment', // Link back to the payment model
            'document_id' => $payment->id,      // Link back to the payment record ID
        ]);

        // --- Create Journal Entries (Debit/Credit) ---
        $totalDebit = 0;
        $totalCredit = 0;

        // DEBIT: The Bank/Cash account increases
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $bankOrCashAccount->id,
            'debit' => $payment->amount,
            'credit' => 0,
            'description' => 'Deposit for Payment #' . $payment->payment_number,
            'contact_id' => $payment->contact_id, // Link entry to the contact
        ]);
        $totalDebit += $payment->amount;

        // CREDIT: The Accounts Receivable decreases (customer owes less)
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $arAccount->id,
            'debit' => 0,
            'credit' => $payment->amount,
            'description' => 'Payment applied from ' . ($payment->contact->name ?? 'Unknown Contact'),
            'contact_id' => $payment->contact_id, // Link entry to the contact
        ]);
        $totalCredit += $payment->amount;

        // --- Balance Check ---
        if (abs($totalDebit - $totalCredit) > 0.001) { // Use tolerance for float comparison
             Log::error("Journal unbalanced for Payment {$payment->id}. Debit: {$totalDebit}, Credit: {$totalCredit}. Rolling back journal creation.");
             // Clean up the created journal and entries before throwing error
             $journal->entries()->delete();
             $journal->delete();
             throw new \Exception("Journal unbalanced for Payment #{$payment->payment_number}.");
        }

        // --- Link Journal to Payment ---
        $payment->journal_id = $journal->id;
        $payment->saveQuietly(); // Use saveQuietly to avoid triggering potential update events on Payment model again

        return $journal; // Return the created journal if needed
    }

    /**
     * Helper to find or create the Accounts Receivable system account.
     * Assumes AR account code is unique globally or defined in config.
     */
     private function findOrCreateArAccount($schoolId) // Keep $schoolId if needed for *creation* context
    {
        // Get the designated code for AR account from config (Best Practice)
        $arCode = config('accounting.ar_account_code', '1100'); // Default '1100' if not set

        // 1. Try finding by CODE and TYPE (Most reliable) - *Without* school_id filter
        $arAccount = ChartOfAccount::where('account_code', $arCode)
            ->whereHas('accountType', fn($q) => $q->where('code', 'ASSET')) // Ensure it's an Asset
            ->first();

        if ($arAccount) return $arAccount;

        // 2. Fallback: Try finding by NAME and TYPE - *Without* school_id filter
         Log::warning("Accounts Receivable account with code '{$arCode}' not found. Falling back to search by name 'Accounts Receivable%'.");
         $arAccount = ChartOfAccount::where('name', 'LIKE', 'Accounts Receivable%')
             ->whereHas('accountType', fn($q) => $q->where('code', 'ASSET'))
            ->first();

         if ($arAccount) return $arAccount;

         // 3. If still not found, CREATE it.
         Log::warning("Accounts Receivable account not found by code or name. Creating default global AR account (Code: {$arCode}). Ensure config('accounting.ar_account_code') is set correctly if needed.");

         $assetType = AccountType::where('code', 'ASSET')->first();
         if (!$assetType) {
            Log::critical("AccountType 'ASSET' not found in the database. Cannot create Accounts Receivable account.");
            throw new \Exception('Critical Setup Error: Asset Account Type not found.'); // This is a setup issue
         }

         try {
             return ChartOfAccount::create([
                // 'school_id' => $schoolId, // <<-- ONLY include this if your chart_of_accounts TABLE HAS school_id AND you intend AR accounts to be school-specific (less common)
                'account_type_id' => $assetType->id,
                'account_code' => $arCode, // Use the code from config
                'name' => 'Accounts Receivable',
                'description' => 'Default Accounts Receivable (System Created)',
                'is_active' => true,
                'is_system' => true, // Flag it as a system account
                // 'is_bank_account' => false, // Explicitly set other flags if needed
                // 'is_system_ar' => true, // Consider adding this flag via migration for even easier lookup
            ]);
         } catch (\Exception $e) {
             Log::critical("Failed to create default Accounts Receivable account: " . $e->getMessage());
             throw new \Exception("Failed to create required Accounts Receivable account. Please check database permissions and schema.");
         }
    } // *** Closing brace for findOrCreateArAccount ***


    /**
     * Basic authorization check helper (Example).
     * Replace or augment with Laravel Policies for more robust authorization.
     * Checks if the user's school_id matches the model's school_id.
     */
    private function authorizeSchoolAccess($model)
    {
        $userSchoolId = Auth::user()?->school_id;

        // If the user has a school_id (is not a superadmin maybe?)
        // AND the model has a school_id column
        // AND the IDs don't match -> deny access
        if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
            abort(403, 'Unauthorized action. Record belongs to another school.');
        }
        // Allow access if user has no school_id (superadmin?) or if IDs match
    }

} // End of PaymentsController class