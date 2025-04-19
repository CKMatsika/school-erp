<?php

namespace App\Http\Controllers\Accounting;

// Base Controller & Core Laravel Classes
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;

// Facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema; // Keep for AR helper
use Barryvdh\DomPDF\Facade\Pdf;

// Validation
use Illuminate\Validation\Rule;

// Application Models (Accounting)
use App\Models\Accounting\AccountType;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Contact;
use App\Models\Accounting\FeeStructure;
use App\Models\Accounting\FeeStructureItem;
use App\Models\Accounting\InventoryItem;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceItem;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentMethod;
use App\Models\Accounting\StockMovement; // Used in other controllers, maybe needed? Keep for now.
use App\Models\Accounting\TaxRate;

// Application Models (Other - Keep if needed by FeeStructure or other logic)
use App\Models\Student\AcademicYear; // Used in other controllers, maybe needed? Keep for now.

// Mailables
use App\Mail\PaymentConfirmationMail;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        $payments = Payment::with(['contact', 'invoice', 'paymentMethod', 'account']) // Added 'account'
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
            ->orderBy('name')->get(['id', 'name', 'contact_type']); // Added type, reduced columns

        // Payment methods
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Bank/Deposit Accounts
        $bankAccounts = ChartOfAccount::where('is_bank_account', 1) // Ensure is_bank_account exists
            ->where('is_active', 1)
            // Filter by school if CoA are school-specific and table has school_id
            // ->when($school_id && Schema::hasColumn('chart_of_accounts', 'school_id'), function ($query, $school_id) {
            //     return $query->where('school_id', $school_id);
            // })
            ->orderBy('name')
            ->get(['id', 'name', 'account_code']);

        $selectedInvoice = null;
        $contactInvoices = collect();

        // Pre-load data based on request (e.g., clicking 'Pay Now' on an invoice)
        $preselectContactId = $request->input('contact_id');
        $preselectInvoiceId = $request->input('invoice_id');

        if ($preselectInvoiceId) {
            $selectedInvoice = Invoice::with('contact')
                                ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                ->find($preselectInvoiceId);
            // If found, ensure contact matches or set contact ID
            if ($selectedInvoice) {
                 $preselectContactId = $selectedInvoice->contact_id;
            } else {
                 Log::warning("Invoice ID {$preselectInvoiceId} provided to create payment form, but not found.");
                 // Optionally flash a message back or handle differently
            }
        }

        // If contact ID is known (either from request or linked invoice), load their outstanding invoices
        if ($preselectContactId) {
             $contactInvoices = Invoice::where('contact_id', $preselectContactId)
                                    ->whereIn('status', ['sent', 'partial', 'overdue', 'unpaid']) // Include common unpaid statuses
                                    ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                    ->orderBy('due_date')
                                    ->get(['id', 'invoice_number', 'total', 'amount_paid', 'due_date']); // Fetch only needed data
        }

        // Pass the preselected IDs to the view for old() helper fallback
        $selectedContactId = old('contact_id', $preselectContactId);
        $selectedInvoiceId = old('invoice_id', $preselectInvoiceId);


        return view('accounting.payments.create', compact(
            'contacts',
            'paymentMethods',
            'bankAccounts',
            'selectedInvoice', // Could be null
            'contactInvoices',
            'selectedContactId', // For old() helper
            'selectedInvoiceId' // For old() helper
        ));
    }

    /**
     * Get outstanding invoices for a specific contact (Used for AJAX calls).
     */
    public function getContactInvoices(Request $request): \Illuminate\Http\JsonResponse // Added return type
    {
        // Use correct table name 'contacts'
        $validated = $request->validate([
            'contact_id' => 'required|integer|exists:contacts,id'
        ]);
        $school_id = Auth::user()?->school_id;
        $contactId = $validated['contact_id'];

        // Security check
        if ($school_id && Schema::hasColumn('contacts', 'school_id')) { // Check if contacts table has school_id
            $contactExists = Contact::where('id', $contactId)->where('school_id', $school_id)->exists();
            if (!$contactExists) {
                 return response()->json(['error' => 'Contact not found or access denied.'], 403);
            }
        }

        $invoices = Invoice::where('contact_id', $contactId)
            ->whereIn('status', ['sent', 'partial', 'overdue', 'unpaid']) // Only show unpaid/partially paid
            ->when($school_id && Schema::hasColumn('invoices', 'school_id'), fn($q, $id) => $q->where('school_id', $id))
            ->select('id', 'invoice_number', 'total', 'amount_paid', 'due_date')
            ->orderBy('due_date')
            ->get()
            ->map(function($invoice) {
                // Calculate balance due
                $invoice->balance_due = max(0, (float)$invoice->total - (float)($invoice->amount_paid ?? 0));
                return $invoice;
            });

        return response()->json($invoices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse // Added return type
    {
        // --- Initial Validation ---
        $validatedData = $request->validate([
            'contact_id' => 'required|exists:contacts,id', // Use correct table 'contacts'
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'send_confirmation' => 'nullable|boolean', // Correctly handles checkbox value '1' or absence
        ]);

        // --- Post-Validation Data Prep ---
        $schoolId = Auth::user()?->school_id;
        $hasCoASchoolId = Schema::hasColumn('chart_of_accounts', 'school_id');
        $hasContactSchoolId = Schema::hasColumn('contacts', 'school_id');
        $hasInvoiceSchoolId = Schema::hasColumn('invoices', 'school_id');

        // --- Additional Validation ---

        // 1. Verify the selected account_id is actually a bank/cash account AND belongs to school if applicable
        $bankAccountQuery = ChartOfAccount::where('id', $validatedData['account_id'])
                                          ->where('is_active', true)
                                          ->where(function($q) { // Must be bank OR cash account
                                                $q->where('is_bank_account', true)
                                                  ->orWhere('is_cash_account', true); // Add is_cash_account check if you use it
                                          });
        if ($schoolId && $hasCoASchoolId) {
            $bankAccountQuery->where('school_id', $schoolId);
        }
        $bankAccount = $bankAccountQuery->first();

        if (!$bankAccount) {
            // ***** DEBUG POINT 1 *****
            // This dd() will trigger if the selected account_id doesn't exist, isn't active,
            // isn't a bank/cash account, or doesn't belong to the user's school (if applicable).
            dd('FAIL: Bank/Cash account check failed.', [
                'account_id_submitted' => $validatedData['account_id'],
                'school_id_check_applied' => $schoolId && $hasCoASchoolId,
                'user_school_id' => $schoolId,
                'found_account' => ChartOfAccount::find($validatedData['account_id'])?->toArray() // Check if it exists at all
            ]);
            return redirect()->back()->withInput()->with('error', 'Invalid deposit account selected.');
        }

        // 2. Verify contact belongs to the school (if multi-tenant and contacts are school-specific)
        if ($schoolId && $hasContactSchoolId) {
            $contactValid = Contact::where('id', $validatedData['contact_id'])
                                   ->where('school_id', $schoolId)
                                   ->exists();
            if (!$contactValid) {
                 // ***** DEBUG POINT 2 *****
                 // This dd() will trigger if the selected contact_id doesn't belong to the user's school.
                 dd('FAIL: Contact school check failed.', [
                     'contact_id_submitted' => $validatedData['contact_id'],
                     'user_school_id' => $schoolId,
                     'contact_found' => Contact::find($validatedData['contact_id'])?->toArray()
                 ]);
                 return redirect()->back()->withInput()->with('error', 'Selected contact is invalid or does not belong to your school.');
            }
        }

        // 3. If invoice_id is provided, verify it belongs to the selected contact AND school
        $invoice = null; // Define invoice variable outside the block
        if (!empty($validatedData['invoice_id'])) {
             $invoiceQuery = Invoice::where('id', $validatedData['invoice_id'])
                                    ->where('contact_id', $validatedData['contact_id']); // Must match contact

             if ($schoolId && $hasInvoiceSchoolId) {
                 $invoiceQuery->where('school_id', $schoolId); // Must match school
             }
             $invoice = $invoiceQuery->first(); // Use first() to get the model or null

             if (!$invoice) {
                  // ***** DEBUG POINT 3 *****
                  // This dd() will trigger if an invoice_id was submitted, but it doesn't exist,
                  // or doesn't match the selected contact_id, or doesn't match the user's school_id.
                  dd('FAIL: Invoice validation failed.', [
                      'invoice_id_submitted' => $validatedData['invoice_id'],
                      'contact_id_submitted' => $validatedData['contact_id'],
                      'school_id_check_applied' => $schoolId && $hasInvoiceSchoolId,
                      'user_school_id' => $schoolId,
                      'invoice_found_by_id_only' => Invoice::find($validatedData['invoice_id'])?->toArray() // Check if ID exists at all
                  ]);
                 return redirect()->back()->withInput()->with('error', 'Selected invoice does not match the selected contact or school.');
             }

             // Optional: Check if invoice is already fully paid (consider allowing overpayment)
             // $balanceDue = $invoice->total - ($invoice->amount_paid ?? 0);
             // if ($balanceDue < 0.01) {
             //     // return redirect()->back()->withInput()->with('warning', 'Selected invoice is already fully paid.');
             // }
        }
        // --- End Additional Validation ---

        // --- Payment Number Generation ---
        $paymentNumber = $this->generateNextPaymentNumber($schoolId);

        // --- Database Transaction ---
        DB::beginTransaction();
        try {
            // Prepare data for Payment model
            $paymentData = $validatedData; // Start with validated data
            $paymentData['school_id'] = $schoolId; // Add school_id if applicable
            $paymentData['payment_number'] = $paymentNumber;
            $paymentData['status'] = 'completed'; // Assume completed
            $paymentData['is_pos_transaction'] = false; // Assume not POS
            $paymentData['created_by'] = auth()->id();

            // Create Payment
            $payment = Payment::create($paymentData);

            // Update Invoice if linked
            if ($invoice) { // Use the $invoice variable fetched during validation
                // It's safer to lock the invoice row to prevent race conditions
                $invoice = Invoice::lockForUpdate()->find($invoice->id);
                if($invoice){ // Re-check after lock
                    $invoice->amount_paid = (float)($invoice->amount_paid ?? 0) + (float)$payment->amount;
                    $balance = (float)$invoice->total - $invoice->amount_paid;
                    $tolerance = 0.01; // Use tolerance

                    if ($balance <= $tolerance) {
                        $invoice->status = 'paid';
                    } else {
                        $invoice->status = 'partial';
                    }
                    $invoice->save();
                } else {
                     Log::error("Failed to lock/find Invoice ID {$payment->invoice_id} for update during payment creation.");
                     throw new \Exception("Associated invoice could not be updated.");
                }
            }

            // Create Journal Entry
            $this->createJournalForPayment($payment);

            DB::commit();

            // --- Post-Commit Actions (Email) ---
            $emailSent = false;
            $emailError = null;
            if ($request->boolean('send_confirmation')) { // Use boolean() helper
                 $payment->loadMissing('contact');
                 if ($payment->contact?->email) {
                    try {
                        Mail::to($payment->contact->email)->send(new PaymentConfirmationMail($payment));
                        $emailSent = true;
                         Log::info("Payment confirmation email sent successfully for Payment ID {$payment->id} to {$payment->contact->email}.");
                    } catch (\Exception $mailException) {
                        Log::error("Failed to send payment confirmation email for Payment ID {$payment->id}: " . $mailException->getMessage());
                        $emailError = 'Payment saved, but failed to send confirmation email. Please check logs.';
                    }
                 } else {
                     Log::warning("Payment confirmation requested for Payment ID {$payment->id}, but contact ID {$payment->contact_id} has no email address.");
                    $emailError = 'Payment saved, but confirmation email not sent: Contact has no email address.';
                 }
            }

            $successMessage = 'Payment recorded successfully.';
            if ($emailSent) $successMessage .= ' Confirmation email sent.';

            // Redirect to receipt for immediate access
            return redirect()->route('accounting.payments.download-receipt', $payment)
                ->with('success', $successMessage)
                ->with('warning_email', $emailError); // Pass email status as separate flash message

        } catch (\Exception $e) {
            DB::rollBack();

            // ***** CATCH BLOCK - Keep this dd() for now *****
            dd('Exception Caught in store()!', $e->getMessage(), $e->getTraceAsString(), $e);

            Log::error("Error recording payment: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Error recording payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): View // Added return type
    {
        $this->authorizeSchoolAccess($payment); // Use helper
        $payment->loadMissing(['contact', 'invoice', 'paymentMethod', 'account', 'creator', 'journal.entries.account']);
        return view('accounting.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment): View | RedirectResponse // Added return types
    {
        $this->authorizeSchoolAccess($payment);

        if (!in_array($payment->status, ['pending', 'failed'])) { // Example editable statuses
            return redirect()->route('accounting.payments.show', $payment)
                ->with('error', 'Only pending or failed payments can be directly edited.');
        }

        $payment->loadMissing(['contact', 'invoice', 'paymentMethod', 'account']);
        $school_id = $payment->school_id; // Use payment's school_id

        $hasCoASchoolId = Schema::hasColumn('chart_of_accounts', 'school_id');
        $hasContactSchoolId = Schema::hasColumn('contacts', 'school_id');
        $hasInvoiceSchoolId = Schema::hasColumn('invoices', 'school_id');

        $contacts = Contact::where('is_active', true)
                    ->when($school_id && $hasContactSchoolId, fn($q) => $q->where('school_id', $school_id))
                    ->orderBy('name')->get(['id', 'name']);

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $bankAccounts = ChartOfAccount::where('is_active', true)
                        ->where(fn($q) => $q->where('is_bank_account', true)->orWhere('is_cash_account', true))
                        ->when($school_id && $hasCoASchoolId, fn($q) => $q->where('school_id', $school_id))
                        ->orderBy('name')->get(['id', 'name', 'account_code']);

        $contactInvoices = collect();
        if ($payment->contact_id) {
            $contactInvoices = Invoice::where('contact_id', $payment->contact_id)
                ->where(function($query) use ($payment) {
                     $query->whereIn('status', ['sent', 'partial', 'overdue', 'unpaid'])
                           ->orWhere('id', $payment->invoice_id); // Include currently linked one
                })
                ->when($school_id && $hasInvoiceSchoolId, fn($q) => $q->where('school_id', $school_id))
                ->orderBy('due_date')
                ->get(['id', 'invoice_number', 'total', 'amount_paid', 'due_date'])
                 ->map(function($invoice) {
                    $invoice->balance_due = max(0, (float)$invoice->total - (float)($invoice->amount_paid ?? 0));
                    return $invoice;
                 });
        }

        $selectedInvoice = $payment->invoice;

        return view('accounting.payments.edit', compact('payment', 'contacts', 'paymentMethods', 'bankAccounts', 'contactInvoices', 'selectedInvoice'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment): RedirectResponse // Added return type
    {
        $this->authorizeSchoolAccess($payment);

        if (!in_array($payment->status, ['pending', 'failed'])) {
            return redirect()->route('accounting.payments.show', $payment)
                ->with('error', 'Only pending or failed payments can be updated.');
        }

        $validatedData = $request->validate([
             'contact_id' => 'required|exists:contacts,id', // Use correct table 'contacts'
             'invoice_id' => 'nullable|exists:invoices,id',
             'payment_method_id' => 'required|exists:payment_methods,id',
             'account_id' => 'required|exists:chart_of_accounts,id',
             'payment_date' => 'required|date',
             'amount' => 'required|numeric|min:0.01',
             'reference' => 'nullable|string|max:255',
             'notes' => 'nullable|string',
             'status' => 'required|in:pending,completed,failed', // Allow status update
        ]);

        $schoolId = $payment->school_id; // Use payment's school_id
        $hasCoASchoolId = Schema::hasColumn('chart_of_accounts', 'school_id');
        $hasContactSchoolId = Schema::hasColumn('contacts', 'school_id');
        $hasInvoiceSchoolId = Schema::hasColumn('invoices', 'school_id');


        // --- Additional Validation (similar logic to store) ---
        $bankAccountQuery = ChartOfAccount::where('id', $validatedData['account_id'])
                        ->where('is_active', true)
                        ->where(fn($q) => $q->where('is_bank_account', true)->orWhere('is_cash_account', true));
         if ($schoolId && $hasCoASchoolId) { $bankAccountQuery->where('school_id', $schoolId); }
         if (!$bankAccountQuery->exists()) {
            return redirect()->back()->withInput()->with('error', 'Invalid deposit account selected.');
         }

        if ($schoolId && $hasContactSchoolId) {
             $contactValid = Contact::where('id', $validatedData['contact_id'])->where('school_id', $schoolId)->exists();
             if (!$contactValid) { return redirect()->back()->withInput()->with('error', 'Selected contact is invalid.'); }
        }

        $invoice = null;
        if (!empty($validatedData['invoice_id'])) {
             $invoiceQuery = Invoice::where('id', $validatedData['invoice_id'])
                        ->where('contact_id', $validatedData['contact_id']);
              if ($schoolId && $hasInvoiceSchoolId) { $invoiceQuery->where('school_id', $schoolId); }
              $invoice = $invoiceQuery->first(); // Get invoice model or null
             if (!$invoice) { return redirect()->back()->withInput()->with('error', 'Selected invoice does not match the selected contact or school.'); }
        }
        // --- End Validation ---

        DB::beginTransaction();
        try {
            // Store original values
            $originalInvoiceId = $payment->getOriginal('invoice_id'); // Get original before potential update
            $originalAmount = (float) $payment->getOriginal('amount');
            $originalStatus = $payment->getOriginal('status');

            // --- Perform Update ---
            $payment->fill($validatedData); // Fill with new validated data
            $payment->save(); // Save the updated payment first

            // Reload the invoice relationship based on the potentially updated invoice_id
            $payment->load('invoice');
            $newInvoice = $payment->invoice; // Could be null if invoice_id was removed

            // --- Adjust related Invoices ---

            // 1. Reverse effect on the ORIGINAL invoice if it's different from the new one
            //    AND the original payment was 'completed'.
            if ($originalInvoiceId && $originalInvoiceId !== $payment->invoice_id && $originalStatus === 'completed') {
                $oldInvoice = Invoice::lockForUpdate()->find($originalInvoiceId);
                if ($oldInvoice) {
                    $oldInvoice->amount_paid = max(0, (float)($oldInvoice->amount_paid ?? 0) - $originalAmount);
                    $this->updateInvoiceStatus($oldInvoice); // Use helper
                    $oldInvoice->save();
                }
            }

            // 2. Apply effect to the NEW invoice if it exists
            //    Calculate the CHANGE in applied amount if the invoice is the SAME.
            //    Apply the full new amount if the invoice is different OR if original wasn't completed.
            if ($newInvoice) {
                 $newInvoice = Invoice::lockForUpdate()->find($newInvoice->id); // Lock the new/current invoice
                 if ($newInvoice) {
                     $amountChange = (float) $payment->amount; // Default to applying the full new amount

                     if ($originalInvoiceId === $payment->invoice_id) { // Invoice didn't change
                         if ($originalStatus === 'completed' && $payment->status !== 'completed') {
                            // Payment moved from completed to pending/failed: REVERSE original amount
                            $amountChange = -$originalAmount;
                         } elseif ($originalStatus !== 'completed' && $payment->status === 'completed') {
                             // Payment moved from pending/failed to completed: APPLY new amount
                             $amountChange = (float) $payment->amount;
                         } elseif ($originalStatus === 'completed' && $payment->status === 'completed') {
                             // Payment stayed completed, amount might have changed: Apply the DIFFERENCE
                              $amountChange = (float) $payment->amount - $originalAmount;
                         } else {
                             // Payment was pending/failed and remains pending/failed: No change to invoice applied amount
                             $amountChange = 0;
                         }
                     } elseif ($payment->status !== 'completed') {
                         // Invoice changed, but new payment is not completed: Don't apply amount
                         $amountChange = 0;
                     }
                     // Else (invoice changed AND new payment is completed): Apply full new amount (default $amountChange is correct)


                     if ($amountChange != 0) { // Only update if there's a change
                         $newInvoice->amount_paid = (float)($newInvoice->amount_paid ?? 0) + $amountChange;
                         $this->updateInvoiceStatus($newInvoice); // Use helper
                         $newInvoice->save();
                     }
                 } else {
                    throw new \Exception("Associated invoice ID {$payment->invoice_id} not found during update.");
                 }
            }

            // 3. Update Journal Entry
             $existingJournal = $payment->journal()->first(); // Get existing journal if any
             if ($existingJournal) {
                 // Always delete old journal if payment is updated
                 $existingJournal->entries()->delete();
                 $existingJournal->delete();
                 $payment->journal_id = null;
             }

             if ($payment->status === 'completed') {
                 // Create new journal only if the updated status is 'completed'
                 $this->createJournalForPayment($payment); // Will re-link payment->journal_id
             } else {
                  // Ensure journal_id is null if status is not completed
                  if ($payment->journal_id !== null) {
                      $payment->journal_id = null;
                      $payment->saveQuietly(); // Save without firing events again
                  }
             }


            DB::commit();
            return redirect()->route('accounting.payments.show', $payment)->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            // ***** CATCH BLOCK - Keep dd() for now *****
            dd('Exception Caught in update()!', $e->getMessage(), $e->getTraceAsString(), $e);

            Log::error("Error updating payment {$payment->id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Error updating payment: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage. (Reversal is preferred)
     */
    public function destroy(Payment $payment): RedirectResponse // Added return type
    {
        $this->authorizeSchoolAccess($payment);

        if ($payment->status === 'completed') {
            return redirect()->route('accounting.payments.index')
                ->with('error', 'Completed payments cannot be deleted. Please create a reversal or refund.');
        }

        DB::beginTransaction();
        try {
            $paymentNumber = $payment->payment_number;
            $invoiceId = $payment->invoice_id;
            $amount = (float) $payment->amount;
            $originalStatus = $payment->status; // Status before delete (likely pending/failed)

            // Delete Journal First
            if ($payment->journal) {
                 $payment->journal->entries()->delete();
                 $payment->journal->delete();
            }

            // Delete the Payment
            $payment->delete();

            // Reverse Invoice Amount only if the deleted payment *was* completed
            // (This check might be redundant given the initial block, but good practice)
            if ($invoiceId && $originalStatus === 'completed') { // Should technically not happen due to initial check
                $invoice = Invoice::lockForUpdate()->find($invoiceId);
                if ($invoice) {
                    $invoice->amount_paid = max(0, (float)($invoice->amount_paid ?? 0) - $amount);
                    $this->updateInvoiceStatus($invoice); // Use helper
                    $invoice->save();
                 }
            }

            DB::commit();
            return redirect()->route('accounting.payments.index')->with('success', "Payment #{$paymentNumber} deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();

            // ***** CATCH BLOCK - Keep dd() for now *****
            dd('Exception Caught in destroy()!', $e->getMessage(), $e->getTraceAsString(), $e);

            Log::error("Error deleting payment {$payment->id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('accounting.payments.index')->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }

    /**
     * Generate and download a PDF receipt for the payment.
     */
    public function downloadReceipt(Payment $payment): Response // Added return type
    {
        $this->authorizeSchoolAccess($payment);
        $payment->loadMissing(['contact', 'invoice', 'paymentMethod', 'account', 'creator']);

        try {
            $data = ['payment' => $payment];
            // Ensure view exists: resources/views/accounting/payments/receipt_pdf.blade.php
            $pdf = Pdf::loadView('accounting.payments.receipt_pdf', $data);
            $filename = 'payment-receipt-' . preg_replace('/[^A-Za-z0-9\-]/', '_', $payment->payment_number) . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error("Failed to generate PDF receipt for Payment {$payment->id}: " . $e->getMessage());
            return redirect()->route('accounting.payments.show', $payment)->with('error', 'Could not generate PDF receipt.');
        }
    }

    /**
     * Show a printer-friendly HTML receipt.
     */
    public function receipt(Payment $payment): View // Added return type
    {
        $this->authorizeSchoolAccess($payment);
        $payment->loadMissing(['contact', 'invoice', 'paymentMethod', 'account', 'creator']);
         // Ensure view exists: resources/views/accounting/payments/receipt.blade.php
        return view('accounting.payments.receipt', compact('payment'));
    }

    // --- HELPER METHODS ---

    /**
     * Create the journal entry for a *completed* payment.
     */
    private function createJournalForPayment(Payment $payment): ?Journal // Added return type hint
    {
        $payment->loadMissing(['contact', 'account', 'invoice']);

        if ($payment->status !== 'completed') {
             Log::warning("Journal creation skipped: Payment ID {$payment->id} status is '{$payment->status}'.");
             return null;
        }

        // --- Get Accounts ---
        $bankOrCashAccount = $payment->account;
        if (!$bankOrCashAccount) {
             Log::critical("Journal creation failed: Bank/Cash account ID {$payment->account_id} not found for Payment ID {$payment->id}.");
             throw new \Exception("Deposit account not found for Payment #{$payment->payment_number}.");
        }
        if (empty($bankOrCashAccount->is_bank_account) && empty($bankOrCashAccount->is_cash_account)) { // Check if flags exist and are true
             Log::warning("Journal creation warning: Account '{$bankOrCashAccount->name}' (ID: {$bankOrCashAccount->id}) used for Payment ID {$payment->id} is not marked as a bank or cash account.");
             // Allow proceeding but log warning. Could throw exception if stricter control is needed.
        }

        $arAccount = $this->findOrCreateArAccount($payment->school_id);
        if (!$arAccount) {
            // Exception is thrown within findOrCreateArAccount if it fails critically
            return null; // Should not be reached if findOrCreate throws
        }

        // --- Delete Existing Journal (if called during update) ---
         $existingJournal = $payment->journal()->first(); // Use relation method
         if ($existingJournal) {
             try {
                 $existingJournal->entries()->delete();
                 $existingJournal->delete();
                 $payment->journal_id = null;
             } catch (\Exception $e) {
                 Log::error("Error deleting existing journal (ID: {$existingJournal->id}) for Payment ID {$payment->id}: " . $e->getMessage());
                 // Consider re-throwing or handling differently if deletion failure is critical
             }
         }

        // --- Create New Journal Header ---
        $journal = Journal::create([
            'school_id' => $payment->school_id,
            'reference' => 'Payment #' . $payment->payment_number . ($payment->invoice ? ' / Inv #' . $payment->invoice->invoice_number : ''),
            'journal_date' => $payment->payment_date,
            'description' => 'Payment received from ' . ($payment->contact->name ?? 'Unknown Contact') . ($payment->reference ? ' Ref: '.$payment->reference : ''),
            'status' => 'posted',
            'created_by' => $payment->created_by ?? auth()->id(),
            'document_type' => Payment::class, // Use class constant for morph map safety
            'document_id' => $payment->id,
        ]);

        // --- Create Journal Entries ---
        $amount = (float) $payment->amount;
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $bankOrCashAccount->id, // Debit Bank/Cash
            'debit' => $amount,
            'credit' => 0,
            'description' => 'Deposit for Payment #' . $payment->payment_number,
            'contact_id' => $payment->contact_id,
        ]);

        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $arAccount->id, // Credit A/R
            'debit' => 0,
            'credit' => $amount,
            'description' => 'Payment applied from ' . ($payment->contact->name ?? 'Unknown Contact'),
            'contact_id' => $payment->contact_id,
        ]);

        // --- Link Journal to Payment & Save ---
        // Use updateColumn to avoid triggering observers/timestamps on Payment again
        // Ensure 'journal_id' is in $fillable if not using updateColumn
        $payment->updateQuietly(['journal_id' => $journal->id]);
        // $payment->journal_id = $journal->id;
        // $payment->saveQuietly();

        Log::info("Journal (ID: {$journal->id}) created successfully for Payment ID {$payment->id}.");
        return $journal;
    }

    /**
     * Helper to find or create the Accounts Receivable system account.
     */
     private function findOrCreateArAccount(?int $schoolId): ChartOfAccount // Added types
    {
        $arCode = config('accounting.ar_account_code', '1100');
        $hasCoASchoolId = Schema::hasColumn('chart_of_accounts', 'school_id');

        // Prioritize finding specific account for the school if applicable
        $query = ChartOfAccount::query();
        if ($schoolId && $hasCoASchoolId) {
            $query->where('school_id', $schoolId);
        } elseif ($hasCoASchoolId) {
            // If school ID exists but user is global/doesn't have one, look for global AR
             $query->whereNull('school_id');
        } // If CoA table doesn't have school_id, no school filtering needed

        $arAccount = $query->where('account_code', $arCode)
                           ->whereHas('accountType', fn($q) => $q->where('code', 'ASSET'))
                           ->first();

        if ($arAccount) return $arAccount;

        // Fallback: Try finding by NAME (scoped similarly to above)
        Log::warning("AR account code '{$arCode}' not found for school scope " . ($schoolId ?? 'Global') . ". Falling back to name search.");
         $queryByName = ChartOfAccount::query();
         if ($schoolId && $hasCoASchoolId) { $queryByName->where('school_id', $schoolId); }
         elseif ($hasCoASchoolId) { $queryByName->whereNull('school_id'); }

         $arAccount = $queryByName->where('name', 'LIKE', 'Accounts Receivable%')
                         ->whereHas('accountType', fn($q) => $q->where('code', 'ASSET'))
                         ->first();

         if ($arAccount) return $arAccount;

         // --- Create if not found ---
         Log::warning("AR account not found by code or name for school scope " . ($schoolId ?? 'Global') . ". Creating default AR account.");
         $assetType = AccountType::where('code', 'ASSET')->first();
         if (!$assetType) {
            Log::critical("AccountType 'ASSET' not found. Cannot create AR account.");
            throw new \Exception('Critical Setup Error: Asset Account Type not found.');
         }

         try {
             $createData = [
                'account_type_id' => $assetType->id,
                'account_code' => $arCode,
                'name' => 'Accounts Receivable',
                'description' => 'Default Accounts Receivable (System Created)',
                'is_active' => true,
                'is_system' => true,
             ];
             // Assign school_id only if the table supports it AND a schoolId was provided
             if ($schoolId && $hasCoASchoolId) {
                 $createData['school_id'] = $schoolId;
             } elseif ($hasCoASchoolId) {
                  $createData['school_id'] = null; // Explicitly set to null for global account
             }

             return ChartOfAccount::create($createData);
         } catch (\Exception $e) {
             Log::critical("Failed to create default AR account: " . $e->getMessage(), ['school_id' => $schoolId, 'code' => $arCode]);
             throw new \Exception("Failed to create required Accounts Receivable account.");
         }
    }

     /**
     * Helper to generate the next payment number.
     */
     private function generateNextPaymentNumber(?int $schoolId): string // Added types
     {
        $prefix = config('accounting.payment_prefix', 'PMT-') . date('Ymd') . '-';
        $padding = config('accounting.payment_number_padding', 4);
        $schoolSpecific = config('accounting.payment_school_specific_sequence', true); // Default to school specific
        $hasSchoolIdColumn = Schema::hasColumn('payments', 'school_id');

        $query = Payment::query();

        if ($schoolId && $hasSchoolIdColumn && $schoolSpecific) {
            $query->where('school_id', $schoolId);
        } elseif (!$schoolSpecific && $hasSchoolIdColumn) {
             // Look for global payments (null school_id) if sequence isn't school specific
             $query->whereNull('school_id');
        } // If no school_id column, or schoolSpecific is false and no schoolId provided, query all

        $query->where('payment_number', 'LIKE', $prefix . '%')
              ->orderBy('payment_number', 'desc');

        $lastPayment = $query->first(['payment_number']); // Select only needed column

        $nextNum = 1;
        if ($lastPayment) {
             $lastNumPart = substr($lastPayment->payment_number, strlen($prefix));
             if (ctype_digit($lastNumPart)) { // More robust check than is_numeric
                 $nextNum = intval($lastNumPart) + 1;
             } else {
                 Log::warning("Could not extract sequence number from last payment number.", ['payment_number' => $lastPayment->payment_number, 'prefix' => $prefix]);
                 // Attempt fallback (maybe remove prefix and take last digits?) - Cautious about this
             }
        }
        return $prefix . str_pad($nextNum, $padding, '0', STR_PAD_LEFT);
     }


    /**
     * Basic authorization check helper.
     */
    private function authorizeSchoolAccess($model): void // Added type hint
    {
        $userSchoolId = Auth::user()?->school_id;

        if (is_null($userSchoolId)) return; // Super Admin allow all

        if (property_exists($model, 'school_id') && $userSchoolId && $model->school_id !== $userSchoolId) {
             if(Schema::hasColumn($model->getTable(), 'school_id')) { // Check column exists
                Log::warning("Authorization failed: School ID mismatch.", [
                     'user_id' => Auth::id(), 'user_school_id' => $userSchoolId,
                     'model_class' => get_class($model), 'model_id' => $model->id,
                     'model_school_id' => $model->school_id
                 ]);
                abort(403, 'Unauthorized action. Record belongs to another school.');
             }
        }
    }

    /**
    * Helper to update invoice status based on amount paid.
    *
    * @param Invoice $invoice The invoice model instance (already loaded/updated)
    * @return void
    */
    private function updateInvoiceStatus(Invoice $invoice): void
    {
        $balance = (float)$invoice->total - (float)($invoice->amount_paid ?? 0);
        $tolerance = 0.01;

        if ($balance <= $tolerance) {
            $invoice->status = 'paid';
        } elseif ((float)($invoice->amount_paid ?? 0) > $tolerance) {
            $invoice->status = 'partial';
        } else {
            // If amount paid is zero (or negligible), revert to 'unpaid' or 'sent'
            // Keep existing status if it was already 'sent' or 'overdue' unless payment makes it partial/paid
            if (!in_array($invoice->status, ['draft', 'void'])) { // Don't change draft/void status by payment updates
                $invoice->status = 'unpaid'; // Or 'sent' depending on your workflow after approval
            }
        }
        // Note: The calling method should handle saving the invoice.
    }


} // End of PaymentsController class