<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountType;
use App\Models\Accounting\ChartOfAccount; // Needed for delete check
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Needed for 'normal_balance' validation

class AccountTypeController extends Controller
{
    /**
     * Display a listing of the Account Types.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Fetch all account types, maybe order them
        $accountTypes = AccountType::orderBy('name')->get();

        return view('accounting.account_types.index', compact('accountTypes'));
    }

    /**
     * Show the form for creating a new Account Type.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Just show the create form view
        return view('accounting.account_types.create');
    }

    /**
     * Store a newly created Account Type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|uppercase|unique:account_types,code|max:50', // Ensure code is unique and uppercase
            'description' => 'nullable|string',
            'normal_balance' => ['required', Rule::in(['debit', 'credit'])],
            'is_active' => 'sometimes|boolean',
        ]);

        // Prepare data for creation
        $dataToCreate = $validatedData;
        $dataToCreate['code'] = strtoupper($validatedData['code']); // Ensure code is stored uppercase
        $dataToCreate['is_active'] = $request->has('is_active'); // Handle checkbox default
        $dataToCreate['is_system'] = false; // User-created types are never system types

        AccountType::create($dataToCreate);

        return redirect()->route('accounting.account-types.index')
                         ->with('success', 'Account Type created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * Note: Often not needed for simple types, index/edit might suffice.
     * Implement if you need a dedicated detail view.
     *
     * @param  \App\Models\Accounting\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function show(AccountType $accountType)
    {
        // Optional: If you need a dedicated view page for a single type
        // return view('accounting.account_types.show', compact('accountType'));
        return redirect()->route('accounting.account-types.index'); // Or redirect if no show view
    }

    /**
     * Show the form for editing the specified Account Type.
     *
     * @param  \App\Models\Accounting\AccountType  $accountType  (Route Model Binding)
     * @return \Illuminate\Http\Response
     */
    public function edit(AccountType $accountType)
    {
        // Pass the specific account type to the edit view
        return view('accounting.account_types.edit', compact('accountType'));
    }

    /**
     * Update the specified Account Type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\AccountType  $accountType (Route Model Binding)
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AccountType $accountType)
    {
        // Prevent editing core system types
        if ($accountType->is_system) {
            return redirect()->route('accounting.account-types.index')
                             ->with('error', 'System account types cannot be modified.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // Ensure code is unique, ignoring the current record ID
            'code' => ['required', 'string', 'uppercase', Rule::unique('account_types')->ignore($accountType->id), 'max:50'],
            'description' => 'nullable|string',
            'normal_balance' => ['required', Rule::in(['debit', 'credit'])],
            'is_active' => 'sometimes|boolean',
        ]);

        // Prepare data for update
        $dataToUpdate = $validatedData;
        $dataToUpdate['code'] = strtoupper($validatedData['code']); // Ensure code is stored uppercase
        $dataToUpdate['is_active'] = $request->has('is_active'); // Handle checkbox default

        $accountType->update($dataToUpdate);

        return redirect()->route('accounting.account-types.index')
                         ->with('success', 'Account Type updated successfully.');
    }

    /**
     * Remove the specified Account Type from storage.
     *
     * @param  \App\Models\Accounting\AccountType  $accountType (Route Model Binding)
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountType $accountType)
    {
        // Prevent deleting core system types
        if ($accountType->is_system) {
            return redirect()->route('accounting.account-types.index')
                             ->with('error', 'System account types cannot be deleted.');
        }

        // Prevent deleting if it's currently used by any Chart of Account record
        // This assumes you have the 'accounts' relationship defined in the AccountType model
        if ($accountType->accounts()->exists()) { // More efficient check
             return redirect()->route('accounting.account-types.index')
                             ->with('error', 'Cannot delete Account Type: It is currently assigned to one or more accounts in the Chart of Accounts.');
        }

        $accountType->delete();

        return redirect()->route('accounting.account-types.index')
                         ->with('success', 'Account Type deleted successfully.');
    }
}