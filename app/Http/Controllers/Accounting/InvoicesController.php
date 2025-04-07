<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Contact;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceItem;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\TaxRate;
use App\Models\Accounting\AccountType; // Need this for AR/Tax account creation fallback
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // For logging errors
use Illuminate\Support\Facades\Mail; // For sending email
use App\Mail\InvoiceMail;           // Your Mailable class (create if not exists)
use Barryvdh\DomPDF\Facade\Pdf;     // Assuming you use laravel-dompdf
use Illuminate\Support\Facades\Auth; // Make sure Auth is imported

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        $invoices = Invoice::with('contact')
             ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
             ->orderBy('issue_date', 'desc')->orderBy('id', 'desc')->get();
        return view('accounting.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $school_id = Auth::user()?->school_id;
        $contacts = Contact::where('is_active', true)
             ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
             ->orderBy('name')->get(['id', 'name']);
        $taxRates = TaxRate::where('is_active', true)
             // ->when($school_id, fn($q, $id) => $q->where('school_id', $id)) // Tax rates might be global
             ->orderBy('name')->get(['id', 'name', 'rate']);

        $accounts = ChartOfAccount::whereHas('accountType', function($query) {
                $query->whereIn('code', ['INCOME', 'REVENUE']);
            })
            ->where('is_active', true)
             // ->when($school_id, fn($q, $id) => $q->where('school_id', $id)) // CoA might be global or school specific
             ->orderBy('name')->get(['id', 'name', 'account_code']);

        if ($accounts->isEmpty()) {
            Log::warning('No INCOME/REVENUE accounts found. Falling back to all active accounts for invoice creation.');
            $accounts = ChartOfAccount::where('is_active', true)
                 // ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                 ->orderBy('name')->get(['id', 'name', 'account_code']);
        }

        $nextInvoiceNumber = $this->generateNextInvoiceNumber($school_id); // Pass schoolId if prefix needed

        return view('accounting.invoices.create', compact('contacts', 'taxRates', 'accounts', 'nextInvoiceNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $validatedData = $request->validate([ // Use validated data directly
            'contact_id' => 'required|exists:accounting_contacts,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'reference' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        $schoolId = Auth::user()?->school_id;

        // --- Additional Validation ---
         if ($validatedData['contact_id']) {
             $contactValid = Contact::where('id', $validatedData['contact_id'])
                                    ->when($schoolId, fn($q, $id) => $q->where('school_id', $id))
                                    ->exists();
             if (!$contactValid) return redirect()->back()->withInput()->with('error', 'Selected contact is invalid or does not belong to your school.');
         }
        // --- End Additional Validation ---

        DB::beginTransaction();
        try {
            $totalSubtotal = 0; $totalTaxAmount = 0;
            $taxRatesData = TaxRate::whereIn('id', collect($validatedData['items'])->pluck('tax_rate_id')->filter())->pluck('rate', 'id');

            foreach ($validatedData['items'] as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $lineTaxAmount = (!empty($item['tax_rate_id']) && isset($taxRatesData[$item['tax_rate_id']])) ? ($lineSubtotal * ($taxRatesData[$item['tax_rate_id']] / 100)) : 0;
                $totalSubtotal += $lineSubtotal;
                $totalTaxAmount += $lineTaxAmount;
            }
            $totalAmount = $totalSubtotal + $totalTaxAmount;

            $invoice = Invoice::create([
                'school_id' => $schoolId,
                'contact_id' => $validatedData['contact_id'],
                'invoice_number' => $validatedData['invoice_number'],
                'reference' => $validatedData['reference'],
                'issue_date' => $validatedData['issue_date'],
                'due_date' => $validatedData['due_date'],
                'notes' => $validatedData['notes'],
                'terms' => $validatedData['terms'],
                'subtotal' => $totalSubtotal,
                'tax_amount' => $totalTaxAmount,
                'total' => $totalAmount,
                'amount_paid' => 0,
                'status' => 'draft', // Always start draft
                'created_by' => auth()->id(),
            ]);

            foreach ($validatedData['items'] as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $lineTaxAmount = (!empty($item['tax_rate_id']) && isset($taxRatesData[$item['tax_rate_id']])) ? ($lineSubtotal * ($taxRatesData[$item['tax_rate_id']] / 100)) : 0;
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate_id' => $item['tax_rate_id'] ?? null,
                    'account_id' => $item['account_id'],
                    'subtotal' => $lineSubtotal,
                    'tax_amount' => $lineTaxAmount,
                ]);
            }

            DB::commit();
            return redirect()->route('accounting.invoices.show', $invoice)
                ->with('success', 'Draft invoice created successfully.');
        } catch (\Exception $e) { DB::rollBack(); Log::error("Error creating invoice: " . $e->getMessage() . "\n" . $e->getTraceAsString()); return redirect()->back()->withInput()->with('error', 'Error creating invoice: ' . $e->getMessage()); }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $this->authorizeSchoolAccess($invoice); // Example authorization
        $invoice->load(['contact', 'items.account', 'items.taxRate', 'creator', 'payments.paymentMethod', 'journal']);
        return view('accounting.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
         $this->authorizeSchoolAccess($invoice); // Example authorization
        if ($invoice->status !== 'draft') {
            return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Only draft invoices can be edited.');
        }
        $invoice->load(['items']);
        $school_id = Auth::user()?->school_id;
        $contacts = Contact::where('is_active', true)->when($school_id, fn($q, $id)=>$q->where('school_id',$id))->orderBy('name')->get(['id', 'name']);
        $taxRates = TaxRate::where('is_active', true)->orderBy('name')->get(['id', 'name', 'rate']);
        $accounts = ChartOfAccount::whereHas('accountType', fn($q)=>$q->whereIn('code', ['INCOME', 'REVENUE']))
            ->where('is_active', true)->orderBy('name')->get(['id', 'name', 'account_code']);
        if ($accounts->isEmpty()) {
             Log::warning('No INCOME/REVENUE accounts found during edit. Falling back to all active.');
             $accounts = ChartOfAccount::where('is_active', true)->orderBy('name')->get(['id', 'name', 'account_code']);
        }
        $nextInvoiceNumber = $invoice->invoice_number; // Use existing number
        return view('accounting.invoices.edit', compact('invoice', 'contacts', 'taxRates', 'accounts', 'nextInvoiceNumber'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
         $this->authorizeSchoolAccess($invoice); // Example authorization
        if ($invoice->status !== 'draft') {
            return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Only draft invoices can be edited.');
        }

        $validatedData = $request->validate([ /* ... validation rules as before ... */
            'contact_id' => 'required|exists:accounting_contacts,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number,' . $invoice->id,
            'reference' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        $schoolId = Auth::user()?->school_id;
         // --- Additional Validation ---
         if ($validatedData['contact_id']) {
            $contactValid = Contact::where('id', $validatedData['contact_id'])->when($schoolId, fn($q, $id) => $q->where('school_id', $id))->exists();
            if (!$contactValid) return redirect()->back()->withInput()->with('error', 'Selected contact is invalid or does not belong to your school.');
         }
        // --- End Additional Validation ---

        DB::beginTransaction();
        try {
            $totalSubtotal = 0; $totalTaxAmount = 0;
            $taxRatesData = TaxRate::whereIn('id', collect($validatedData['items'])->pluck('tax_rate_id')->filter())->pluck('rate', 'id');

            foreach ($validatedData['items'] as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $lineTaxAmount = (!empty($item['tax_rate_id']) && isset($taxRatesData[$item['tax_rate_id']])) ? ($lineSubtotal * ($taxRatesData[$item['tax_rate_id']] / 100)) : 0;
                $totalSubtotal += $lineSubtotal;
                $totalTaxAmount += $lineTaxAmount;
            }
            $totalAmount = $totalSubtotal + $totalTaxAmount;

            $invoice->update([
                'contact_id' => $validatedData['contact_id'],
                'invoice_number' => $validatedData['invoice_number'],
                'reference' => $validatedData['reference'],
                'issue_date' => $validatedData['issue_date'],
                'due_date' => $validatedData['due_date'],
                'notes' => $validatedData['notes'],
                'terms' => $validatedData['terms'],
                'subtotal' => $totalSubtotal,
                'tax_amount' => $totalTaxAmount,
                'total' => $totalAmount,
            ]);

            $existingItemIds = $invoice->items->pluck('id')->toArray();
            $updatedItemIds = [];

            foreach ($validatedData['items'] as $itemData) {
                 $itemId = $itemData['id'] ?? null;
                 $lineSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                 $lineTaxAmount = (!empty($itemData['tax_rate_id']) && isset($taxRatesData[$itemData['tax_rate_id']])) ? ($lineSubtotal * ($taxRatesData[$itemData['tax_rate_id']] / 100)) : 0;
                 $itemPayload = [ /* ... description, quantity etc. ... */
                     'description' => $itemData['description'],'quantity' => $itemData['quantity'],'unit_price' => $itemData['unit_price'],
                     'tax_rate_id' => $itemData['tax_rate_id'] ?? null,'account_id' => $itemData['account_id'],
                     'subtotal' => $lineSubtotal,'tax_amount' => $lineTaxAmount,
                 ];
                 if ($itemId && in_array($itemId, $existingItemIds)) {
                    InvoiceItem::find($itemId)?->update($itemPayload); $updatedItemIds[] = $itemId;
                 } else { $newItem = $invoice->items()->create($itemPayload); $updatedItemIds[] = $newItem->id; }
            }

            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) InvoiceItem::destroy($itemsToDelete);

            if ($invoice->journal) { $invoice->journal->entries()->delete(); $invoice->journal->delete(); $invoice->journal_id = null; $invoice->save(); }

            DB::commit();
            return redirect()->route('accounting.invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) { DB::rollBack(); Log::error("Error updating invoice {$invoice->id}: " . $e->getMessage() . "\n" . $e->getTraceAsString()); return redirect()->back()->withInput()->with('error', 'Error updating invoice: ' . $e->getMessage()); }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorizeSchoolAccess($invoice); // Example authorization
        if ($invoice->status !== 'draft') { return redirect()->route('accounting.invoices.index')->with('error', 'Only draft invoices can be deleted.'); }
        if ($invoice->payments()->exists()) { return redirect()->route('accounting.invoices.index')->with('error', 'Invoice cannot be deleted because it has payments associated.'); }

        DB::beginTransaction();
        try {
            $invoiceNumber = $invoice->invoice_number;
            if ($invoice->journal) { $invoice->journal->entries()->delete(); $invoice->journal->delete(); } // Should be rare for draft
            $invoice->items()->delete();
            $invoice->delete();
            DB::commit();
            return redirect()->route('accounting.invoices.index')->with('success', "Draft Invoice #{$invoiceNumber} deleted successfully.");
        } catch (\Exception $e) { DB::rollBack(); Log::error("Error deleting invoice {$invoice->id}: " . $e->getMessage()); return redirect()->route('accounting.invoices.index')->with('error', 'Error deleting invoice: ' . $e->getMessage()); }
    }

    // --- NEW ACTION METHODS ---

    /**
     * Approve a draft invoice.
     */
    public function approve(Invoice $invoice)
    {
        $this->authorizeSchoolAccess($invoice);
        if ($invoice->status !== 'draft') {
             return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Only draft invoices can be approved.');
        }
        try {
            $invoice->update(['status' => 'approved']);
            return redirect()->route('accounting.invoices.show', $invoice)->with('success', 'Invoice approved successfully. Ready to be sent.');
        } catch (\Exception $e) { Log::error("Error approving invoice {$invoice->id}: " . $e->getMessage()); return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Error approving invoice: ' . $e->getMessage()); }
    }

    /**
     * Mark an invoice as sent (and create journal entry).
     */
    public function send(Invoice $invoice)
    {
        $this->authorizeSchoolAccess($invoice);
        if (!in_array($invoice->status, ['draft', 'approved'])) {
            return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Only draft or approved invoices can be marked as sent.');
        }
        DB::beginTransaction();
        try {
            $this->createJournalForInvoice($invoice); // Creates/updates journal & saves invoice with journal_id
            $invoice->update(['status' => 'sent']); // Update status after journal
            DB::commit();
            return redirect()->route('accounting.invoices.show', $invoice)->with('success', 'Invoice marked as sent and journal entry created.');
        } catch (\Exception $e) { DB::rollBack(); Log::error("Error marking invoice {$invoice->id} as sent: " . $e->getMessage() . "\n" . $e->getTraceAsString()); return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Error marking invoice as sent: ' . $e->getMessage()); }
    }

     /**
     * Email the invoice to the contact.
     */
    public function emailInvoice(Invoice $invoice)
    {
        $this->authorizeSchoolAccess($invoice);
        if (!$invoice->contact?->email) { return redirect()->back()->with('error', 'Contact email address is missing.'); }
        if (in_array($invoice->status, ['draft'])) { return redirect()->back()->with('error', 'Draft invoices cannot be emailed.'); }

        try {
            $invoice->loadMissing(['contact', 'items']);
            Mail::to($invoice->contact->email)->send(new InvoiceMail($invoice));
            // Maybe update status here if emailing implies sending for the first time?
            // if ($invoice->status == 'approved') { $invoice->update(['status' => 'sent']); }
            return redirect()->back()->with('success', 'Invoice emailed successfully to ' . $invoice->contact->email);
        } catch (\Exception $e) { Log::error("Failed to email invoice {$invoice->id}: " . $e->getMessage()); return redirect()->back()->with('error', 'Failed to send invoice email.'); }
    }

    /**
     * Download the invoice as a PDF.
     */
    public function downloadPdf(Invoice $invoice)
    {
         $this->authorizeSchoolAccess($invoice);
        // if ($invoice->status == 'draft') { return redirect()->back()->with('error', 'Draft invoices cannot be downloaded.'); } // Optional check

        try {
            $invoice->loadMissing(['contact', 'items.taxRate', 'items.account']);
            $data = ['invoice' => $invoice];
            $pdf = Pdf::loadView('accounting.invoices.pdf', $data);
            $filename = 'invoice-' . preg_replace('/[^A-Za-z0-9\-]/', '', $invoice->invoice_number) . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) { Log::error("Failed to generate PDF for invoice {$invoice->id}: " . $e->getMessage()); return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Could not generate PDF.'); }
    }


    /**
     * Display a printer-friendly version of the invoice.
     */
    public function printInvoice(Invoice $invoice)
    {
        $this->authorizeSchoolAccess($invoice);
        $invoice->load(['contact', 'items.account', 'items.taxRate', 'creator']);
        return view('accounting.invoices.print', compact('invoice'));
    }


    /**
     * Void an existing invoice.
     */
    public function voidInvoice(Invoice $invoice)
    {
        $this->authorizeSchoolAccess($invoice);
        $voidableStatuses = ['approved', 'sent', 'partial', 'overdue'];
        if (!in_array($invoice->status, $voidableStatuses)) { return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Only approved, sent, partial, or overdue invoices can be voided.'); }
        if ($invoice->amount_paid > 0) { return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Cannot void an invoice with payments. Consider a Credit Note.'); }

        DB::beginTransaction();
        try {
            if ($invoice->journal) {
                // TODO: Proper reversal logic (create reversing journal)
                Log::warning("Voiding invoice {$invoice->id}: Deleting original journal {$invoice->journal->id}. Proper reversal recommended.");
                $invoice->journal->entries()->delete(); $invoice->journal->delete(); $invoice->journal_id = null;
            }
            $invoice->status = 'void'; $invoice->save();
            DB::commit();
            return redirect()->route('accounting.invoices.show', $invoice)->with('success', 'Invoice has been voided successfully.');
        } catch (\Exception $e) { DB::rollBack(); Log::error("Error voiding invoice {$invoice->id}: " . $e->getMessage()); return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Error voiding invoice: ' . $e->getMessage()); }
    }


    // --- HELPER METHODS ---

    /**
     * Create or update the journal entry for an invoice.
     */
    private function createJournalForInvoice(Invoice $invoice)
    {
        // This method remains unchanged (with AR/Tax lookup helpers below)
        $invoice->loadMissing('items.account');
        if ($invoice->journal) { $invoice->journal->entries()->delete(); $invoice->journal->delete(); $invoice->journal_id = null; }
        $arAccount = $this->findOrCreateArAccount($invoice->school_id);
        $journal = Journal::create([ /* ... journal details ... */
            'school_id' => $invoice->school_id, 'reference' => 'Invoice #' . $invoice->invoice_number, 'journal_date' => $invoice->issue_date,
            'description' => 'Sales Invoice to ' . ($invoice->contact->name ?? 'N/A'), 'status' => 'posted',
            'created_by' => auth()->id() ?? $invoice->created_by, 'document_type' => 'invoice', 'document_id' => $invoice->id,
         ]);
        $totalDebit = 0; $totalCredit = 0;
        JournalEntry::create([ /* Debit AR */
             'journal_id' => $journal->id, 'account_id' => $arAccount->id, 'debit' => $invoice->total, 'credit' => 0,
             'description' => 'Invoice #' . $invoice->invoice_number . ' - Total', 'contact_id' => $invoice->contact_id,
         ]); $totalDebit += $invoice->total;
         foreach ($invoice->items as $item) { /* ... Credit Income ... */
            if (!$item->account) { Log::error("Journal skipped for item ID {$item->id}: Missing account."); continue; }
            $creditAmount = $item->subtotal;
            JournalEntry::create(['journal_id' => $journal->id, 'account_id' => $item->account_id, 'debit' => 0, 'credit' => $creditAmount,
                'description' => $item->description . ' (Inv #' . $invoice->invoice_number . ')', 'contact_id' => $invoice->contact_id, ]);
            $totalCredit += $creditAmount;
         }
         if ($invoice->tax_amount > 0) { /* ... Credit Tax Payable ... */
             $taxAccount = $this->findOrCreateTaxAccount($invoice->school_id);
             JournalEntry::create(['journal_id' => $journal->id, 'account_id' => $taxAccount->id, 'debit' => 0, 'credit' => $invoice->tax_amount,
                 'description' => 'Sales Tax on Invoice #' . $invoice->invoice_number, 'contact_id' => $invoice->contact_id, ]);
             $totalCredit += $invoice->tax_amount;
         }
         if (abs($totalDebit - $totalCredit) > 0.001) { Log::error("Journal unbalanced for Invoice {$invoice->id}."); throw new \Exception("Journal entries unbalanced."); }
         $invoice->journal_id = $journal->id; $invoice->saveQuietly();
         return $journal;
    }

    /**
     * Helper to find or create the Accounts Receivable system account.
     */
    private function findOrCreateArAccount($schoolId) // Note: $schoolId parameter removed from queries
    {
        $arCode = config('accounting.ar_account_code', '1100');
        // Query WITHOUT school_id filter directly on chart_of_accounts
        $arAccount = ChartOfAccount::where('account_code', $arCode)
            ->whereHas('accountType', fn($q) => $q->where('code', 'ASSET'))
            ->first();
        if ($arAccount) return $arAccount;
        // Fallback: Search by name WITHOUT school_id filter
         $arAccount = ChartOfAccount::where('name', 'LIKE', 'Accounts Receivable%')
            ->whereHas('accountType', fn($q) => $q->where('code', 'ASSET'))
            ->first();
        if ($arAccount) return $arAccount;
        Log::warning("AR account not found. Creating default global AR (Code: {$arCode}).");
        $assetType = AccountType::where('code', 'ASSET')->first();
        if (!$assetType) throw new \Exception('Asset Account Type not found.');
        // Create WITHOUT school_id
        return ChartOfAccount::create([
            // 'school_id' => $schoolId, // REMOVED
            'account_type_id' => $assetType->id, 'account_code' => $arCode, 'name' => 'Accounts Receivable',
            'description' => 'Default Accounts Receivable (System Created)', 'is_active' => true, 'is_system' => true,
        ]);
    } // Added missing brace

     /**
     * Helper to find or create the Tax Payable system account.
     */
     private function findOrCreateTaxAccount($schoolId) // Note: $schoolId parameter removed from queries
    {
        $taxCode = config('accounting.tax_account_code', '2300');
        // Query WITHOUT school_id filter directly on chart_of_accounts
        $taxAccount = ChartOfAccount::where('account_code', $taxCode)
             ->whereHas('accountType', fn($q) => $q->where('code', 'LIABILITY'))
             ->first();
        if ($taxAccount) return $taxAccount;
        // Fallback: Search by name WITHOUT school_id filter
        $taxAccount = ChartOfAccount::where('name', 'LIKE', '%Tax Payable%')
             ->whereHas('accountType', fn($q) => $q->where('code', 'LIABILITY'))
             ->first();
        if ($taxAccount) return $taxAccount;
        Log::warning("Tax Payable account not found. Creating default global Tax account (Code: {$taxCode}).");
        $liabilityType = AccountType::where('code', 'LIABILITY')->first();
        if (!$liabilityType) throw new \Exception('Liability Account Type not found.');
        // Create WITHOUT school_id
        return ChartOfAccount::create([
             // 'school_id' => $schoolId, // REMOVED
            'account_type_id' => $liabilityType->id, 'account_code' => $taxCode, 'name' => 'Tax Payable',
            'description' => 'Default Tax Payable (System Created)', 'is_active' => true, 'is_system' => true,
        ]);
    } // Added missing brace


    /**
     * Generate the next invoice number.
     */
    private function generateNextInvoiceNumber($schoolId = null) // Accept optional schoolId
    {
        // Example: INV-YYYYMMDD-NNNN (Consider school-specific prefix if needed)
        $prefix = 'INV-' . date('Ymd') . '-';
        $query = Invoice::where('invoice_number', 'LIKE', $prefix . '%')
                      // ->when($schoolId, fn($q, $id) => $q->where('school_id', $id)) // Add if using school-specific sequences
                      ->orderBy('invoice_number', 'desc');

        $lastInvoice = $query->first();

        $nextNum = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastNumPart = end($parts);
            if (is_numeric($lastNumPart)) {
                $nextNum = intval($lastNumPart) + 1;
            }
        }
        return $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT); // Pad to 3 digits (e.g., 001)
    } // <--- ***** ADDED MISSING BRACE HERE *****


     /**
     * Basic authorization check helper (Example - Implement proper Policies/Gates)
     */
    private function authorizeSchoolAccess($model)
    {
        $userSchoolId = Auth::user()?->school_id;
        // Check if the model has a school_id and if it matches the user's school_id
        if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
            abort(403, 'Unauthorized action.'); // Or redirect with error
        }
        // Allow access if user has no school_id (maybe super admin?) or model has no school_id
        // Add more granular permission checks here based on roles if needed
    } // <--- Added missing brace here

} // End of InvoicesController class - Ensure this is the VERY LAST brace