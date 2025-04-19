<?php

namespace App\Http\Controllers\Accounting;

// Base Controller & Core Laravel Classes
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse; // Used for redirecting responses
use Illuminate\Http\Response;         // Used for downloadPdf return type
use Illuminate\Contracts\View\View;     // Used for view return type hints

// Facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;     // Keep if you plan to implement email sending
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;         // For PDF generation

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
use App\Models\Accounting\StockMovement;   // Added: Used conditionally in store method
use App\Models\Accounting\TaxRate;

// Application Models (Other - Keep if needed by FeeStructure or other logic)
use App\Models\Student\AcademicYear;

// Mailables (Keep if you plan to implement email sending)
use App\Mail\InvoiceMail;

// --- The rest of your controller class starts below ---
class InvoicesController extends Controller
{
    // Methods are now correctly placed INSIDE the class block

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $school_id = Auth::user()?->school_id;
        $invoices = Invoice::with('contact')
             ->when($school_id && Schema::hasColumn('invoices', 'school_id'), fn($q, $id) => $q->where('school_id', $id))
             ->orderBy('issue_date', 'desc')->orderBy('id', 'desc')->get();
        return view('accounting.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     * MODIFIED for Invoice Type Selection
     */
    public function create(): View
    {
        $school_id = Auth::user()?->school_id;

        // --- Data for Invoice Header ---
        $contacts = Contact::where('is_active', true)
             ->when($school_id && Schema::hasColumn('contacts', 'school_id'), fn($q, $id) => $q->where('school_id', $id))
             ->orderBy('name')->get(['id', 'name', 'contact_type']);
        $nextInvoiceNumber = $this->generateNextInvoiceNumber($school_id);

        // --- Data for Invoice Items (General) ---
        $taxRates = TaxRate::where('is_active', true)
             ->orderBy('name')->get(['id', 'name', 'rate']);
        $incomeAccounts = ChartOfAccount::whereHas('accountType', function($query) {
                $query->whereIn('code', ['INCOME', 'REVENUE']);
            })
            ->where('is_active', true)
             ->when($school_id && Schema::hasColumn('chart_of_accounts', 'school_id'), fn($q, $id) => $q->where('school_id', $id)) // Consider re-enabling if needed
             ->orderBy('name')->get(['id', 'name', 'account_code']);

        if ($incomeAccounts->isEmpty()) { // Fallback
            Log::warning('No INCOME/REVENUE accounts found for invoice creation. Falling back to all active accounts.', ['school_id' => $school_id]);
            $incomeAccounts = ChartOfAccount::where('is_active', true)
                 ->when($school_id && Schema::hasColumn('chart_of_accounts', 'school_id'), fn($q, $id) => $q->where('school_id', $id)) // Consider re-enabling if needed
                 ->orderBy('name')->get(['id', 'name', 'account_code']);
        }

        // --- Data for Conditional Item Selection (NEW) ---
        $feeItems = collect();
        $activeFeeStructures = collect(); // Initialize
        if (class_exists(FeeStructure::class) && Schema::hasTable('fee_structures') && Schema::hasColumn('fee_structures', 'school_id')) {
            $activeFeeStructures = FeeStructure::where('is_active', true)
                                        ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                        ->pluck('id');
        }

        if ($activeFeeStructures->isNotEmpty() && class_exists(FeeStructureItem::class) && Schema::hasTable('fee_structure_items')) {
            $feeItems = FeeStructureItem::whereIn('fee_structure_id', $activeFeeStructures)
                            ->with('incomeAccount') // Eager load if needed in view
                            ->orderBy('name')
                            ->get(['id', 'name', 'description', 'amount', 'income_account_id']);
        } else if ($activeFeeStructures->isNotEmpty()) {
             Log::warning('Fee structures found, but FeeStructureItem class (' . FeeStructureItem::class . ') or table does not exist or is not imported correctly.');
        }

        $inventoryItems = collect(); // Initialize
        if (class_exists(InventoryItem::class) && Schema::hasTable('inventory_items') && Schema::hasColumn('inventory_items', 'school_id')) {
            $inventoryItems = InventoryItem::where('is_active', true)
                ->when($school_id, fn($q, $id) => $q->where('school_id', $id)) // Consider re-enabling if needed
                ->orderBy('name')
                ->get(['id', 'name', 'description', 'unit_cost']); // Make sure 'unit_cost' exists
        }


        return view('accounting.invoices.create', compact(
            'contacts',
            'taxRates',
            'incomeAccounts',
            'nextInvoiceNumber',
            'feeItems',
            'inventoryItems'
        ));
    }

    /**
     * Store a newly created resource in storage.
     * MODIFIED for Invoice Type
     */
    public function store(Request $request): RedirectResponse
    {
         $schoolId = Auth::user()?->school_id;
         $hasInvoiceSchoolId = Schema::hasColumn('invoices', 'school_id');
         $hasContactSchoolId = Schema::hasColumn('contacts', 'school_id');

         $validatedData = $request->validate([
            'contact_id' => ['required', Rule::exists('contacts', 'id')->where(function ($query) use ($schoolId, $hasContactSchoolId) {
                if ($schoolId && $hasContactSchoolId) {
                    $query->where('school_id', $schoolId);
                }
            })],
            'invoice_number' => [
                'required',
                'string',
                 Rule::unique('invoices')->where(function ($query) use ($schoolId, $hasInvoiceSchoolId) {
                     if ($schoolId && $hasInvoiceSchoolId) {
                         return $query->where('school_id', $schoolId);
                     }
                     return $query;
                 }),
             ],
            'invoice_type' => 'required|in:fees,inventory',
            'reference' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_source_id' => 'nullable|integer',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
        ], [ /* Custom messages */
             'items.required' => 'At least one item line is required.',
             'items.*.account_id.required' => 'An income account must be selected for each item.',
             'invoice_type.required' => 'Please select an invoice type (Fees or Inventory).',
             'invoice_number.unique' => 'This invoice number is already in use for your school.',
             'contact_id.exists' => 'Selected contact is invalid or does not belong to your school.',
        ]);

        // Additional check (already covered by Rule::exists with where clause, but can keep as extra safety)
        // if ($validatedData['contact_id'] && $schoolId && $hasContactSchoolId) {
        //     $contactValid = Contact::where('id', $validatedData['contact_id'])
        //                            ->where('school_id', $schoolId)
        //                            ->exists();
        //     if (!$contactValid) {
        //        return redirect()->back()->withInput()->with('error', 'Selected contact is invalid or does not belong to your school.');
        //     }
        // }

        DB::beginTransaction();
        try {
            // --- Pre-calculate totals ---
            $totalSubtotal = 0;
            $totalDiscountAmount = 0;
            $totalTaxAmount = 0;
            $taxRatesData = TaxRate::whereIn('id', collect($validatedData['items'])->pluck('tax_rate_id')->filter())->pluck('rate', 'id');

            foreach ($validatedData['items'] as $item) {
                $quantity = (float)($item['quantity'] ?? 0);
                $price = (float)($item['unit_price'] ?? 0);
                $discount = (float)($item['discount'] ?? 0);
                $taxRateId = $item['tax_rate_id'] ?? null;
                $lineSubtotalBeforeDiscount = $quantity * $price;
                $lineSubtotalAfterDiscount = max(0, $lineSubtotalBeforeDiscount - $discount);
                $lineTaxAmount = ($taxRateId && isset($taxRatesData[$taxRateId])) ? ($lineSubtotalAfterDiscount * ($taxRatesData[$taxRateId] / 100)) : 0;

                $totalSubtotal += $lineSubtotalBeforeDiscount;
                $totalDiscountAmount += $discount;
                $totalTaxAmount += $lineTaxAmount;
            }
            $totalAmount = ($totalSubtotal - $totalDiscountAmount) + $totalTaxAmount;
            // --- End Pre-calculation ---

            $invoiceData = [
                'contact_id' => $validatedData['contact_id'],
                'invoice_number' => $validatedData['invoice_number'],
                'invoice_type' => $validatedData['invoice_type'],
                'reference' => $validatedData['reference'],
                'issue_date' => $validatedData['issue_date'],
                'due_date' => $validatedData['due_date'],
                'notes' => $validatedData['notes'],
                'terms' => $validatedData['terms'],
                'subtotal' => $totalSubtotal,
                'discount_amount' => $totalDiscountAmount,
                'tax_amount' => $totalTaxAmount,
                'total' => $totalAmount,
                'amount_paid' => 0,
                'status' => 'draft', // Start as draft
                'created_by' => auth()->id(),
            ];
            if ($schoolId && $hasInvoiceSchoolId) {
                $invoiceData['school_id'] = $schoolId;
            }
            $invoice = Invoice::create($invoiceData);

            // --- Determine item source class (uses Morph Map alias if possible) ---
            $itemSourceClass = null; // Will be determined based on Morph Map Alias preferred
            if ($validatedData['invoice_type'] == 'fees' && class_exists(FeeStructureItem::class)) {
                 $itemSourceClass = FeeStructureItem::class; // Or 'fee_item' if using alias directly
            } elseif ($validatedData['invoice_type'] == 'inventory' && class_exists(InventoryItem::class)) {
                 $itemSourceClass = InventoryItem::class; // Or 'inventory_item'
            }

            // --- Create Invoice Items ---
            foreach ($validatedData['items'] as $itemData) {
                $quantity = (float)($itemData['quantity'] ?? 0);
                $price = (float)($itemData['unit_price'] ?? 0);
                $discount = (float)($itemData['discount'] ?? 0);
                $taxRateId = $itemData['tax_rate_id'] ?? null;
                $lineSubtotalBeforeDiscount = $quantity * $price;
                $lineSubtotalAfterDiscount = max(0, $lineSubtotalBeforeDiscount - $discount);
                $lineTaxAmount = ($taxRateId && isset($taxRatesData[$taxRateId])) ? ($lineSubtotalAfterDiscount * ($taxRatesData[$taxRateId] / 100)) : 0;
                $lineTotal = $lineSubtotalAfterDiscount + $lineTaxAmount;

                $itemPayload = [
                    'name' => $itemData['name'],
                    'description' => $itemData['description'],
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'discount' => $discount,
                    'tax_rate_id' => $taxRateId,
                    'account_id' => $itemData['account_id'],
                    'tax_amount' => $lineTaxAmount,
                    'total' => $lineTotal,
                    // Set source IF ID is provided AND we determined a valid class/alias
                    'item_source_id' => $itemData['item_source_id'] ?? null,
                    'item_source_type' => ($itemData['item_source_id'] && $itemSourceClass) ? $itemSourceClass : null,
                ];
                 // Let Eloquent handle morph map alias if itemSourceClass is a class string
                 // and morph map is active during save.
                InvoiceItem::create(['invoice_id' => $invoice->id] + $itemPayload);


                // --- Optional: Stock Adjustment ---
                if ($validatedData['invoice_type'] == 'inventory' && !empty($itemData['item_source_id']) && class_exists(InventoryItem::class)) {
                     $inventoryItem = InventoryItem::find($itemData['item_source_id']);
                     if ($inventoryItem && Schema::hasColumn('inventory_items', 'current_stock')) {
                          // Ensure quantity doesn't exceed stock if checks are needed here
                          $inventoryItem->decrement('current_stock', $quantity);
                         if (class_exists(StockMovement::class) && Schema::hasTable('stock_movements')) {
                             // Log stock movement (simplified)
                              StockMovement::create([
                                 'item_id' => $inventoryItem->id,
                                 'movement_type' => 'invoice_out', // Make sure this type exists or adjust
                                 'quantity' => -$quantity, // Negative for outgoing stock
                                 'related_document_type' => Invoice::class, // Morph relation
                                 'related_document_id' => $invoice->id,
                                 'movement_date' => $invoice->issue_date,
                                 'notes' => "Invoice #{$invoice->invoice_number}",
                                 'created_by' => auth()->id(),
                                 // Add school_id if the StockMovement table has it
                                 // 'school_id' => $schoolId,
                             ]);
                         }
                     } elseif ($inventoryItem) {
                         Log::warning("Inventory item found, but 'current_stock' column missing. Cannot decrement stock.", ['item_id' => $itemData['item_source_id']]);
                     } else {
                         Log::warning("Inventory item for stock adjustment not found.", ['item_id' => $itemData['item_source_id'], 'invoice_id' => $invoice->id]);
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
            Log::warning("Validation failed during invoice creation.", ['errors' => $e->errors(), 'user_id' => auth()->id()]);
            throw $e; // Re-throw validation exception to let Laravel handle it (displays errors)
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating invoice: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->except(['_token', 'items']), // Be careful logging sensitive data
                'user_id' => auth()->id()
            ]);
            // Provide a user-friendly error message
            return redirect()->back()
                      ->withInput() // Keep form data
                      ->with('error', 'An unexpected error occurred while creating the invoice. Please check the logs or contact support.');
        }
    }


    /**
     * Display the specified invoice.
     *
     * @param  \App\Models\Accounting\Invoice  $invoice
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Invoice $invoice): View|RedirectResponse
    {
        $this->authorizeSchoolAccess($invoice);

        try {
            // Eager load relationships needed for the view
            $invoice->load([
                'contact', // Customer/Contact relationship
                'items' => function ($query) {
                    // Eager load relationships for each item
                    $query->with(['account', 'taxRate', 'itemSource']); // itemSource uses MorphMap
                },
                'journal' => function ($query) {
                    // Eager load journal and its entries/accounts if it exists
                    $query->with(['entries.account']);
                },
                'createdBy' // User who created the invoice
            ]);
        } catch (\Illuminate\Database\Eloquent\RelationNotFoundException $e) {
             // Catch issues if relationships are defined incorrectly
             Log::error("Relationship error loading invoice show {$invoice->id}: " . $e->getMessage());
             return redirect()->route('accounting.invoices.index')
                    ->with('error', 'Error loading invoice details due to a relationship configuration issue. Please contact support.');
        } catch (\Exception $e) {
             // Catch potential issues during load, especially if bad morph data still exists
             Log::error("Error loading relationships for invoice show {$invoice->id}: " . $e->getMessage());
             // Redirect back with error, maybe to index if show is unusable
             return redirect()->route('accounting.invoices.index')
                    ->with('error', 'Error loading invoice details. Please check data integrity or contact support. Issue: ' . $e->getMessage());
         }

        // Pass the loaded invoice to the view
        return view('accounting.invoices.show', compact('invoice'));
    }


    /**
     * Show the form for editing the specified invoice.
     * ADDED METHOD
     * @param  \App\Models\Accounting\Invoice  $invoice
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Invoice $invoice): View|RedirectResponse
    {
        // 1. Authorization Check
        $this->authorizeSchoolAccess($invoice);

        // 2. Status Check (Only allow editing drafts)
        if (!in_array($invoice->status, ['draft'])) {
             Log::warning("Attempted to edit non-draft invoice.", ['invoice_id' => $invoice->id, 'status' => $invoice->status, 'user_id' => auth()->id()]);
             return redirect()->route('accounting.invoices.show', $invoice)
                    ->with('error', 'Only invoices in draft status can be edited.');
        }

        // 3. Load Data for the Form
        try {
             // Eager load necessary relationships for the form fields and items table
             // Make sure these relationships exist and are correctly defined in the Invoice model
            $invoice->load(['contact', 'items.account', 'items.taxRate', 'items.itemSource']); // Load items too
        } catch (\Illuminate\Database\Eloquent\RelationNotFoundException $e) {
             Log::error("Relationship error loading invoice edit {$invoice->id}: " . $e->getMessage());
             return redirect()->route('accounting.invoices.show', $invoice) // Redirect back to show might be better
                    ->with('error', 'Error loading invoice data for editing due to relationship issues. Contact support.');
        } catch (\Exception $e) {
             // Catch potential issues during load (e.g., bad morph data)
             Log::error("Error loading relationships for invoice edit {$invoice->id}: " . $e->getMessage());
             return redirect()->route('accounting.invoices.index') // Redirect to index might be safer
                    ->with('error', 'Error loading invoice data for editing. Please fix data inconsistencies (like item source types) first.');
        }

        // Load data needed for dropdowns etc. (similar to the create method, but scoped)
        $school_id = $invoice->school_id ?? Auth::user()?->school_id; // Use invoice's school_id if available
        $hasContactSchoolId = Schema::hasColumn('contacts', 'school_id');
        $hasCoASchoolId = Schema::hasColumn('chart_of_accounts', 'school_id');
        $hasFeeStructSchoolId = Schema::hasColumn('fee_structures', 'school_id');
        $hasInvItemSchoolId = Schema::hasColumn('inventory_items', 'school_id');

        // Contacts (Active, potentially scoped by school)
        $contacts = Contact::where('is_active', true)
             ->when($school_id && $hasContactSchoolId, fn($q) => $q->where('school_id', $school_id))
             ->orderBy('name')->get(['id', 'name']);

        // Tax Rates (Active, usually global)
        $taxRates = TaxRate::where('is_active', true)
             ->orderBy('name')->get(['id', 'name', 'rate']);

        // Income Accounts (Active, potentially scoped by school)
        $incomeAccounts = ChartOfAccount::whereHas('accountType', fn($q) => $q->whereIn('code', ['INCOME', 'REVENUE']))
            ->where('is_active', true)
             ->when($school_id && $hasCoASchoolId, fn($q) => $q->where('school_id', $school_id)) // Scope if needed
             ->orderBy('name')->get(['id', 'name', 'account_code']);
        if ($incomeAccounts->isEmpty()) { // Fallback if no specific income accounts found
             $incomeAccounts = ChartOfAccount::where('is_active', true)
                ->when($school_id && $hasCoASchoolId, fn($q) => $q->where('school_id', $school_id)) // Scope fallback too
                ->orderBy('name')->get(['id', 'name', 'account_code']);
        }

        // Load Fee/Inventory items ONLY if they are needed for dynamic dropdowns/selection in the edit view
        // If the edit view just displays existing items, these might not be strictly needed here
        $feeItems = collect();
         if (class_exists(FeeStructureItem::class) && Schema::hasTable('fee_structure_items')) {
            $activeFeeStructures = collect();
            if (class_exists(FeeStructure::class) && Schema::hasTable('fee_structures') && Schema::hasColumn('fee_structures', 'school_id')) {
                $activeFeeStructures = FeeStructure::where('is_active', true)
                    ->when($school_id && $hasFeeStructSchoolId, fn($q) => $q->where('school_id', $school_id))
                    ->pluck('id');
            }
             if ($activeFeeStructures->isNotEmpty()) {
                 $feeItems = FeeStructureItem::whereIn('fee_structure_id', $activeFeeStructures)
                                ->orderBy('name')
                                ->get(['id', 'name', 'description', 'amount', 'income_account_id']); // Get fields needed for dropdown/selection
             }
         }

         $inventoryItems = collect();
         if (class_exists(InventoryItem::class) && Schema::hasTable('inventory_items') && Schema::hasColumn('inventory_items', 'school_id')) {
             $inventoryItems = InventoryItem::where('is_active', true)
                 ->when($school_id && $hasInvItemSchoolId, fn($q) => $q->where('school_id', $school_id)) // Scope if needed
                 ->orderBy('name')
                 ->get(['id', 'name', 'description', 'unit_cost']); // Get fields needed for dropdown/selection
         }

        // 4. Return the View
        // Ensure you have a view file at resources/views/accounting/invoices/edit.blade.php
        return view('accounting.invoices.edit', compact(
            'invoice',        // The main invoice object being edited
            'contacts',       // List of contacts for dropdown
            'taxRates',       // List of tax rates for dropdown
            'incomeAccounts', // List of income accounts for dropdown
            'feeItems',       // List of fee items (if needed for selection)
            'inventoryItems'  // List of inventory items (if needed for selection)
        ));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        // 1. Authorization Check
        $this->authorizeSchoolAccess($invoice);

        // 2. Status Check: Only update draft invoices
        if (!in_array($invoice->status, ['draft'])) {
            Log::warning("Attempted to update non-draft invoice.", ['invoice_id' => $invoice->id, 'status' => $invoice->status, 'user_id' => auth()->id()]);
            return redirect()->route('accounting.invoices.show', $invoice)->with('error', 'Cannot update an invoice that is not in draft status.');
        }

        $schoolId = $invoice->school_id ?? Auth::user()?->school_id; // Use invoice's school ID
        $hasInvoiceSchoolId = Schema::hasColumn('invoices', 'school_id');
        $hasContactSchoolId = Schema::hasColumn('contacts', 'school_id');

        // 3. Validation Rules
        $validatedData = $request->validate([
            'contact_id' => ['required', Rule::exists('contacts', 'id')->where(function ($query) use ($schoolId, $hasContactSchoolId) {
                if ($schoolId && $hasContactSchoolId) {
                    $query->where('school_id', $schoolId);
                }
            })],
            'invoice_number' => [
                'required', 'string',
                Rule::unique('invoices')->ignore($invoice->id)->where(function ($query) use ($schoolId, $hasInvoiceSchoolId) {
                    // Scope uniqueness check to the school if applicable
                    if ($schoolId && $hasInvoiceSchoolId) {
                        return $query->where('school_id', $schoolId);
                    }
                    return $query;
                }),
            ],
            // 'invoice_type' should generally NOT be editable after creation, remove if not needed.
            // 'invoice_type' => 'required|in:fees,inventory',
            'reference' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            // --- Item Validation ---
            'items' => 'required|array|min:1', // Must have at least one item
            'items.*.id' => 'nullable|integer|exists:invoice_items,id', // For existing items being updated
            'items.*.item_source_id' => 'nullable|integer', // Optional source ID
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.0001', // Allow fractional quantities, but must be positive
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id', // Tax rate must exist or be null
            'items.*.account_id' => 'required|exists:chart_of_accounts,id', // Account must exist
        ], [ /* Custom Error Messages */
             'items.required' => 'At least one item line is required.',
             'items.*.account_id.required' => 'An income account must be selected for each item.',
             'items.*.account_id.exists' => 'The selected income account is invalid for item #:position.',
             'items.*.tax_rate_id.exists' => 'The selected tax rate is invalid for item #:position.',
             'invoice_number.unique' => 'This invoice number is already in use for your school.',
             'contact_id.exists' => 'Selected contact is invalid or does not belong to your school.',
        ]);

