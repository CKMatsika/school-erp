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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with('contact')->orderBy('created_at', 'desc')->get();
        return view('accounting.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $contacts = Contact::where('is_active', true)->get();
        $taxRates = TaxRate::where('is_active', true)->get();
        $incomeAccounts = ChartOfAccount::whereHas('accountType', function($query) {
            $query->where('code', 'INCOME');
        })->where('is_active', true)->get();
        
        return view('accounting.invoices.create', compact('contacts', 'taxRates', 'incomeAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:accounting_contacts,id',
            'invoice_number' => 'required|string|unique:invoices',
            'reference' => 'nullable|string',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        // Get school ID if available
        $schoolId = null;
        if (auth()->user()->school) {
            $schoolId = auth()->user()->school->id;
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the invoice
            $invoice = Invoice::create([
                'school_id' => $schoolId,
                'contact_id' => $request->contact_id,
                'invoice_number' => $request->invoice_number,
                'reference' => $request->reference,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'status' => 'draft', // Default status
                'created_by' => auth()->id(),
            ]);

            // Create the invoice items
            foreach ($request->items as $item) {
                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_rate_id' => $item['tax_rate_id'] ?? null,
                    'account_id' => $item['account_id'],
                ]);

                // Calculate tax amount and total
                $invoiceItem->calculateTotals();
            }

            // Calculate invoice totals
            $invoice->calculateTotals();

            // Create the journal entry for this invoice
            $this->createJournalForInvoice($invoice);

            DB::commit();

            return redirect()->route('accounting.invoices.index')
                ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['contact', 'items.account', 'items.taxRate', 'creator']);
        return view('accounting.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('accounting.invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $invoice->load(['contact', 'items.account', 'items.taxRate']);
        
        $contacts = Contact::where('is_active', true)->get();
        $taxRates = TaxRate::where('is_active', true)->get();
        $incomeAccounts = ChartOfAccount::whereHas('accountType', function($query) {
            $query->where('code', 'INCOME');
        })->where('is_active', true)->get();
        
        return view('accounting.invoices.edit', compact('invoice', 'contacts', 'taxRates', 'incomeAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('accounting.invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $request->validate([
            'contact_id' => 'required|exists:accounting_contacts,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number,' . $invoice->id,
            'reference' => 'nullable|string',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'status' => 'required|in:draft,sent',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the invoice
            $invoice->update([
                'contact_id' => $request->contact_id,
                'invoice_number' => $request->invoice_number,
                'reference' => $request->reference,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'status' => $request->status,
            ]);

            // Get the IDs of existing items
            $existingItemIds = $invoice->items->pluck('id')->toArray();
            $updatedItemIds = [];

            // Update or create the invoice items
            foreach ($request->items as $item) {
                if (isset($item['id']) && in_array($item['id'], $existingItemIds)) {
                    // Update existing item
                    $invoiceItem = InvoiceItem::find($item['id']);
                    $invoiceItem->update([
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax_rate_id' => $item['tax_rate_id'] ?? null,
                        'account_id' => $item['account_id'],
                    ]);
                    $updatedItemIds[] = $item['id'];
                } else {
                    // Create new item
                    $invoiceItem = InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax_rate_id' => $item['tax_rate_id'] ?? null,
                        'account_id' => $item['account_id'],
                    ]);
                    $updatedItemIds[] = $invoiceItem->id;
                }

                // Calculate tax amount and total
                $invoiceItem->calculateTotals();
            }

            // Delete removed items
            foreach ($existingItemIds as $itemId) {
                if (!in_array($itemId, $updatedItemIds)) {
                    InvoiceItem::destroy($itemId);
                }
            }

            // Calculate invoice totals
            $invoice->calculateTotals();

            // Update or create the journal entry for this invoice
            if ($invoice->status === 'sent') {
                $this->createJournalForInvoice($invoice);
            }

            DB::commit();

            return redirect()->route('accounting.invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('accounting.invoices.index')
                ->with('error', 'Only draft invoices can be deleted.');
        }

        if ($invoice->payments()->count() > 0) {
            return redirect()->route('accounting.invoices.index')
                ->with('error', 'Invoice cannot be deleted because it has payments.');
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Delete the invoice items
            $invoice->items()->delete();
            
            // Delete the invoice
            $invoice->delete();

            DB::commit();

            return redirect()->route('accounting.invoices.index')
                ->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('accounting.invoices.index')
                ->with('error', 'Error deleting invoice: ' . $e->getMessage());
        }
    }

    /**
     * Print the invoice.
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['contact', 'items.account', 'items.taxRate', 'creator']);
        return view('accounting.invoices.print', compact('invoice'));
    }

    /**
     * Email the invoice to the contact.
     */
    public function email(Invoice $invoice)
    {
        // Implementation will depend on your email setup
        return redirect()->route('accounting.invoices.show', $invoice)
            ->with('success', 'Invoice email sent successfully.');
    }

    /**
     * Create a journal entry for an invoice.
     */
    private function createJournalForInvoice(Invoice $invoice)
    {
        // If there's already a journal for this invoice, delete it
        if ($invoice->journal) {
            $invoice->journal->entries()->delete();
            $invoice->journal->delete();
        }

        // Create a new journal entry
        $journal = Journal::create([
            'school_id' => $invoice->school_id,
            'reference' => 'Invoice #' . $invoice->invoice_number,
            'journal_date' => $invoice->issue_date,
            'description' => 'Invoice created for ' . $invoice->contact->name,
            'status' => 'posted',
            'created_by' => auth()->id(),
            'document_type' => 'invoice',
            'document_id' => $invoice->id,
        ]);

        // Create journal entries for each invoice item
        foreach ($invoice->items as $item) {
            // Debit Accounts Receivable
            JournalEntry::create([
                'journal_id' => $journal->id,
                'account_id' => ChartOfAccount::where('account_code', '1100')->first()->id, // Accounts Receivable
                'debit' => $item->total,
                'credit' => 0,
                'description' => $item->name,
                'contact_id' => $invoice->contact_id,
            ]);

            // Credit Income Account
            JournalEntry::create([
                'journal_id' => $journal->id,
                'account_id' => $item->account_id,
                'debit' => 0,
                'credit' => $item->total,
                'description' => $item->name,
                'contact_id' => $invoice->contact_id,
            ]);
        }

        return $journal;
    }
}