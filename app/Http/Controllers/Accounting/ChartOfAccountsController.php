<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountType;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accountTypes = AccountType::with('accounts')->get();
        return view('accounting.accounts.index', compact('accountTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accountTypes = AccountType::where('is_active', true)->get();
        return view('accounting.accounts.create', compact('accountTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_type_id' => 'required|exists:account_types,id',
            'account_code' => 'required|string|unique:chart_of_accounts',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'balance_date' => 'nullable|date',
            'is_bank_account' => 'nullable|boolean',
        ]);

        $account = ChartOfAccount::create([
            'account_type_id' => $request->account_type_id,
            'account_code' => $request->account_code,
            'name' => $request->name,
            'description' => $request->description,
            'opening_balance' => $request->opening_balance ?? 0,
            'balance_date' => $request->balance_date,
            'is_bank_account' => $request->has('is_bank_account'),
            'is_active' => true,
            'is_system' => false,
        ]);

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $account)
    {
        $account->load(['accountType', 'journalEntries.journal']);
        return view('accounting.accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartOfAccount $account)
    {
        $accountTypes = AccountType::where('is_active', true)->get();
        return view('accounting.accounts.edit', compact('account', 'accountTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $account)
    {
        $request->validate([
            'account_type_id' => 'required|exists:account_types,id',
            'account_code' => 'required|string|unique:chart_of_accounts,account_code,' . $account->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'balance_date' => 'nullable|date',
            'is_bank_account' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $account->update([
            'account_type_id' => $request->account_type_id,
            'account_code' => $request->account_code,
            'name' => $request->name,
            'description' => $request->description,
            'opening_balance' => $request->opening_balance ?? $account->opening_balance,
            'balance_date' => $request->balance_date ?? $account->balance_date,
            'is_bank_account' => $request->has('is_bank_account'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $account)
    {
        // Check if account has journal entries
        if ($account->journalEntries()->count() > 0) {
            return redirect()->route('accounting.accounts.index')
                ->with('error', 'Account cannot be deleted because it has journal entries.');
        }

        if ($account->is_system) {
            return redirect()->route('accounting.accounts.index')
                ->with('error', 'System accounts cannot be deleted.');
        }

        $account->delete();

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}