        // --- Recalculate totals based on submitted data ---
        $totalSubtotal = 0;
        $totalDiscountAmount = 0;
        $totalTaxAmount = 0;
        $taxRatesData = TaxRate::whereIn('id', collect($validatedData['items'])->pluck('tax_rate_id')->filter())->pluck('rate', 'id');
        $updatedItemIds = []; // Keep track of item IDs present in the request

        foreach ($validatedData['items'] as $item) {
            $quantity = (float)($item['quantity'] ?? 0);
            $price = (float)($item['unit_price'] ?? 0);
            $discount = (float)($item['discount'] ?? 0);
            $taxRateId = $item['tax_rate_id'] ?? null;
            $lineSubtotalBeforeDiscount = $quantity * $price;
            $lineSubtotalAfterDiscount = max(0, $lineSubtotalBeforeDiscount - $discount);
            $lineTaxAmount = ($taxRateId && isset($taxRatesData[$taxRateId])) ? ($lineSubtotalAfterDiscount * ($taxRatesData[$taxRateId] / 100)) : 0;

            $totalSubtotal += $lineSubtotalBeforeDiscount;
            $totalDiscountAmount += $discount; // Sum of discounts applied per line
            $totalTaxAmount += $lineTaxAmount;

            // Collect IDs of items submitted in the form
            if (!empty($item['id'])) {
                $updatedItemIds[] = $item['id'];
            }
        }
        $totalAmount = ($totalSubtotal - $totalDiscountAmount) + $totalTaxAmount; // Recalculate final total
        // --- End Recalculation ---


