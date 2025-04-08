<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the bank accounts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bankAccounts = BankAccount::with('chartOfAccount')->orderBy('account_name')->get();
        
        // Calculate total balance across all accounts
        $totalBalance = $bankAccounts->sum('current_balance');
        
        return view('accounting.bank-accounts.index', compact('bankAccounts', 'totalBalance'));
    }

    /**
     * Show the form for creating a new bank account.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get chart of accounts that are of type 'bank' or 'cash'
        $chartOfAccounts = ChartOfAccount::whereHas('accountType', function($query) {
            $query->whereIn('name', ['Bank', 'Cash', 'Current Asset']);
        })->orderBy('name')->get();
        
        return view('accounting.bank-accounts.create', compact('chartOfAccounts'));
    }

    /**
     * Store a newly created bank account in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'bank_name' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:50',
            'opening_balance' => 'required|numeric|min:0',
            'account_type' => 'required|string|in:checking,savings,credit,cash',
            'currency' => 'required|string|size:3',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);
        
        // Set current balance equal to opening balance initially
        $validated['current_balance'] = $validated['opening_balance'];
        $validated['is_active'] = $request->has('is_active');
        
        try {
            DB::beginTransaction();
            
            // Create the bank account
            $bankAccount = BankAccount::create($validated);
            
            // If there's an opening balance, we may need to create a journal entry here
            // This is accounting-system-specific logic
            
            DB::commit();
            
            return redirect()->route('accounting.bank-accounts.index')
                ->with('success', 'Bank account created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create bank account: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified bank account.
     *
     * @param  \App\Models\Accounting\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function show(BankAccount $bankAccount)
    {
        // Load recent transactions for this account
        $transactions = $bankAccount->cashbookEntries()
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
            
        // Get recent reconciliations
        $reconciliations = $bankAccount->reconciliations()
            ->orderBy('statement_date', 'desc')
            ->limit(10)
            ->get();
            
        // Get transfer activity
        $transfers = collect();
        $transfers = $transfers->merge($bankAccount->incomingTransfers)
            ->merge($bankAccount->outgoingTransfers)
            ->sortByDesc('transfer_date')
            ->take(20);
            
        return view('accounting.bank-accounts.show', compact(
            'bankAccount', 
            'transactions', 
            'reconciliations', 
            'transfers'
        ));
    }

    /**
     * Show the form for editing the specified bank account.
     *
     * @param  \App\Models\Accounting\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function edit(BankAccount $bankAccount)
    {
        // Get chart of accounts that are of type 'bank' or 'cash'
        $chartOfAccounts = ChartOfAccount::whereHas('accountType', function($query) {
            $query->whereIn('name', ['Bank', 'Cash', 'Current Asset']);
        })->orderBy('name')->get();
        
        return view('accounting.bank-accounts.edit', compact('bankAccount', 'chartOfAccounts'));
    }

    /**
     * Update the specified bank account in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'bank_name' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:50',
            'account_type' => 'required|string|in:checking,savings,credit,cash',
            'currency' => 'required|string|size:3',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        
        // Don't allow direct editing of balances to maintain integrity
        // If balance adjustment is needed, it should be done via a journal entry
        
        $bankAccount->update($validated);
        
        return redirect()->route('accounting.bank-accounts.index')
            ->with('success', 'Bank account updated successfully.');
    }

    /**
     * Remove the specified bank account from storage.
     *
     * @param  \App\Models\Accounting\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy(BankAccount $bankAccount)
    {
        // Check if there are any transactions, don't allow deletion if there are
        if ($bankAccount->cashbookEntries()->count() > 0 || 
            $bankAccount->reconciliations()->count() > 0 ||
            $bankAccount->incomingTransfers()->count() > 0 ||
            $bankAccount->outgoingTransfers()->count() > 0) {
            return back()->with('error', 'Cannot delete bank account with transaction history.');
        }
        
        $bankAccount->delete();
        
        return redirect()->route('accounting.bank-accounts.index')
            ->with('success', 'Bank account deleted successfully.');
    }
}