<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankReconciliation;
use App\Models\Accounting\BankReconciliationItem;
use App\Models\Accounting\BankStatementEntry;
use App\Models\Accounting\CashbookEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BankReconciliationController extends Controller
{
    /**
     * Display a listing of bank reconciliations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reconciliations = BankReconciliation::with(['bankAccount'])
            ->orderBy('statement_date', 'desc')
            ->paginate(15);
            
        return view('accounting.bank-reconciliation.index', compact('reconciliations'));
    }

    /**
     * Show the form for creating a new reconciliation.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('account_name')
            ->get();
            
        return view('accounting.bank-reconciliation.create', compact('bankAccounts'));
    }

    /**
     * Store a newly created reconciliation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'statement_date' => 'required|date',
            'statement_balance' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);
        
        // Get the current balance from the system for this account
        $bankAccount = BankAccount::findOrFail($validated['bank_account_id']);
        $validated['system_balance'] = $bankAccount->current_balance;
        $validated['difference'] = $validated['statement_balance'] - $validated['system_balance'];
        $validated['status'] = 'draft';
        
        try {
            DB::beginTransaction();
            
            $reconciliation = BankReconciliation::create($validated);
            
            // Find unreconciled entries for the account up to statement date
            $unreconciled = CashbookEntry::where('bank_account_id', $bankAccount->id)
                ->where('transaction_date', '<=', $validated['statement_date'])
                ->where('is_reconciled', false)
                ->get();
                
            // Create reconciliation items for each unreconciled entry
            foreach ($unreconciled as $entry) {
                BankReconciliationItem::create([
                    'bank_reconciliation_id' => $reconciliation->id,
                    'cashbook_entry_id' => $entry->id,
                    'is_reconciled' => false,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('accounting.bank-reconciliation.show', $reconciliation)
                ->with('success', 'Bank reconciliation started successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create reconciliation: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified reconciliation.
     *
     * @param  \App\Models\Accounting\BankReconciliation  $bankReconciliation
     * @return \Illuminate\Http\Response
     */
    public function show(BankReconciliation $bankReconciliation)
    {
        // Load relationships
        $bankReconciliation->load(['bankAccount', 'items.cashbookEntry', 'items.statementEntry', 'statementEntries']);
        
        // Get unmatched items from both sides
        $unmatchedSystemEntries = $bankReconciliation->items()
            ->where('is_reconciled', false)
            ->whereNull('bank_statement_entry_id')
            ->with('cashbookEntry')
            ->get();
            
        $unmatchedStatementEntries = $bankReconciliation->statementEntries()
            ->where('is_matched', false)
            ->get();
            
        // Get matched items
        $matchedItems = $bankReconciliation->items()
            ->where('is_reconciled', true)
            ->whereNotNull('bank_statement_entry_id')
            ->with(['cashbookEntry', 'statementEntry'])
            ->get();
            
        return view('accounting.bank-reconciliation.show', compact(
            'bankReconciliation',
            'unmatchedSystemEntries',
            'unmatchedStatementEntries',
            'matchedItems'
        ));
    }

    /**
     * Show the match screen for the specified reconciliation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function match($id)
    {
        $reconciliation = BankReconciliation::with(['bankAccount', 'items.cashbookEntry', 'statementEntries'])
            ->findOrFail($id);
            
        if ($reconciliation->status === 'completed') {
            return redirect()->route('accounting.bank-reconciliation.show', $reconciliation)
                ->with('error', 'This reconciliation is already completed and cannot be modified.');
        }
        
        // Get unmatched items from both sides
        $unmatchedSystemEntries = $reconciliation->items()
            ->where('is_reconciled', false)
            ->whereNull('bank_statement_entry_id')
            ->with('cashbookEntry')
            ->get()
            ->sortBy('cashbookEntry.transaction_date');
            
        $unmatchedStatementEntries = $reconciliation->statementEntries()
            ->where('is_matched', false)
            ->orderBy('transaction_date')
            ->get();
            
        return view('accounting.bank-reconciliation.match', compact(
            'reconciliation',
            'unmatchedSystemEntries',
            'unmatchedStatementEntries'
        ));
    }

    /**
     * Import a bank statement for a reconciliation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function importStatement(Request $request, $id)
    {
        $request->validate([
            'statement_file' => 'required|file|mimes:csv,txt,ofx,qfx|max:10240',
            'date_format' => 'required|string',
        ]);
        
        $reconciliation = BankReconciliation::findOrFail($id);
        
        if ($reconciliation->status === 'completed') {
            return back()->with('error', 'This reconciliation is already completed and cannot be modified.');
        }
        
        // Store the file temporarily
        $file = $request->file('statement_file');
        $path = $file->storeAs('temp', Str::random(40) . '.' . $file->getClientOriginalExtension());
        
        try {
            DB::beginTransaction();
            
            // Get file contents
            $contents = Storage::get($path);
            $fileType = strtolower($file->getClientOriginalExtension());
            
            if ($fileType === 'csv' || $fileType === 'txt') {
                $this->importCsvStatement($reconciliation, $contents, $request->date_format);
            } elseif ($fileType === 'ofx' || $fileType === 'qfx') {
                $this->importOfxStatement($reconciliation, $contents);
            }
            
            // Clean up
            Storage::delete($path);
            
            // Update difference
            $reconciliation->status = 'in_progress';
            $reconciliation->save();
            
            DB::commit();
            
            return redirect()->route('accounting.bank-reconciliation.match', $reconciliation)
                ->with('success', 'Statement imported successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::delete($path);
            return back()->with('error', 'Failed to import statement: ' . $e->getMessage());
        }
    }

    /**
     * Import statement from CSV format
     */
    private function importCsvStatement(BankReconciliation $reconciliation, $contents, $dateFormat)
    {
        // This would need to be customized based on the bank's CSV format
        // Basic implementation assuming standard CSV with headers:
        $lines = explode("\n", $contents);
        $headers = str_getcsv(array_shift($lines));
        
        // Map headers to expected column indices
        $dateIndex = array_search('Date', $headers);
        $descIndex = array_search('Description', $headers);
        $refIndex = array_search('Reference', $headers);
        $debitIndex = array_search('Debit', $headers);
        $creditIndex = array_search('Credit', $headers);
        $balanceIndex = array_search('Balance', $headers);
        
        if ($dateIndex === false || ($debitIndex === false && $creditIndex === false)) {
            throw new \Exception('CSV format not recognized. Required headers: Date, and either Debit or Credit.');
        }
        
        // Parse each line
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $data = str_getcsv($line);
            if (count($data) < max($dateIndex, $debitIndex, $creditIndex) + 1) continue;
            
            // Parse date based on format
            $date = \DateTime::createFromFormat($dateFormat, $data[$dateIndex]);
            if (!$date) continue;
            
            // Determine amount and type
            $isDebit = false;
            $amount = 0;
            
            if ($debitIndex !== false && !empty($data[$debitIndex])) {
                $amount = abs(floatval(str_replace(['$', ','], '', $data[$debitIndex])));
                $isDebit = true;
            } elseif ($creditIndex !== false && !empty($data[$creditIndex])) {
                $amount = abs(floatval(str_replace(['$', ','], '', $data[$creditIndex])));
                $isDebit = false;
            }
            
            if ($amount <= 0) continue; // Skip zero or invalid amounts
            
            // Get running balance if available
            $runningBalance = null;
            if ($balanceIndex !== false && !empty($data[$balanceIndex])) {
                $runningBalance = floatval(str_replace(['$', ','], '', $data[$balanceIndex]));
            }
            
            // Create statement entry
            BankStatementEntry::create([
                'bank_reconciliation_id' => $reconciliation->id,
                'transaction_date' => $date->format('Y-m-d'),
                'description' => $descIndex !== false ? $data[$descIndex] : '',
                'reference' => $refIndex !== false ? $data[$refIndex] : '',
                'amount' => $amount,
                'is_debit' => $isDebit,
                'running_balance' => $runningBalance,
                'is_matched' => false,
            ]);
        }
    }

    /**
     * Import statement from OFX/QFX format
     */
    private function importOfxStatement(BankReconciliation $reconciliation, $contents)
    {
        // OFX parsing would require a library
        // This is a placeholder - you would implement the actual parsing
        throw new \Exception('OFX/QFX import not implemented.');
    }

    /**
     * Confirm and complete a reconciliation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function confirm(Request $request, $id)
    {
        $reconciliation = BankReconciliation::findOrFail($id);
        
        if ($reconciliation->status === 'completed') {
            return back()->with('error', 'This reconciliation is already completed.');
        }
        
        // Check if reconciliation is balanced
        $difference = $reconciliation->statement_balance - $reconciliation->system_balance;
        if (abs($difference) > 0.01) {
            return back()->with('error', 'Cannot complete reconciliation: the difference must be zero.');
        }
        
        try {
            DB::beginTransaction();
            
            // Mark all reconciliation items as reconciled
            foreach ($reconciliation->items as $item) {
                if ($item->is_reconciled) {
                    // Mark the corresponding cashbook entry as reconciled
                    $item->cashbookEntry->is_reconciled = true;
                    $item->cashbookEntry->save();
                }
            }
            
            // Update reconciliation status
            $reconciliation->status = 'completed';
            $reconciliation->completed_at = now();
            $reconciliation->completed_by = auth()->id();
            $reconciliation->save();
            
            // Update bank account balance to match statement
            $bankAccount = $reconciliation->bankAccount;
            $bankAccount->current_balance = $reconciliation->statement_balance;
            $bankAccount->save();
            
            DB::commit();
            
            return redirect()->route('accounting.bank-reconciliation.show', $reconciliation)
                ->with('success', 'Reconciliation completed successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete reconciliation: ' . $e->getMessage());
        }
    }

    /**
     * Update reconciliation matches via AJAX
     * This would be an AJAX endpoint for the matching screen
     */
    public function updateMatch(Request $request, $id)
    {
        $request->validate([
            'system_entry_id' => 'required|exists:bank_reconciliation_items,id',
            'statement_entry_id' => 'required|exists:bank_statement_entries,id',
            'action' => 'required|in:match,unmatch',
        ]);
        
        $reconciliation = BankReconciliation::findOrFail($id);
        
        if ($reconciliation->status === 'completed') {
            return response()->json(['error' => 'This reconciliation is already completed.'], 422);
        }
        
        try {
            DB::beginTransaction();
            
            if ($request->action === 'match') {
                // Match the entries
                $reconciliationItem = BankReconciliationItem::findOrFail($request->system_entry_id);
                $statementEntry = BankStatementEntry::findOrFail($request->statement_entry_id);
                
                // Ensure they belong to this reconciliation
                if ($reconciliationItem->bank_reconciliation_id != $id || $statementEntry->bank_reconciliation_id != $id) {
                    throw new \Exception('Invalid entries for this reconciliation.');
                }
                
                // Update the reconciliation item
                $reconciliationItem->bank_statement_entry_id = $statementEntry->id;
                $reconciliationItem->is_reconciled = true;
                $reconciliationItem->save();
                
                // Update the statement entry
                $statementEntry->is_matched = true;
                $statementEntry->save();
                
            } else {
                // Unmatch the entries
                $reconciliationItem = BankReconciliationItem::findOrFail($request->system_entry_id);
                
                // Ensure it belongs to this reconciliation
                if ($reconciliationItem->bank_reconciliation_id != $id) {
                    throw new \Exception('Invalid entry for this reconciliation.');
                }
                
                // Get the statement entry before unmatching
                $statementEntry = null;
                if ($reconciliationItem->bank_statement_entry_id) {
                    $statementEntry = BankStatementEntry::find($reconciliationItem->bank_statement_entry_id);
                }
                // Unmatch the reconciliation item
                $reconciliationItem->bank_statement_entry_id = null;
                $reconciliationItem->is_reconciled = false;
                $reconciliationItem->save();
                
                // Update the statement entry if found
                if ($statementEntry) {
                    $statementEntry->is_matched = false;
                    $statementEntry->save();
                }
            }
            
            // Update reconciliation figures
            $this->updateReconciliationFigures($reconciliation);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $request->action === 'match' ? 'Entries matched successfully.' : 'Entries unmatched successfully.',
                'difference' => $reconciliation->difference,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    
    /**
     * Update reconciliation balance figures
     */
    private function updateReconciliationFigures(BankReconciliation $reconciliation)
    {
        // Calculate total of reconciled system entries
        $reconciledSystemTotal = $reconciliation->items()
            ->where('is_reconciled', true)
            ->join('cashbook_entries', 'cashbook_entries.id', '=', 'bank_reconciliation_items.cashbook_entry_id')
            ->select(DB::raw('SUM(CASE WHEN cashbook_entries.is_debit = 1 THEN -cashbook_entries.amount ELSE cashbook_entries.amount END) as total'))
            ->value('total') ?? 0;
            
        // Update system balance (starting balance + reconciled entries)
        $bankAccount = $reconciliation->bankAccount;
        $startingBalance = $bankAccount->opening_balance;
        $reconciliation->system_balance = $startingBalance + $reconciledSystemTotal;
        
        // Calculate difference
        $reconciliation->difference = $reconciliation->statement_balance - $reconciliation->system_balance;
        $reconciliation->save();
    }
}