        DB::beginTransaction();
        try {
             // --- Update Invoice Header ---
            $invoiceHeaderData = [
                'contact_id' => $validatedData['contact_id'],
                'invoice_number' => $validatedData['invoice_number'],
                'reference' => $validatedData['reference'],
                'issue_date' => $validatedData['issue_date'],
                'due_date' => $validatedData['due_date'],
                'notes' => $validatedData['notes'],
                'terms' => $validatedData['terms'],
                'subtotal' => $totalSubtotal,
                'discount_amount' => $totalDiscountAmount,
                'tax_amount' => $totalTaxAmount,
                'total' => $totalAmount,
                // Do NOT update amount_paid or status here; they are managed by payments/approval
            ];
             $invoice->update($invoiceHeaderData);

             // --- Determine Item Source Class based on the *existing* invoice type ---
             // Invoice type should generally not change during an update.
             $itemSourceClass = null;
             if ($invoice->invoice_type == 'fees' && class_exists(FeeStructureItem::class)) {
                  $itemSourceClass = FeeStructureItem::class; // Or morph alias 'fee_item'
             } elseif ($invoice->invoice_type == 'inventory' && class_exists(InventoryItem::class)) {
                  $itemSourceClass = InventoryItem::class; // Or morph alias 'inventory_item'
             }

             // --- Process Items: Update existing, Create new ---
             foreach ($validatedData['items'] as $itemData) {
                 $quantity = (float)($itemData['quantity'] ?? 0);
                 $price = (float)($itemData['unit_price'] ?? 0);
                 $discount = (float)($itemData['discount'] ?? 0);
                 $taxRateId = $itemData['tax_rate_id'] ?? null;

                 // Recalculate line totals for saving
                 $lineSubtotalBeforeDiscount = $quantity * $price;
                 $lineSubtotalAfterDiscount = max(0, $lineSubtotalBeforeDiscount - $discount);
                 $lineTaxAmount = ($taxRateId && isset($taxRatesData[$taxRateId])) ? ($lineSubtotalAfterDiscount * ($taxRatesData[$taxRateId] / 100)) : 0;
                 $lineTotal = $lineSubtotalAfterDiscount + $lineTaxAmount;

                 $itemPayload = [
                     'name' => $itemData['name'],
                     'description' => $itemData['description'],
                     'quantity' => $quantity,
                     'unit_price' => $price,
                     'discount' => $discount,
                     'tax_rate_id' => $taxRateId,
                     'account_id' => $itemData['account_id'],
                     'item_source_id' => $itemData['item_source_id'] ?? null,
                     'item_source_type' => ($itemData['item_source_id'] && $itemSourceClass) ? $itemSourceClass : null, // Use determined class/alias
                     'tax_amount' => $lineTaxAmount, // Store calculated tax per line
                     'total' => $lineTotal,           // Store calculated total per line
                 ];

                 if (!empty($itemData['id'])) {
                     // Update Existing Item: Find the item *belonging to this invoice* and update it
                     $invoiceItem = InvoiceItem::where('id', $itemData['id'])
                                              ->where('invoice_id', $invoice->id) // Important security/integrity check
                                              ->first();
                     if ($invoiceItem) {
                         $invoiceItem->update($itemPayload);
                     } else {
                         // Log a warning if an item ID was submitted that doesn't belong to this invoice
                         Log::warning("Attempted update on mismatched item ID {$itemData['id']} for invoice ID {$invoice->id}. Item not updated.");
                         // Optionally, throw an exception or add an error message if this shouldn't happen
                         // throw new \Exception("Invalid item ID {$itemData['id']} submitted for invoice {$invoice->id}.");
                     }
                 } else {
                     // Create New Item: Associate the new item with the current invoice
                     $invoice->items()->create($itemPayload); // Use the relationship to automatically set invoice_id
                 }

                 // NOTE: Stock adjustments during update are tricky.
                 // You'd need to calculate the *difference* in quantity from the original item
                 // and adjust stock accordingly. This is complex and error-prone.
                 // A simpler approach is often to only adjust stock on initial creation/approval
                 // or handle returns/adjustments via separate processes (like Credit Notes).
                 // We will skip stock adjustment on update here for simplicity.
             }

             // --- Delete Removed Items ---
             // Get IDs of items currently associated with the invoice in the database
             $existingItemIds = $invoice->items()->pluck('id')->toArray();
             // Find IDs that exist in the database but were NOT submitted in the form
             $itemsToDeleteIds = array_diff($existingItemIds, $updatedItemIds);

             if (!empty($itemsToDeleteIds)) {
                 // Delete items that were removed from the form
                 InvoiceItem::whereIn('id', $itemsToDeleteIds)
                           ->where('invoice_id', $invoice->id) // Ensure we only delete items from *this* invoice
                           ->delete();
             }

            DB::commit();
             Log::info("Invoice #{$invoice->invoice_number} updated successfully.", ['invoice_id' => $invoice->id, 'user_id' => auth()->id()]);
            return redirect()->route('accounting.invoices.show', $invoice)->with('success', 'Invoice updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
             DB::rollBack();
             Log::warning("Validation failed during invoice update.", ['invoice_id' => $invoice->id, 'errors' => $e->errors(), 'user_id' => auth()->id()]);
             throw $e; // Let Laravel handle displaying validation errors
        } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Error updating invoice {$invoice->id}: " . $e->getMessage(), [
                 'trace' => $e->getTraceAsString(),
                 'request' => $request->except(['_token', 'items']), // Be careful logging sensitive data
                 'user_id' => auth()->id()
                ]);
             // Provide a user-friendly error message
             return redirect()->back()
                       ->withInput()
                       ->with('error', 'An unexpected error occurred while updating the invoice. Please check logs or contact support.');
        }
    }


    // --- HELPER METHODS --- (Keep existing: createJournalForInvoice, findOrCreateArAccount, findOrCreateTaxAccount, generateNextInvoiceNumber, authorizeSchoolAccess)

    /**
     * Create or update the journal entry for an invoice upon approval.
     * Ensures idempotency by deleting old journal entries if they exist.
     */
    private function createJournalForInvoice(Invoice $invoice): void // Changed return type to void
    {
        // Ensure necessary relationships are loaded
        $invoice->loadMissing(['items.account', 'items.taxRate', 'contact', 'journal']); // Load existing journal if present

        // Find essential accounts
        $arAccount = $this->findOrCreateArAccount($invoice->school_id);
        if (!$arAccount) {
            throw new \Exception("Accounts Receivable account could not be found or created for school ID: " . ($invoice->school_id ?? 'Global') . ". Cannot create journal.");
        }

        $taxAccount = null;
        if ((float)$invoice->tax_amount > 0) {
            $taxAccount = $this->findOrCreateTaxAccount($invoice->school_id);
            if (!$taxAccount) {
                throw new \Exception("Tax Payable account could not be found or created for school ID: " . ($invoice->school_id ?? 'Global') . ", but invoice has tax. Cannot create journal.");
            }
        }

        DB::transaction(function () use ($invoice, $arAccount, $taxAccount) {
            // --- Delete existing journal entry if invoice is being re-approved or corrected ---
            // This ensures the journal reflects the *current* state of the approved invoice.
            if ($invoice->journal_id && $invoice->journal) { // Check if journal_id exists and relation is loaded/exists
                Log::info("Deleting existing journal before recreating for invoice approval/update.", [
                    'invoice_id' => $invoice->id,
                    'journal_id' => $invoice->journal_id,
                    'user_id' => auth()->id() ?? $invoice->created_by // Use logged-in user or original creator
                ]);
                $invoice->journal->entries()->delete(); // Delete related entries first
                $invoice->journal->delete();            // Delete the journal header
                $invoice->journal_id = null;           // Nullify the link on the invoice
                // No need to save invoice here, it happens after journal creation or outside this function
            } elseif ($invoice->journal_id) {
                 // If journal_id exists but relation failed to load, log it but still try to proceed
                 Log::warning("Invoice {$invoice->id} has journal_id {$invoice->journal_id} but the journal relation could not be loaded. Attempting to clear ID and create new journal.", ['user_id' => auth()->id()]);
                 $invoice->journal_id = null;
                 // Consider cleanup logic here if needed, e.g., manually deleting orphaned journals/entries if this happens often
            }


            // --- Create the new Journal ---
            $journal = Journal::create([
                'school_id' => $invoice->school_id, // Can be null if global
                'journal_date' => $invoice->issue_date, // Use invoice issue date for journal date
                'reference' => 'Invoice #' . $invoice->invoice_number,
                'description' => 'Sales Invoice to ' . ($invoice->contact?->name ?? 'N/A'),
                'status' => 'posted', // Journal is typically posted immediately on invoice approval
                'created_by' => auth()->id() ?? $invoice->created_by, // Use logged-in user or original creator
                'document_type' => Invoice::class, // Link back to the invoice using morph relation
                'document_id' => $invoice->id,
             ]);

            $totalDebit = 0;
            $totalCredit = 0;
            $precision = 2; // Define precision for rounding comparisons

            // 1. Debit Accounts Receivable (AR) for the *total* invoice amount
            $debitAmount = round((float) $invoice->total, $precision);
             if ($debitAmount > 0) {
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $arAccount->id,
                    'debit' => $debitAmount,
                    'credit' => 0,
                    'description' => 'Invoice #' . $invoice->invoice_number . ' - Total Due from ' . ($invoice->contact?->name ?? 'N/A'),
                    'contact_id' => $invoice->contact_id, // Link AR entry to the contact
                ]);
                $totalDebit += $debitAmount;
             } elseif ($debitAmount < 0) {
                 // This would typically be a credit note, handle appropriately if needed
                 Log::warning("Invoice total is negative, creating AR credit instead of debit.", ['invoice_id' => $invoice->id, 'total' => $invoice->total]);
                 // Handle negative total if necessary (e.g., reverse debit/credit)
             }

             // 2. Credit Income Accounts for each line item (subtotal after discount)
             foreach ($invoice->items as $item) {
                 // Calculate the subtotal for this line item *after* applying discounts
                 $itemSubtotalAfterDiscount = round(max(0, ((float)$item->quantity * (float)$item->unit_price) - (float)$item->discount), $precision);

                 if (!$item->account_id) {
                     // Critical error: Every item MUST have an income account assigned
                     throw new \Exception("Invoice item ID {$item->id} ('{$item->name}') is missing an account. Cannot create journal entry.");
                 }

                 if ($itemSubtotalAfterDiscount > 0) {
                     JournalEntry::create([
                        'journal_id' => $journal->id,
                        'account_id' => $item->account_id, // The specific income account for this item
                        'debit' => 0,
                        'credit' => $itemSubtotalAfterDiscount,
                        'description' => $item->name . ' (Inv #' . $invoice->invoice_number . ')', // Item description
                        'contact_id' => $invoice->contact_id, // Optional: Link income entry to contact too
                     ]);
                     $totalCredit += $itemSubtotalAfterDiscount;
                 }
             }

             // 3. Credit Tax Payable for the *total* tax amount on the invoice
             $invoiceTaxAmount = round((float) $invoice->tax_amount, $precision);
             if ($invoiceTaxAmount > 0) {
                  if (!$taxAccount) {
                      // This check is redundant due to the check at the start, but good for clarity
                      throw new \Exception("Invoice has tax amount ({$invoiceTaxAmount}) but Tax Payable account was not found. Cannot create journal entry.");
                  }
                  JournalEntry::create([
                     'journal_id' => $journal->id,
                     'account_id' => $taxAccount->id,
                     'debit' => 0,
                     'credit' => $invoiceTaxAmount,
                     'description' => 'Sales Tax on Invoice #' . $invoice->invoice_number,
                     'contact_id' => $invoice->contact_id, // Optional: Link tax entry to contact
                 ]);
                 $totalCredit += $invoiceTaxAmount;
             }

             // --- Final Balance Check ---
             // Use a small tolerance for floating point comparisons
             $tolerance = 0.01; // Adjust as needed (e.g., 0.001)
             if (abs($totalDebit - $totalCredit) > $tolerance) {
                 Log::critical("Journal unbalanced for Invoice {$invoice->id}!", [
                     'invoice_id' => $invoice->id,
                     'journal_id' => $journal->id,
                     'calculated_debit' => $totalDebit,
                     'calculated_credit' => $totalCredit,
                     'invoice_total' => $invoice->total,
                     'invoice_subtotal' => $invoice->subtotal,
                     'invoice_discount' => $invoice->discount_amount,
                     'invoice_tax' => $invoice->tax_amount,
                     'user_id' => auth()->id()
                 ]);
                 // Throw an exception to rollback the transaction
                 throw new \Exception("Journal entries are unbalanced. Debit: {$totalDebit}, Credit: {$totalCredit}. Transaction rolled back.");
             }

             // --- Link Journal to Invoice ---
             // This needs to happen *before* commit if the calling function relies on it immediately
             // However, saving the invoice should happen *outside* the transaction usually,
             // or right at the end *inside* the transaction. Let's update the ID here.
             $invoice->journal_id = $journal->id;
             $invoice->saveQuietly(); // Use saveQuietly if you don't want events triggered here

             Log::info("Journal entry created/updated successfully for Invoice #{$invoice->invoice_number}.", [
                 'invoice_id' => $invoice->id,
                 'journal_id' => $journal->id,
                 'user_id' => auth()->id()
                ]);

        }); // End DB::transaction

        // Note: The invoice object's journal_id attribute is updated in memory.
        // The calling function (e.g., approve method) should handle the final save of the invoice model
        // to persist the journal_id along with any status changes.
    }


    /**
     * Helper to find or create the Accounts Receivable system account.
     * Prioritizes school-specific account, then global system account.
     */
     private function findOrCreateArAccount($schoolId = null): ?ChartOfAccount
    {
        $arCode = config('accounting.ar_account_code', '1100'); // Default AR Code
        $arName = 'Accounts Receivable';
        $accountTypeName = 'ASSET'; // AR must be an Asset

        return $this->findOrCreateSystemAccount($schoolId, $arCode, $arName, $accountTypeName);
    }

    /**
     * Helper to find or create the Tax Payable system account.
     * Prioritizes school-specific account, then global system account.
     */
     private function findOrCreateTaxAccount($schoolId = null): ?ChartOfAccount
    {
        $taxCode = config('accounting.tax_account_code', '2300'); // Default Tax Code
        $taxName = 'Tax Payable'; // Or 'Sales Tax Payable', 'VAT Payable' etc.
        $accountTypeName = 'LIABILITY'; // Tax payable is a Liability

        return $this->findOrCreateSystemAccount($schoolId, $taxCode, $taxName, $accountTypeName);
    }

    /**
     * Generic helper to find or create a system Chart of Account.
     * - Checks for school-specific account by code first.
     * - Checks for global account by code next.
     * - Falls back to searching by name (school-specific then global).
     * - Attempts creation if not found.
     *
     * @param int|null $schoolId
     * @param string $accountCode
     * @param string $accountName
     * @param string $accountTypeCode ('ASSET', 'LIABILITY', 'INCOME', etc.)
     * @return ChartOfAccount|null
     */
    private function findOrCreateSystemAccount($schoolId, string $accountCode, string $accountName, string $accountTypeCode): ?ChartOfAccount
    {
        $hasSchoolIdColumn = Schema::hasColumn('chart_of_accounts', 'school_id');

        // --- 1. Find by Code ---
        $queryByCode = ChartOfAccount::query()
            ->where('account_code', $accountCode)
            ->whereHas('accountType', fn($q) => $q->where('code', $accountTypeCode));

        if ($schoolId && $hasSchoolIdColumn) {
            // Prioritize school-specific, then global
            $queryByCode->orderByRaw("CASE WHEN school_id = ? THEN 0 ELSE 1 END", [$schoolId])
                       ->where(function ($q) use ($schoolId) {
                           $q->where('school_id', $schoolId)
                             ->orWhereNull('school_id'); // Allow matching global if school-specific not found
                       });
        } else {
            // Only look for global accounts if no schoolId or no school_id column
            if ($hasSchoolIdColumn) {
                $queryByCode->whereNull('school_id');
            }
        }

        // Prefer system accounts if multiple matches found (e.g., user created one with same code)
        $account = $queryByCode->orderByRaw("CASE WHEN is_system = 1 THEN 0 ELSE 1 END")
                               ->orderByRaw("CASE WHEN is_active = 1 THEN 0 ELSE 1 END") // Prefer active
                               ->first();

        if ($account) {
            // Activate if found but inactive (optional, depends on business logic)
            // if (!$account->is_active) {
            //     Log::warning("Found system account '{$accountCode}' ({$accountName}) but it was inactive. Activating.", ['account_id' => $account->id, 'school_id' => $schoolId]);
            //     $account->update(['is_active' => true]);
            // }
            return $account;
        }

        // --- 2. Fallback: Find by Name ---
        Log::warning("System account '{$accountCode}' not found by code. Falling back to search by name '{$accountName}'.", ['school_id' => $schoolId, 'type' => $accountTypeCode]);
        $queryByName = ChartOfAccount::query()
            ->where('name', 'LIKE', $accountName . '%') // Use LIKE for flexibility
            ->whereHas('accountType', fn($q) => $q->where('code', $accountTypeCode));

        if ($schoolId && $hasSchoolIdColumn) {
            $queryByName->orderByRaw("CASE WHEN school_id = ? THEN 0 ELSE 1 END", [$schoolId])
                        ->where(function ($q) use ($schoolId) {
                            $q->where('school_id', $schoolId)
                              ->orWhereNull('school_id');
                        });
        } else {
             if ($hasSchoolIdColumn) {
                 $queryByName->whereNull('school_id');
             }
        }

        $account = $queryByName->orderByRaw("CASE WHEN is_system = 1 THEN 0 ELSE 1 END")
                               ->orderByRaw("CASE WHEN is_active = 1 THEN 0 ELSE 1 END")
                               ->first();

        if ($account) {
            Log::info("Found system account by name fallback: '{$account->name}' (Code: {$account->account_code})", ['account_id' => $account->id, 'school_id' => $schoolId]);
            // Optionally update code if found by name but code doesn't match config? Risky.
            return $account;
        }

        // --- 3. Creation Logic ---
        Log::warning("System account '{$accountName}' (Code: {$accountCode}) not found by code or name. Attempting creation.", ['school_id' => $schoolId, 'type' => $accountTypeCode]);
        $accountType = AccountType::where('code', $accountTypeCode)->first();
        if (!$accountType) {
            Log::critical("Cannot create system account: Account Type '{$accountTypeCode}' is missing in the database.", ['school_id' => $schoolId]);
            return null; // Cannot proceed without the account type
        }

        try {
            // Prepare data for creation
            $createData = [
                'account_type_id' => $accountType->id,
                'account_code' => $accountCode,
                'name' => $accountName,
                'description' => 'Default ' . $accountName . ' (System Created)',
                'is_active' => true,
                'is_system' => true, // Mark as a system account
                'school_id' => null, // Default to global unless schoolId is provided and column exists
            ];

            // Assign school_id only if it's provided and the column exists
            if ($schoolId && $hasSchoolIdColumn) {
                // Check if a global account with this code *already* exists before creating a school-specific one
                $globalExists = ChartOfAccount::where('account_code', $accountCode)->whereNull('school_id')->exists();
                if ($globalExists && config('accounting.prefer_global_system_accounts', true)) {
                     Log::warning("Global account with code {$accountCode} exists. Not creating school-specific one due to preference.", ['school_id' => $schoolId]);
                     // In this scenario, maybe we should have found it earlier? Or configuration prevents finding it.
                     // Re-querying for the global one might be needed, or adjust the logic.
                     // For now, we'll proceed assuming creation is intended.
                }
                 $createData['school_id'] = $schoolId;
                 $createData['description'] .= " for School ID: {$schoolId}";
            } elseif ($hasSchoolIdColumn) {
                // Ensure global account doesn't already exist if creating globally
                $globalExists = ChartOfAccount::where('account_code', $accountCode)->whereNull('school_id')->exists();
                if ($globalExists) {
                     Log::error("Attempted to create a global system account with code {$accountCode}, but one already exists.", ['name' => $accountName]);
                     // Return the existing one instead?
                     return ChartOfAccount::where('account_code', $accountCode)->whereNull('school_id')->first();
                }
            }

            $newAccount = ChartOfAccount::create($createData);
            Log::info("Successfully created system account: '{$newAccount->name}' (Code: {$newAccount->account_code})", [
                'account_id' => $newAccount->id,
                'school_id' => $newAccount->school_id
            ]);
            return $newAccount;

        } catch (\Illuminate\Database\QueryException $e) {
             // Catch potential unique constraint violations or other DB errors
             Log::critical("Failed to create default system account '{$accountName}' (Code: {$accountCode}): " . $e->getMessage(), [
                 'sql_error_code' => $e->getCode(),
                 'school_id' => $schoolId,
                 'type' => $accountTypeCode
                ]);
             // Maybe the account was created in a race condition? Try finding it one last time.
             return ChartOfAccount::where('account_code', $accountCode)
                 ->when($schoolId && $hasSchoolIdColumn, fn($q) => $q->where('school_id', $schoolId))
                 ->when(!$schoolId && $hasSchoolIdColumn, fn($q) => $q->whereNull('school_id'))
                 ->first(); // Return null if still not found after error
        } catch (\Exception $e) {
            Log::critical("An unexpected error occurred while creating system account '{$accountName}' (Code: {$accountCode}): " . $e->getMessage(), ['school_id' => $schoolId]);
            return null;
        }
    }


    /**
     * Generate the next invoice number based on configured pattern and sequence.
     * Handles optional date prefix, padding, and school-specific sequences.
     */
    private function generateNextInvoiceNumber($schoolId = null): string
    {
        // Configuration values (consider caching these)
        $prefix = config('accounting.invoice_prefix', 'INV-');
        $useDate = config('accounting.invoice_prefix_date', true); // Include YYYYMMDD in prefix?
        $padding = config('accounting.invoice_number_padding', 4); // e.g., 4 => 0001
        $schoolSpecificSequence = config('accounting.invoice_school_specific_sequence', true); // Separate sequence per school?
        $separator = config('accounting.invoice_separator', '-'); // Separator between parts

        // Determine if the invoices table has a school_id column
        $hasSchoolIdColumn = Schema::hasColumn('invoices', 'school_id');

        // Construct the dynamic part of the prefix (date if used)
        $datePart = $useDate ? date('Ymd') . $separator : '';
        $currentPrefix = $prefix . $datePart; // e.g., "INV-20231027-" or "INV-"

        // Base query
        $query = Invoice::query();

        // Determine filtering context (school-specific or global)
        $filterBySchool = $schoolId && $hasSchoolIdColumn && $schoolSpecificSequence;

        if ($filterBySchool) {
            // Filter by school ID and the constructed prefix
            $query->where('school_id', $schoolId)
                  ->where('invoice_number', 'LIKE', $currentPrefix . '%');
            Log::debug("Generating next invoice number for school {$schoolId} with prefix '{$currentPrefix}'.");
        } else {
            // Global sequence: Filter only by prefix
            // If sequences are global but table has school_id, filter for NULL school_id OR based on config
            if ($hasSchoolIdColumn && !$schoolSpecificSequence && config('accounting.global_sequence_ignores_schools', true)) {
                 // Option 1: True global sequence (ignores school IDs)
                 $query->where('invoice_number', 'LIKE', $currentPrefix . '%');
                 Log::debug("Generating next GLOBAL invoice number with prefix '{$currentPrefix}' (ignoring school IDs).");
            } elseif ($hasSchoolIdColumn && !$schoolSpecificSequence) {
                 // Option 2: Global sequence only applies to non-school invoices
                 $query->whereNull('school_id')
                       ->where('invoice_number', 'LIKE', $currentPrefix . '%');
                 Log::debug("Generating next invoice number for non-school (global) context with prefix '{$currentPrefix}'.");
            } else {
                 // No school_id column, simple prefix match
                 $query->where('invoice_number', 'LIKE', $currentPrefix . '%');
                 Log::debug("Generating next invoice number (no school context) with prefix '{$currentPrefix}'.");
            }
        }

        // Get the last invoice number matching the criteria, ordered numerically if possible
        // Ordering directly by invoice_number might fail if padding differs or format changes.
        // It's safer to extract the numeric part if the format is consistent.
        // Let's try ordering by the full number first, assuming consistent padding.
        $lastInvoice = $query->orderBy('invoice_number', 'desc')->first();

        $nextNum = 1; // Default starting number

        if ($lastInvoice) {
            // Attempt to extract the numeric sequence part after the prefix
            $prefixLength = strlen($currentPrefix);
            $numericPart = substr($lastInvoice->invoice_number, $prefixLength);

            if (is_numeric($numericPart)) {
                $nextNum = intval($numericPart) + 1;
            } else {
                 // Fallback: Try extracting number after the *last* separator if primary extraction fails
                 Log::warning("Failed numeric extraction from invoice number using prefix.", [
                     'last_invoice_number' => $lastInvoice->invoice_number,
                     'calculated_prefix' => $currentPrefix,
                     'extracted_part' => $numericPart
                    ]);

                 $lastSeparatorPos = strrpos($lastInvoice->invoice_number, $separator);
                 if ($lastSeparatorPos !== false && $lastSeparatorPos > $prefixLength - strlen($separator)) { // Ensure separator is after prefix part
                     $fallbackNumericPart = substr($lastInvoice->invoice_number, $lastSeparatorPos + 1);
                      if (is_numeric($fallbackNumericPart)) {
                          $nextNum = intval($fallbackNumericPart) + 1;
                          Log::info("Successfully extracted sequence number using fallback method.", ['extracted_number' => $nextNum]);
                      } else {
                           Log::error("Fallback numeric extraction also failed.", ['last_invoice_number' => $lastInvoice->invoice_number]);
                           // Cannot determine next number, might need manual intervention or reset logic
                           // Consider throwing an exception or returning a default error format.
                           // For now, we proceed with nextNum = 1, which might cause duplicates.
                      }
                 } else {
                      Log::error("Could not find separator or extract sequence number using fallback.", ['last_invoice_number' => $lastInvoice->invoice_number]);
                      // Proceeding with nextNum = 1, potential duplicates.
                 }
            }
        }

        // Format the next number with padding
        $paddedNumber = str_pad($nextNum, $padding, '0', STR_PAD_LEFT);

        // Construct the final invoice number
        $nextInvoiceNumber = $currentPrefix . $paddedNumber;
        Log::info("Generated next invoice number: {$nextInvoiceNumber}", ['context_school_id' => $schoolId, 'is_school_specific' => $filterBySchool]);

        return $nextInvoiceNumber;
    }


     /**
     * Basic authorization check helper: Ensures the authenticated user
     * belongs to the same school as the model record, if applicable.
     * Allows access if the user has no school_id (assumed Super Admin)
     * or if the model doesn't have a school_id column.
     *
     * @param object $model Eloquent model instance (e.g., Invoice, Contact)
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException (401 or 403)
     */
    private function authorizeSchoolAccess($model): void
    {
        // 1. Check Authentication
        if (!Auth::check()) {
             abort(401, 'Unauthenticated.'); // Should typically be handled by middleware
        }

        // 2. Get User's School ID
        $userSchoolId = Auth::user()?->school_id;

        // 3. Super Admin Check: If user has no school_id, grant access (adjust if needed)
        if (is_null($userSchoolId) && config('auth.super_admin_can_access_all_schools', true)) {
            Log::debug("Super Admin access granted (user school_id is null).", ['user_id' => Auth::id()]);
            return;
        }

        // 4. Model School ID Check
        $modelTable = $model->getTable();
        $hasSchoolIdColumn = Schema::hasColumn($modelTable, 'school_id');

        // If the model's table doesn't have school_id, it's likely a global resource. Access granted.
        if (!$hasSchoolIdColumn) {
            Log::debug("Access granted: Model '{$modelTable}' does not have a school_id column.", ['user_id' => Auth::id()]);
            return;
        }

        // Get the model's school_id
        $modelSchoolId = $model->school_id;

        // 5. Comparison: If the model *has* a school_id, it must match the user's school_id
        if (!is_null($modelSchoolId) && $modelSchoolId !== $userSchoolId) {
            Log::warning('Authorization failed: School ID mismatch.', [
                'user_id' => Auth::id(),
                'user_school_id' => $userSchoolId,
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
                'model_school_id' => $modelSchoolId,
            ]);
            abort(403, 'You are not authorized to access this resource.');
        }

        // If model's school_id is NULL, access might depend on policy (e.g., only super admin or all school admins?)
        // Current logic allows access if model's school_id is NULL (treat as global/accessible)
        // If stricter control is needed for NULL school_id models, add it here:
        // if (is_null($modelSchoolId) && !is_null($userSchoolId)) {
        //     Log::warning('Authorization denied: School user attempting to access global resource.', [...]);
        //     abort(403, 'You cannot access global resources.');
        // }


        // If we reach here, authorization passed
        Log::debug("Authorization successful for user {$userSchoolId} on model " . get_class($model) . " with ID {$model->getKey()} (School ID: {$modelSchoolId}).", ['user_id' => Auth::id()]);
    }

    // --- Placeholder for Destroy (if needed) ---
    /**
     * Remove the specified resource from storage.
     * CONSIDER CAREFULLY: Deleting invoices can have major accounting implications.
     * Usually, 'voiding' or 'cancelling' is preferred.
     *
     * @param  \App\Models\Accounting\Invoice  $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    // public function destroy(Invoice $invoice): RedirectResponse
    // {
    //     $this->authorizeSchoolAccess($invoice);
    //
    //     // Add strict status checks - e.g., only allow deleting 'draft' invoices
    //     if ($invoice->status !== 'draft') {
    //         return redirect()->route('accounting.invoices.show', $invoice)
    //             ->with('error', 'Cannot delete an invoice that is not in draft status. Consider voiding instead.');
    //     }
    //
    //     DB::beginTransaction();
    //     try {
    //         // Delete related items first (or use cascading deletes if set up in DB/model)
    //         $invoice->items()->delete();
    //
    //         // If a journal exists (shouldn't for draft, but check), delete it
    //         if ($invoice->journal) {
    //              $invoice->journal->entries()->delete();
    //              $invoice->journal->delete();
    //         }
    //
    //         // Delete the invoice itself
    //         $invoice->delete();
    //
    //         DB::commit();
    //         Log::info("Draft Invoice #{$invoice->invoice_number} deleted successfully.", ['invoice_id' => $invoice->id, 'user_id' => auth()->id()]);
    //         return redirect()->route('accounting.invoices.index')->with('success', 'Draft Invoice deleted successfully.');
    //
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Error deleting invoice {$invoice->id}: " . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //             'user_id' => auth()->id()
    //         ]);
    //         return redirect()->route('accounting.invoices.show', $invoice)
    //             ->with('error', 'An error occurred while deleting the invoice: ' . $e->getMessage());
    //     }
    // }


    /**
     * Approve a draft invoice, create the corresponding journal entry, and update status.
     *
     * @param Invoice $invoice The invoice model instance (route-model binding)
     * @return RedirectResponse
     */
    public function approve(Invoice $invoice): RedirectResponse
    {
        // 1. Authorization Check
        $this->authorizeSchoolAccess($invoice);

        // 2. Status Check: Only approve invoices that are in 'draft' status
        if ($invoice->status !== 'draft') {
            Log::warning("Attempted to approve non-draft invoice.", [
                'invoice_id' => $invoice->id,
                'current_status' => $invoice->status,
                'user_id' => auth()->id()
            ]);
            return redirect()->route('accounting.invoices.show', $invoice)
                ->with('warning', 'This invoice is not in draft status and cannot be approved again.'); // Use warning for non-critical failure
        }

        // Check if items exist before approving
        if ($invoice->items()->doesntExist()) {
             return redirect()->route('accounting.invoices.edit', $invoice)
                 ->with('error', 'Cannot approve an invoice with no line items.');
        }


        DB::beginTransaction();
        try {
            // 3. Create Journal Entry FIRST
            //    This performs validation (e.g., accounts exist, balances) before changing status.
            //    The helper now handles deleting old journals if re-approving.
            $this->createJournalForInvoice($invoice); // Throws exception on failure

            // 4. Update Invoice Status
            //    'unpaid' is a common status after approval, indicating it's official and awaiting payment.
            //    Adjust based on your specific workflow (e.g., 'approved', 'sent').
            $invoice->status = 'unpaid'; // Or config('accounting.invoice_status_after_approval', 'unpaid')

            // 5. Set Approval Timestamp/User (Optional but Recommended)
            if (Schema::hasColumn($invoice->getTable(), 'approved_at')) {
                $invoice->approved_at = now();
            }
            if (Schema::hasColumn($invoice->getTable(), 'approved_by')) {
                $invoice->approved_by = auth()->id();
            }

            // 6. Save the Invoice (Persists status, journal_id, approval info)
            //    The journal_id should have been updated in memory by createJournalForInvoice.
            //    Using save() here will trigger events if needed.
            $invoice->save();

            // 7. Optional: Trigger Events or Notifications
            //    event(new InvoiceApproved($invoice));
            //    Notification::send($invoice->contact->user, new InvoiceReadyNotification($invoice));

            // 8. Optional: Update related models like StudentDebt if necessary
             // Check if the method exists on the model to avoid errors
             // if (method_exists($invoice, 'createOrUpdateStudentDebt')) {
             //     $invoice->createOrUpdateStudentDebt();
             //     Log::info("Updated student debt information for approved invoice.", ['invoice_id' => $invoice->id]);
             // }

            DB::commit();

            Log::info("Invoice #{$invoice->invoice_number} approved successfully.", [
                'invoice_id' => $invoice->id,
                'new_status' => $invoice->status,
                'journal_id' => $invoice->journal_id,
                'user_id' => auth()->id()
            ]);
            return redirect()->route('accounting.invoices.show', $invoice)
                ->with('success', 'Invoice approved and journal entry created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error approving invoice {$invoice->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(), // Log stack trace for debugging
                'user_id' => auth()->id()
            ]);
            // Redirect back to the show page with a specific error message
            return redirect()->route('accounting.invoices.show', $invoice)
                       // ->withInput() // Not usually needed for approval action
                       ->with('error', 'An error occurred while approving the invoice: ' . $e->getMessage());
        }
    }


    // --- Potential Future Methods ---

    /**
     * Download the invoice as a PDF.
     * Requires 'barryvdh/laravel-dompdf' package.
     *
     * @param Invoice $invoice
     * @return Response
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        $this->authorizeSchoolAccess($invoice);

        // Ensure necessary data is loaded (similar to show method)
        $invoice->load(['contact', 'items.account', 'items.taxRate', 'items.itemSource', 'createdBy']);
        // You might need to load school details if they appear on the PDF
        // $invoice->load('school'); // If relationship exists

        try {
            // Prepare data for the PDF view
            $data = [
                'invoice' => $invoice,
                // Add any other data needed for the PDF template (e.g., company info)
                // 'company' => Settings::getCompanyDetails($invoice->school_id),
            ];

            // Generate PDF using a dedicated Blade view (e.g., accounting.invoices.pdf)
            $pdf = Pdf::loadView('accounting.invoices.pdf', $data); // Ensure this view exists

            // Set paper size and orientation if needed
            // $pdf->setPaper('a4', 'portrait');

            // Generate a filename
            $filename = 'Invoice-' . $invoice->invoice_number . '.pdf';

            // Return the PDF for download
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error("Error generating PDF for invoice {$invoice->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return response("Error generating PDF: " . $e->getMessage(), 500); // Or redirect with error
        }
    }

    /**
     * Send the invoice email (requires setup).
     *
     * @param Invoice $invoice
     * @return RedirectResponse
     */
    // public function sendEmail(Invoice $invoice): RedirectResponse
    // {
    //     $this->authorizeSchoolAccess($invoice);
    //
    //     if (!in_array($invoice->status, ['unpaid', 'approved', 'sent', 'partial'])) { // Allow sending approved/unpaid invoices
    //         return redirect()->back()->with('error', 'Cannot email an invoice with status: ' . $invoice->status);
    //     }
    //
    //     if (!$invoice->contact || !$invoice->contact->email) {
    //         return redirect()->back()->with('error', 'Contact has no email address configured.');
    //     }
    //
    //     try {
    //         // Ensure Mail facade and InvoiceMail Mailable are imported
    //         Mail::to($invoice->contact->email)
    //             // ->cc(config('mail.invoice_cc_address')) // Optional CC
    //             ->send(new InvoiceMail($invoice)); // Pass the invoice to the Mailable
    //
    //         // Optionally update invoice status to 'sent'
    //         if ($invoice->status === 'unpaid' || $invoice->status === 'approved') {
    //             $invoice->status = 'sent';
    //             $invoice->save();
    //         }
    //
    //         Log::info("Invoice #{$invoice->invoice_number} emailed successfully.", ['invoice_id' => $invoice->id, 'recipient' => $invoice->contact->email, 'user_id' => auth()->id()]);
    //         return redirect()->route('accounting.invoices.show', $invoice)->with('success', 'Invoice emailed successfully.');
    //
    //     } catch (\Exception $e) {
    //         Log::error("Error sending invoice email for invoice {$invoice->id}: " . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //             'user_id' => auth()->id()
    //         ]);
    //         return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
    //     }
    // }


    /**
     * Void an approved invoice. Creates reversing journal entries.
     *
     * @param Invoice $invoice
     * @return RedirectResponse
     */
    // public function void(Invoice $invoice): RedirectResponse
    // {
    //     $this->authorizeSchoolAccess($invoice);
    //
    //     // Check if the invoice can be voided (e.g., must be approved/unpaid/sent, not paid/draft)
    //     if (!in_array($invoice->status, ['unpaid', 'sent', 'partial', 'overdue', 'approved'])) {
    //         return redirect()->back()->with('error', 'Cannot void an invoice with status: ' . $invoice->status);
    //     }
    //     // Prevent voiding if fully paid? Depends on business logic.
    //     // if ($invoice->amount_paid >= $invoice->total && $invoice->total > 0) {
    //     //    return redirect()->back()->with('error', 'Cannot void a fully paid invoice. Create a Credit Note instead.');
    //     // }
    //
    //     DB::beginTransaction();
    //     try {
    //         $originalStatus = $invoice->status;
    //         $voidDate = now()->toDateString(); // Or allow user to specify void date
    //
    //         // 1. Update Invoice Status
    //         $invoice->status = 'void';
    //         // Optionally store void reason/date if columns exist
    //         // $invoice->voided_at = now();
    //         // $invoice->void_reason = request('void_reason', 'Invoice Voided');
    //         $invoice->save();
    //
    //         // 2. Create Reversing Journal Entry
    //         if ($invoice->journal_id && $invoice->journal) {
    //              $this->createReversingJournalForVoid($invoice, $voidDate);
    //         } else {
    //              Log::warning("Invoice {$invoice->id} is being voided but has no associated journal to reverse.", ['user_id' => auth()->id()]);
    //              // Decide if this is an error or acceptable (e.g., if approved status didn't require journal)
    //         }
    //
    //         // 3. Optional: Reverse related actions (e.g., Student Debt)
    //         // if (method_exists($invoice, 'reverseStudentDebt')) {
    //         //     $invoice->reverseStudentDebt();
    //         // }
    //
    //         // 4. Optional: Reverse Stock Movements if applicable (requires careful implementation)
    //         // $this->reverseStockMovementsForVoid($invoice);
    //
    //         DB::commit();
    //
    //         Log::info("Invoice #{$invoice->invoice_number} voided successfully.", ['invoice_id' => $invoice->id, 'original_status' => $originalStatus, 'user_id' => auth()->id()]);
    //         return redirect()->route('accounting.invoices.show', $invoice)->with('success', 'Invoice voided successfully.');
    //
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Error voiding invoice {$invoice->id}: " . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //             'user_id' => auth()->id()
    //         ]);
    //         return redirect()->back()->with('error', 'An error occurred while voiding the invoice: ' . $e->getMessage());
    //     }
    // }

    /**
     * Helper to create a reversing journal entry for a voided invoice.
     * (This would be a private method called by the `void` method)
     */
    // private function createReversingJournalForVoid(Invoice $invoice, string $voidDate): void
    // {
    //     $originalJournal = $invoice->journal()->with('entries')->first(); // Reload with entries
    //     if (!$originalJournal) return; // Should have been checked before calling
    //
    //     $reversingJournal = Journal::create([
    //         'school_id' => $invoice->school_id,
    //         'journal_date' => $voidDate,
    //         'reference' => 'Void Invoice #' . $invoice->invoice_number,
    //         'description' => 'Reversal of journal entry for voided ' . $originalJournal->description,
    //         'status' => 'posted',
    //         'created_by' => auth()->id(),
    //         'document_type' => Invoice::class, // Still relates to the invoice
    //         'document_id' => $invoice->id,
    //         'is_reversal' => true, // Add a flag if your journal table supports it
    //         'reversed_journal_id' => $originalJournal->id, // Link back if supported
    //     ]);
    //
    //     foreach ($originalJournal->entries as $entry) {
    //         JournalEntry::create([
    //             'journal_id' => $reversingJournal->id,
    //             'account_id' => $entry->account_id,
    //             'debit' => $entry->credit, // Swap debit/credit
    //             'credit' => $entry->debit, // Swap debit/credit
    //             'description' => 'Reversal: ' . $entry->description,
    //             'contact_id' => $entry->contact_id, // Keep contact link if present
    //         ]);
    //     }
    //     Log::info("Created reversing journal entry for voided invoice.", ['invoice_id' => $invoice->id, 'original_journal_id' => $originalJournal->id, 'reversing_journal_id' => $reversingJournal->id]);
    // }

} // End of InvoicesController class