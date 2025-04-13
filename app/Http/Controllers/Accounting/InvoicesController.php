<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
// Corrected: Use the actual Contact model namespace if it's not under Accounting
// Assuming it might be directly under App\Models
use App\Models\Accounting\Contact; // Or App\Models\Accounting\Contact if it lives there
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceItem;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\TaxRate;     // Assuming this model uses 'tax_rates' table
use App\Models\Accounting\AccountType;
// ADD Models needed for the NEW feature (Phase 2)
use App\Models\Accounting\FeeStructure;
use App\Models\Accounting\InventoryItem;
use App\Models\Student\AcademicYear; // Keep if used by FeeStructure

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;           // Ensure this Mailable exists: php artisan make:mail InvoiceMail --markdown=emails.invoices.mail
use Barryvdh\DomPDF\Facade\Pdf;     // Ensure package is installed: composer require barryvdh/laravel-dompdf
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Keep Rule for unique constraint in update

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
     * MODIFIED for Invoice Type Selection
     */
    public function create()
    {
        $school_id = Auth::user()?->school_id;

        // --- Data for Invoice Header ---
        $contacts = Contact::where('is_active', true)
             ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
             ->orderBy('name')->get(['id', 'name', 'contact_type']); // Include type if helpful
        $nextInvoiceNumber = $this->generateNextInvoiceNumber($school_id);

        // --- Data for Invoice Items (General) ---
        $taxRates = TaxRate::where('is_active', true)
             ->orderBy('name')->get(['id', 'name', 'rate']);
        $incomeAccounts = ChartOfAccount::whereHas('accountType', function($query) {
                $query->whereIn('code', ['INCOME', 'REVENUE']); // Common codes for income
            })
            ->where('is_active', true)
             // ->when($school_id, fn($q, $id) => $q->where('school_id', $id)) // Uncomment if CoA are school-specific
             ->orderBy('name')->get(['id', 'name', 'account_code']);

        // Fallback if no specific income accounts are found
        if ($incomeAccounts->isEmpty()) {
            Log::warning('No INCOME/REVENUE accounts found for invoice creation. Falling back to all active accounts.', ['school_id' => $school_id]);
            $incomeAccounts = ChartOfAccount::where('is_active', true)
                 // ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                 ->orderBy('name')->get(['id', 'name', 'account_code']);
        }

        // --- Data for Conditional Item Selection (NEW) ---
        // Fetch active fee structure items (assuming FeeStructureItem model exists and links to FeeStructure)
        // Adjust the query based on your FeeStructureItem model and relationships
        $feeItems = collect(); // Default to empty
        // Example: Fetching items related to *active* fee structures
        $activeFeeStructures = FeeStructure::where('is_active', true)
                                     ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                     ->pluck('id');
        if ($activeFeeStructures->isNotEmpty() && class_exists(\App\Models\Accounting\FeeStructureItem::class)) {
            $feeItems = \App\Models\Accounting\FeeStructureItem::whereIn('fee_structure_id', $activeFeeStructures)
                            ->with('incomeAccount') // Optional: Eager load default account
                            ->orderBy('name')
                            // Select fields needed for the dropdown and JS population
                            ->get(['id', 'name', 'description', 'amount', 'income_account_id']);
                            // Adjust 'amount' field name if different
        }


        // Fetch active inventory items
        $inventoryItems = InventoryItem::where('is_active', true)
             // ->when($school_id, fn($q, $id) => $q->where('school_id', $id)) // Add if inventory is school-specific
             ->orderBy('name')
             // Select fields needed for the dropdown and JS population
             ->get(['id', 'name', 'description', 'unit_cost', /* 'default_income_account_id' */]); // Add default account ID if your model has it

        return view('accounting.invoices.create', compact(
            'contacts',
            'taxRates',
            'incomeAccounts', // Still needed for the account dropdown in each row
            'nextInvoiceNumber',
            'feeItems',       // NEW: Pass fee items
            'inventoryItems'  // NEW: Pass inventory items
        ));
    }

    /**
     * Store a newly created resource in storage.
     * MODIFIED for Invoice Type
     */
    public function store(Request $request)
    {
         // Add invoice_type validation
         $validatedData = $request->validate([
            'contact_id' => 'required|exists:contacts,id', // Check contacts table directly
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'invoice_type' => 'required|in:fees,inventory', // NEW Validation
            'reference' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            // Item validation: Keep these general, but add source tracking
            'items.*.item_source_id' => 'nullable|integer', // ID of FeeStructureItem or InventoryItem
            'items.*.name' => 'required|string|max:255', // Name might be auto-filled but can be overridden
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0', // Added discount validation
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
        ], [
             'items.required' => 'At least one item line is required.',
             'items.*.description.required' => 'Each item must have a description.',
             'items.*.quantity.min' => 'Item quantity must be positive.',
             'items.*.account_id.required' => 'An income account must be selected for each item.',
             'invoice_type.required' => 'Please select an invoice type (Fees or Inventory).',
        ]);

        $schoolId = Auth::user()?->school_id;

        // --- Additional Validation (Optional but good practice) ---
         if ($validatedData['contact_id']) {
             $contactValid = Contact::where('id', $validatedData['contact_id'])
                                    ->when($schoolId, fn($q, $id) => $q->where('school_id', $id))
                                    ->exists();
             if (!$contactValid && $schoolId) {
                return redirect()->back()->withInput()->with('error', 'Selected contact is invalid or does not belong to your school.');
             }
         }
        // --- End Additional Validation ---

        DB::beginTransaction();
        try {
            // Pre-calculate totals for the main invoice record
            $totalSubtotal = 0;         // Subtotal = Sum of (Qty * Price) BEFORE discount
            $totalDiscountAmount = 0;   // Sum of discount amounts entered per line
            $totalTaxAmount = 0;        // Sum of tax calculated on subtotal AFTER discount
            $taxRatesData = TaxRate::whereIn('id', collect($validatedData['items'])->pluck('tax_rate_id')->filter())->pluck('rate', 'id');

            foreach ($validatedData['items'] as $item) {
                $quantity = (float)($item['quantity'] ?? 0);
                $price = (float)($item['unit_price'] ?? 0);
                $discount = (float)($item['discount'] ?? 0); // Use discount field
                $taxRateId = $item['tax_rate_id'] ?? null;

                $lineSubtotalBeforeDiscount = $quantity * $price;
                $lineSubtotalAfterDiscount = max(0, $lineSubtotalBeforeDiscount - $discount); // Apply discount here for tax calc
                $lineTaxAmount = ($taxRateId && isset($taxRatesData[$taxRateId])) ? ($lineSubtotalAfterDiscount * ($taxRatesData[$taxRateId] / 100)) : 0;

                $totalSubtotal += $lineSubtotalBeforeDiscount; // Sum of BASE subtotals (qty*price)
                $totalDiscountAmount += $discount; // Sum of discount amounts
                $totalTaxAmount += $lineTaxAmount; // Sum of tax calculated AFTER discount
            }
            // Grand total = (Sum of Qty*Price) - (Sum of Discounts) + (Sum of Tax)
            // Or simpler: Sum of (Line Subtotal After Discount + Line Tax)
            $totalAmount = ($totalSubtotal - $totalDiscountAmount) + $totalTaxAmount;

            $invoice = Invoice::create([
                'school_id' => $schoolId,
                'contact_id' => $validatedData['contact_id'],
                'invoice_number' => $validatedData['invoice_number'],
                'invoice_type' => $validatedData['invoice_type'], // NEW: Save invoice type
                'reference' => $validatedData['reference'],
                'issue_date' => $validatedData['issue_date'],
                'due_date' => $validatedData['due_date'],
                'notes' => $validatedData['notes'],
                'terms' => $validatedData['terms'],
                'subtotal' => $totalSubtotal,        // Store subtotal BEFORE discount
                'discount_amount' => $totalDiscountAmount, // Store total discount explicitly
                'tax_amount' => $totalTaxAmount,
                'total' => $totalAmount,            // Grand Total
                'amount_paid' => 0,
                'status' => 'draft',                // Always start as draft
                'created_by' => auth()->id(),
            ]);

            // Create Invoice Items
            foreach ($validatedData['items'] as $itemData) {
                $quantity = (float)($itemData['quantity'] ?? 0);
                $price = (float)($itemData['unit_price'] ?? 0);
                $discount = (float)($itemData['discount'] ?? 0);
                $taxRateId = $itemData['tax_rate_id'] ?? null;

                $lineSubtotalBeforeDiscount = $quantity * $price;
                $lineSubtotalAfterDiscount = max(0, $lineSubtotalBeforeDiscount - $discount);
                $lineTaxAmount = ($taxRateId && isset($taxRatesData[$taxRateId])) ? ($lineSubtotalAfterDiscount * ($taxRatesData[$taxRateId] / 100)) : 0;
                $lineTotal = $lineSubtotalAfterDiscount + $lineTaxAmount; // Line total is after discount, plus tax

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    // NEW: Store source if provided by the form
                    'item_source_id' => $itemData['item_source_id'] ?? null,
                    'item_source_type' => $itemData['item_source_id'] ? ($validatedData['invoice_type'] == 'fees' ? FeeStructureItem::class : InventoryItem::class) : null,
                    'name' => $itemData['name'], // Store item name (may differ from source)
                    'description' => $itemData['description'],
                    'quantity' => $quantity,
                    'unit_price' => $price, // Store original unit price
                    'discount' => $discount, // Store line item discount
                    'tax_rate_id' => $taxRateId,
                    'account_id' => $itemData['account_id'],
                    // Store calculated amounts for the line item (optional, but can be useful)
                    'tax_amount' => $lineTaxAmount,
                    'total' => $lineTotal, // Total for this specific line AFTER discount and tax
                ]);

                // --- Optional: Stock Adjustment for Inventory Items ---
                if ($validatedData['invoice_type'] == 'inventory' && !empty($itemData['item_source_id'])) {
                     $inventoryItem = InventoryItem::find($itemData['item_source_id']);
                     if ($inventoryItem /* && $inventoryItem->track_stock */) { // Add track_stock flag if needed
                         // Decrease stock
                          $inventoryItem->decrement('current_stock', $quantity);
                         // Optional: Log stock movement (using your StockMovement model if you have one)
                         if (class_exists(\App\Models\Accounting\StockMovement::class)) {
                              \App\Models\Accounting\StockMovement::create([
                                 'item_id' => $inventoryItem->id,
                                 'movement_type' => 'invoice_out', // Example type
                                 'quantity' => -$quantity, // Negative for outgoing
                                 'related_document_type' => Invoice::class,
                                 'related_document_id' => $invoice->id,
                                 'movement_date' => $invoice->issue_date,
                                 'notes' => "Invoice #{$invoice->invoice_number}",
                                 'created_by' => auth()->id(),
                             ]);
                         }
                     }
                 }
                 // --- End Stock Adjustment ---
            }

            DB::commit();
            Log::info("Invoice #{$invoice->invoice_number} created successfully.", ['invoice_id' => $invoice->id, 'user_id' => auth()->id()]);
            return redirect()->route('accounting.invoices.show', $invoice)
                ->with('success', 'Draft invoice created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // Validation errors are automatically handled by Laravel
            // Log::warning("Invoice validation failed.", ['errors' => $e->errors(), 'user_id' => auth()->id()]);
            throw $e; // Re-throw to let Laravel handle the redirect with errors
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating invoice: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->except(['_token', 'items']), // Log request data safely, maybe exclude items if large
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred while creating the invoice: ' . $e->getMessage());
        }
    }

    // --- Other Methods (show, edit, update, destroy, actions, helpers) ---
    // You'll need to:
    // 1. Add `invoice_type` to the `update` validation and logic if you allow editing it (usually not recommended after creation).
    // 2. Potentially adjust the `edit` view loading if you want to show the selected Fee/Inventory items.
    // 3. Ensure `createJournalForInvoice` correctly reflects the income split based on `invoice_items.account_id`. (Your current journal logic looks fine as it uses the item's account).
    // 4. Update `findOrCreateArAccount` and `findOrCreateTaxAccount` if they need to be school-specific (uncomment the school_id logic).
    // 5. Keep the existing helper methods (`generateNextInvoiceNumber`, `authorizeSchoolAccess`, etc.) as they are generally okay.

    // ... (Keep existing show, edit, update, destroy, approve, send, email, downloadPdf, printInvoice, voidInvoice methods) ...
    // ... (Make sure validation in update allows the correct invoice_type field) ...
    // ... (Keep existing helper methods: createJournalForInvoice, findOrCreateArAccount, findOrCreateTaxAccount, generateNextInvoiceNumber, authorizeSchoolAccess) ...

    // Example Snippet for Update Validation (adjust as needed)
    public function update(Request $request, Invoice $invoice)
    {
        // ... (authorization and status checks) ...

        $validatedData = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'invoice_number' => ['required','string', Rule::unique('invoices')->ignore($invoice->id)],
            // Usually invoice_type is NOT editable after creation, but if needed:
            // 'invoice_type' => 'required|in:fees,inventory',
            'reference' => 'nullable|string|max:255',
            // ... other header fields ...
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer', // For existing items
            'items.*.item_source_id' => 'nullable|integer',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        // ... (Recalculate totals logic - same as store) ...

        DB::beginTransaction();
        try {
            // ... (Update invoice header - include invoice_type if editable) ...
            $invoice->update([
                // ...,
                // 'invoice_type' => $validatedData['invoice_type'], // If editable
                'subtotal' => $totalSubtotal,
                'discount_amount' => $totalDiscountAmount,
                'tax_amount' => $totalTaxAmount,
                'total' => $totalAmount,
                // ...
            ]);

            // ... (Process items: update, create, delete - similar logic to original update) ...
            // When updating/creating items, also set item_source_id and item_source_type if provided
             foreach ($validatedData['items'] as $itemData) {
                  // ... calculation ...
                 $itemPayload = [
                     'name' => $itemData['name'],
                     'description' => $itemData['description'],
                     'quantity' => $quantity,
                     'unit_price' => $price,
                     'discount' => $discount,
                     'tax_rate_id' => $taxRateId,
                     'account_id' => $itemData['account_id'],
                     'item_source_id' => $itemData['item_source_id'] ?? null,
                     'item_source_type' => isset($itemData['item_source_id']) ? ($invoice->invoice_type == 'fees' ? FeeStructureItem::class : InventoryItem::class) : null,
                     'tax_amount' => $lineTaxAmount,
                     'total' => $lineTotal,
                 ];
                 // ... (Update or Create logic using $itemPayload) ...
             }
            // ... (Delete removed items logic) ...

            // Recreate journal if needed (e.g., if status changes or amounts change significantly)
            // If only draft is editable, journal is typically not created yet.
            // If allowing edits to Sent invoices (bad practice), handle journal reversal/update here.

            DB::commit();
             Log::info("Invoice #{$invoice->invoice_number} updated successfully.", ['invoice_id' => $invoice->id, 'user_id' => auth()->id()]);
            return redirect()->route('accounting.invoices.show', $invoice)->with('success', 'Invoice updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
             DB::rollBack(); throw $e;
        } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Error updating invoice {$invoice->id}: " . $e->getMessage(), [/*...*/]);
             return redirect()->back()->withInput()->with('error', 'An unexpected error occurred while updating the invoice: ' . $e->getMessage());
        }
    }


    // --- HELPER METHODS ---

    /**
     * Create or update the journal entry for an invoice.
     * Ensures idempotency - safe to call multiple times (e.g., on Send or manual Re-journal).
     */
    private function createJournalForInvoice(Invoice $invoice)
    {
        $invoice->loadMissing(['items.account', 'contact']); // Ensure needed relations are loaded

        // Find system accounts (ensure helpers return valid ChartOfAccount objects)
        $arAccount = $this->findOrCreateArAccount($invoice->school_id);
        if (!$arAccount) throw new \Exception("Accounts Receivable account could not be found or created.");

        $taxAccount = null; // Only needed if there's tax
        if ((float)$invoice->tax_amount > 0) {
            $taxAccount = $this->findOrCreateTaxAccount($invoice->school_id);
            if (!$taxAccount) throw new \Exception("Tax Payable account could not be found or created.");
        }


        DB::transaction(function () use ($invoice, $arAccount, $taxAccount) {
            // Delete existing journal if it exists, to prevent duplicates/errors on update
            if ($invoice->journal_id) {
                $existingJournal = Journal::find($invoice->journal_id);
                if ($existingJournal) {
                    Log::info("Deleting existing journal before recreating for invoice.", ['invoice_id' => $invoice->id, 'journal_id' => $invoice->journal_id]);
                    // It's safer to delete entries explicitly first in case of FK issues
                    $existingJournal->entries()->delete();
                    $existingJournal->delete();
                    $invoice->journal_id = null; // Explicitly nullify before creating new
                    // No need to save invoice here, saveQuietly will happen after linking new journal
                }
            }


            // Create the new Journal header
            $journal = Journal::create([
                'school_id' => $invoice->school_id,
                'journal_date' => $invoice->issue_date, // Use invoice issue date for journal
                'reference' => 'Invoice #' . $invoice->invoice_number,
                'description' => 'Sales Invoice to ' . ($invoice->contact?->name ?? 'N/A'),
                'status' => 'posted', // Journal is always posted when created here
                'created_by' => auth()->id() ?? $invoice->created_by, // Use current user or original creator
                'document_type' => Invoice::class, // Use morph class name
                'document_id' => $invoice->id,
             ]);

            $totalDebit = 0;
            $totalCredit = 0;
            $calculatedTaxCredit = 0; // Track calculated tax separately for verification

            // 1. Debit Accounts Receivable for the total amount
            $debitAmount = (float) $invoice->total;
            JournalEntry::create([
                 'journal_id' => $journal->id,
                 'account_id' => $arAccount->id,
                 'debit' => $debitAmount,
                 'credit' => 0,
                 'description' => 'Invoice #' . $invoice->invoice_number . ' - Total Due',
                 'contact_id' => $invoice->contact_id,
             ]);
            $totalDebit += $debitAmount;

             // 2. Credit Income/Revenue Accounts for each line item (subtotal after discount)
             foreach ($invoice->items as $item) {
                 // Recalculate item subtotal after discount for accuracy in journal
                 $itemSubtotalAfterDiscount = max(0, ((float)$item->quantity * (float)$item->unit_price) - (float)$item->discount);

                 if (!$item->account_id) {
                     Log::error("Journal entry skipped for Invoice Item ID {$item->id}: Missing account_id.", ['invoice_id' => $invoice->id]);
                     // THROWING EXCEPTION IS SAFER to prevent unbalanced/incomplete journals
                     throw new \Exception("Invoice item ID {$item->id} ('{$item->name}') is missing an account assignment. Cannot create journal.");
                 }
                 if ($itemSubtotalAfterDiscount > 0) { // Only credit if there's an amount after discount
                     JournalEntry::create([
                        'journal_id' => $journal->id,
                        'account_id' => $item->account_id, // Credit the account assigned to the item
                        'debit' => 0,
                        'credit' => $itemSubtotalAfterDiscount, // Credit income for the value AFTER discount
                        'description' => $item->name . ' - Inv #' . $invoice->invoice_number, // Simpler description
                        'contact_id' => $invoice->contact_id,
                     ]);
                     $totalCredit += $itemSubtotalAfterDiscount; // Accumulate credit from income
                 }

                 // Recalculate tax per line for verification (optional but good)
                  if ($item->tax_rate_id && $item->taxRate) { // Assuming relation taxRate exists
                        $lineTax = $itemSubtotalAfterDiscount * ($item->taxRate->rate / 100);
                        $calculatedTaxCredit += $lineTax;
                  }
             }

             // 3. Credit Tax Payable Account for the total tax amount stored on the invoice
             $invoiceTaxAmount = (float) $invoice->tax_amount;
             if ($invoiceTaxAmount > 0 && $taxAccount) { // Ensure tax account was found
                  JournalEntry::create([
                     'journal_id' => $journal->id,
                     'account_id' => $taxAccount->id,
                     'debit' => 0,
                     'credit' => $invoiceTaxAmount, // Credit Tax Payable
                     'description' => 'Sales Tax on Invoice #' . $invoice->invoice_number,
                     'contact_id' => $invoice->contact_id,
                 ]);
                 $totalCredit += $invoiceTaxAmount; // Add tax to total credits
             } elseif ($invoiceTaxAmount > 0 && !$taxAccount) {
                 // This case should have been caught earlier, but belt-and-suspenders
                 throw new \Exception("Invoice has tax amount but Tax Payable account was not found.");
             }

             // Final balance check (use a small tolerance for float comparison)
             $tolerance = 0.01; // e.g., 1 cent
             if (abs($totalDebit - $totalCredit) > $tolerance) {
                 Log::critical("Journal unbalanced for Invoice {$invoice->id}!", [
                     'debit' => $totalDebit,
                     'credit' => $totalCredit,
                     'invoice_total' => $invoice->total,
                     'calculated_tax' => $calculatedTaxCredit,
                     'invoice_tax' => $invoiceTaxAmount,
                     'journal_id' => $journal->id
                 ]);
                 // Rollback should happen via the DB::transaction throwing an exception
                 throw new \Exception("Journal entries are unbalanced. Debit: {$totalDebit}, Credit: {$totalCredit}. Cannot save invoice journal.");
             }

             // Link the journal to the invoice and save QUIETLY (to avoid triggering update events/loops)
             $invoice->journal_id = $journal->id;
             $invoice->saveQuietly();

             Log::info("Journal entry created/updated successfully for Invoice #{$invoice->invoice_number}.", ['invoice_id' => $invoice->id, 'journal_id' => $journal->id]);

        }); // End DB::transaction

        // Return the journal if needed (optional)
        // return Journal::find($invoice->journal_id); // Re-fetch if needed outside transaction
    }


    /**
     * Helper to find or create the Accounts Receivable system account.
     */
     private function findOrCreateArAccount($schoolId = null)
    {
        $arCode = config('accounting.ar_account_code', '1100');

        // Define the query conditions
        $conditions = function ($query) use ($arCode) {
            $query->where('account_code', $arCode)
                  ->whereHas('accountType', fn($q) => $q->where('code', 'ASSET'));
        };

        // Build the base query, potentially filtering by school if CoA has school_id
        $query = ChartOfAccount::query();
        // ** ADJUST THIS based on your CoA schema **
        // if ($schoolId && Schema::hasColumn('chart_of_accounts', 'school_id')) {
        //     $query->where(function ($q) use ($schoolId, $conditions) {
        //         $q->where('school_id', $schoolId)->where($conditions); // School-specific AR
        //     })->orWhere(function ($q) use ($conditions) {
        //         $q->whereNull('school_id')->where($conditions); // Global AR fallback
        //     });
        // } else {
             // Assume global AR if no schoolId provided or CoA table has no school_id column
             $query->where($conditions);
        // }

        $arAccount = $query->orderByRaw("CASE WHEN is_system = 1 THEN 0 ELSE 1 END") // Prioritize system accounts
                           ->orderByRaw("CASE WHEN school_id IS NOT NULL THEN 0 ELSE 1 END") // Prioritize school-specific if applicable
                           ->first();

        if ($arAccount) return $arAccount;

        // Fallback: Search by name (following same school/global logic as above)
        Log::warning("AR account with code '{$arCode}' not found. Falling back to search by name 'Accounts Receivable%'.", ['school_id' => $schoolId]);
        $conditionsByName = function ($query) {
            $query->where('name', 'LIKE', 'Accounts Receivable%')
                  ->whereHas('accountType', fn($q) => $q->where('code', 'ASSET'));
        };
        $queryByName = ChartOfAccount::query();
        // ** ADJUST THIS based on your CoA schema **
        // if ($schoolId && Schema::hasColumn('chart_of_accounts', 'school_id')) {
        //    $queryByName->where(function ($q) use ($schoolId, $conditionsByName) { /* school */ })
        //                ->orWhere(function ($q) use ($conditionsByName) { /* global */ });
        // } else {
             $queryByName->where($conditionsByName);
        // }
        $arAccount = $queryByName->orderByRaw("CASE WHEN is_system = 1 THEN 0 ELSE 1 END")
                                 ->orderByRaw("CASE WHEN school_id IS NOT NULL THEN 0 ELSE 1 END")
                                 ->first();
        if ($arAccount) return $arAccount;


        // --- Creation Logic ---
        Log::warning("Accounts Receivable account not found by code or name. Attempting to create default AR account.", ['code' => $arCode, 'school_id' => $schoolId]);
        $assetType = AccountType::where('code', 'ASSET')->first();
        if (!$assetType) {
             Log::critical("Cannot create AR account: 'ASSET' Account Type not found.");
             return null;
        }

        try {
            // Create it - Decide whether to make it global (null school_id) or school-specific
            $createData = [
                'account_type_id' => $assetType->id,
                'account_code' => $arCode,
                'name' => 'Accounts Receivable',
                'description' => 'Default Accounts Receivable (System Created)',
                'is_active' => true,
                'is_system' => true,
            ];
            // ** ADJUST THIS based on your CoA schema **
            // if ($schoolId && Schema::hasColumn('chart_of_accounts', 'school_id')) {
            //     $createData['school_id'] = $schoolId; // Create school-specific
            // } else {
                 $createData['school_id'] = null; // Create global
            // }
            return ChartOfAccount::create($createData);
        } catch (\Exception $e) {
            Log::critical("Failed to create default AR account: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper to find or create the Tax Payable system account.
     */
     private function findOrCreateTaxAccount($schoolId = null)
    {
        $taxCode = config('accounting.tax_account_code', '2300'); // e.g., Liability code

         // Define query conditions
         $conditions = function ($query) use ($taxCode) {
            $query->where('account_code', $taxCode)
                  ->whereHas('accountType', fn($q) => $q->where('code', 'LIABILITY')); // Tax payable is a liability
        };

         // Build base query with potential school filtering (mirror AR logic)
        $query = ChartOfAccount::query();
        // ** ADJUST THIS based on your CoA schema **
        // if ($schoolId && Schema::hasColumn('chart_of_accounts', 'school_id')) {
        //    $query->where(function ($q) use ($schoolId, $conditions) { /* school */ })
        //          ->orWhere(function ($q) use ($conditions) { /* global */ });
        // } else {
             $query->where($conditions);
        // }

        $taxAccount = $query->orderByRaw("CASE WHEN is_system = 1 THEN 0 ELSE 1 END")
                            ->orderByRaw("CASE WHEN school_id IS NOT NULL THEN 0 ELSE 1 END")
                            ->first();
        if ($taxAccount) return $taxAccount;


        // Fallback: Search by name
         Log::warning("Tax Payable account with code '{$taxCode}' not found. Falling back to search by name '%Tax Payable%'.", ['school_id' => $schoolId]);
         $conditionsByName = function ($query) {
             $query->where('name', 'LIKE', '%Tax Payable%') // Broader search for name
                   ->whereHas('accountType', fn($q) => $q->where('code', 'LIABILITY'));
         };
        $queryByName = ChartOfAccount::query();
        // ** ADJUST THIS based on your CoA schema **
        // if ($schoolId && Schema::hasColumn('chart_of_accounts', 'school_id')) {
        //    $queryByName->where(function ($q) use ($schoolId, $conditionsByName) { /* school */ })
        //                ->orWhere(function ($q) use ($conditionsByName) { /* global */ });
        // } else {
             $queryByName->where($conditionsByName);
        // }
         $taxAccount = $queryByName->orderByRaw("CASE WHEN is_system = 1 THEN 0 ELSE 1 END")
                                  ->orderByRaw("CASE WHEN school_id IS NOT NULL THEN 0 ELSE 1 END")
                                  ->first();
         if ($taxAccount) return $taxAccount;

         // --- Creation Logic ---
         Log::warning("Tax Payable account not found. Attempting to create default Tax Payable account.", ['code' => $taxCode, 'school_id' => $schoolId]);
         $liabilityType = AccountType::where('code', 'LIABILITY')->first();
         if (!$liabilityType) {
             Log::critical("Cannot create Tax Payable account: 'LIABILITY' Account Type not found.");
             return null;
         }

         try {
              $createData = [
                 'account_type_id' => $liabilityType->id,
                 'account_code' => $taxCode,
                 'name' => 'Tax Payable',
                 'description' => 'Default Tax Payable (System Created)',
                 'is_active' => true,
                 'is_system' => true,
             ];
             // ** ADJUST THIS based on your CoA schema **
             // if ($schoolId && Schema::hasColumn('chart_of_accounts', 'school_id')) {
             //     $createData['school_id'] = $schoolId;
             // } else {
                  $createData['school_id'] = null;
             // }
             return ChartOfAccount::create($createData);
         } catch (\Exception $e) {
             Log::critical("Failed to create default Tax Payable account: " . $e->getMessage());
             return null;
         }
    }

    /**
     * Generate the next invoice number based on a pattern.
     * Example: INV-YYYYMMDD-NNN
     */
    private function generateNextInvoiceNumber($schoolId = null)
    {
        $prefix = config('accounting.invoice_prefix', 'INV-');
        $useDate = config('accounting.invoice_prefix_date', true); // Add config option
        $padding = config('accounting.invoice_number_padding', 3);

        $currentPrefix = $prefix . ($useDate ? date('Ymd') . '-' : '');

        // Base query - Filter by the full prefix generated
        $query = Invoice::where('invoice_number', 'LIKE', $currentPrefix . '%');

        // Add school filter *if* sequences are school-specific and schoolId is provided
        // ** ADJUST THIS based on your schema/requirements **
        // if ($schoolId && Schema::hasColumn('invoices', 'school_id')) {
        //     $query->where('school_id', $schoolId);
        // }

        // Find the latest invoice number *within the current prefix*
        $lastInvoice = $query->orderBy('invoice_number', 'desc')
                            //->lockForUpdate() // Use locking if high concurrency is expected
                            ->first();

        $nextNum = 1;
        if ($lastInvoice) {
            // Extract the numeric part *after* the $currentPrefix
            $lastNumPart = substr($lastInvoice->invoice_number, strlen($currentPrefix));
            if (is_numeric($lastNumPart)) {
                $nextNum = intval($lastNumPart) + 1;
            } else {
                Log::warning("Could not extract numeric sequence from last invoice number matching prefix.", [
                    'last_invoice_number' => $lastInvoice->invoice_number,
                    'prefix' => $currentPrefix
                ]);
                // Attempt extraction after the last hyphen as a fallback
                $lastHyphenPos = strrpos($lastInvoice->invoice_number, '-');
                if ($lastHyphenPos !== false) {
                    $fallbackNumPart = substr($lastInvoice->invoice_number, $lastHyphenPos + 1);
                     if (is_numeric($fallbackNumPart)) {
                        $nextNum = intval($fallbackNumPart) + 1;
                        Log::info("Used fallback numeric extraction for invoice number.", ['last_invoice_number' => $lastInvoice->invoice_number]);
                     }
                }
            }
        }

        return $currentPrefix . str_pad($nextNum, $padding, '0', STR_PAD_LEFT);
    }


     /**
     * Basic authorization check helper.
     */
    private function authorizeSchoolAccess($model)
    {
        if (!Auth::check()) {
            abort(401); // Should be caught by middleware
        }
        $userSchoolId = Auth::user()?->school_id;
        // Allow access if user is superadmin (no school_id) OR model matches user's school
        if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
             Log::warning('Authorization failed: User school ID mismatch.', [
                 'user_id' => Auth::id(), 'user_school_id' => $userSchoolId,
                 'model' => get_class($model), 'model_id' => $model->id ?? 'N/A', 'model_school_id' => $model->school_id
             ]);
            abort(403, 'Unauthorized action.');
        }
    }

} // End of InvoicesController class