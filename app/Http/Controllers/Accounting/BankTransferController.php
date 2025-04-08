<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankTransfer;
use App\Models\Accounting\CashbookEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankTransferController extends Controller
{
    /**
     * Display a listing of bank transfers.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transfers = BankTransfer::with(['fromAccount', 'toAccount'])
            ->orderBy('transfer_date', 'desc')
            ->paginate(15);
            
        return view('accounting.bank-transfers.index', compact('transfers'));
    }

    /**
     * Show the form for creating a new bank transfer.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('account_name')
            ->get();
            
        return view('accounting.bank-transfers.create', compact('bankAccounts'));
    }

    /**
     * Store a newly created bank transfer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transfer_date' => 'required|date',
            'from_account_id' => 'required|exists:bank_accounts,id',
            'to_account_id' => 'required|exists:bank_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);
        
        // Add created_by and status
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'completed';
        
        try {
            DB::beginTransaction();
            
            // Create the transfer record
            $transfer = BankTransfer::create($validated);
            
            // Create cashbook entries for both accounts
            // 1. Withdrawal from source account
            CashbookEntry::create([
                'bank_account_id' => $validated['from_account_id'],
                'transaction_date' => $validated['transfer_date'],
                'amount' => $validated['amount'],
                'is_debit' => true,
                'reference_number' => $validated['reference'],
                'description' => 'Transfer to ' . BankAccount::find($validated['to_account_id'])->account_name,
                'type' => 'transfer',
                'bank_transfer_id' => $transfer->id,
                'created_by' => auth()->id(),
            ]);
            
            // 2. Deposit to destination account
            CashbookEntry::create([
                'bank_account_id' => $validated['to_account_id'],
                'transaction_date' => $validated['transfer_date'],
                'amount' => $validated['amount'],
                'is_debit' => false,
                'reference_number' => $validated['reference'],
                'description' => 'Transfer from ' . BankAccount::find($validated['from_account_id'])->account_name,
                'type' => 'transfer',
                'bank_transfer_id' => $transfer->id,
                'created_by' => auth()->id(),
            ]);
            
            // Update bank account balances
            $fromAccount = BankAccount::find($validated['from_account_id']);
            $fromAccount->current_balance -= $validated['amount'];
            $fromAccount->save();
            
            $toAccount = BankAccount::find($validated['to_account_id']);
            $toAccount->current_balance += $validated['amount'];
            $toAccount->save();
            
            DB::commit();
            
            return redirect()->route('accounting.bank-transfers.index')
                ->with('success', 'Bank transfer created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create bank transfer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified bank transfer.
     *
     * @param  \App\Models\Accounting\BankTransfer  $bankTransfer
     * @return \Illuminate\Http\Response
     */
    public function show(BankTransfer $bankTransfer)
    {
        $bankTransfer->load(['fromAccount', 'toAccount', 'createdBy', 'cashbookEntries']);
        
        return view('accounting.bank-transfers.show', compact('bankTransfer'));
    }

    /**
     * Show the form for editing the specified bank transfer.
     * Note: Generally editing transfers should be limited since they impact account balances.
     *
     * @param  \App\Models\Accounting\BankTransfer  $bankTransfer
     * @return \Illuminate\Http\Response
     */
    public function edit(BankTransfer $bankTransfer)
    {
        // For security, only allow editing of recent transfers (e.g., same day)
        if ($bankTransfer->transfer_date->startOfDay() < now()->startOfDay()) {
            return redirect()->route('accounting.bank-transfers.show', $bankTransfer)
                ->with('error', 'Bank transfers can only be edited on the same day they were created.');
        }
        
        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('account_name')
            ->get();
            
        return view('accounting.bank-transfers.edit', compact('bankTransfer', 'bankAccounts'));
    }

    /**
     * Update the specified bank transfer in storage.
     * Note: This is a simplified version - in practice you'd need more complex
     * logic to revert the previous transaction and create new ones.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\BankTransfer  $bankTransfer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BankTransfer $bankTransfer)
    {
        // For security, only allow editing of recent transfers (e.g., same day)
        if ($bankTransfer->transfer_date->startOfDay() < now()->startOfDay()) {
            return redirect()->route('accounting.bank-transfers.show', $bankTransfer)
                ->with('error', 'Bank transfers can only be edited on the same day they were created.');
        }
        
        $validated = $request->validate([
            'transfer_date' => 'required|date',
            'reference' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update the transfer record (limited fields)
            $bankTransfer->update($validated);
            
            // Update cashbook entries with new date and reference
            foreach ($bankTransfer->cashbookEntries as $entry) {
                $entry->transaction_date = $validated['transfer_date'];
                $entry->reference_number = $validated['reference'];
                $entry->save();
            }
            
            DB::commit();
            
            return redirect()->route('accounting.bank-transfers.show', $bankTransfer)
                ->with('success', 'Bank transfer updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update bank transfer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified bank transfer from storage.
     * Note: This is typically not allowed for accounting integrity,
     * but can be implemented as a void/reverse operation.
     *
     * @param  \App\Models\Accounting\BankTransfer  $bankTransfer
     * @return \Illuminate\Http\Response
     */
    public function destroy(BankTransfer $bankTransfer)
    {
        // For security, only allow voiding of recent transfers (e.g., same day)
        if ($bankTransfer->transfer_date->startOfDay() < now()->startOfDay()) {
            return redirect()->route('accounting.bank-transfers.show', $bankTransfer)
                ->with('error', 'Bank transfers can only be voided on the same day they were created.');
        }
        
        try {
            DB::beginTransaction();
            
            // Reverse account balance changes
            $fromAccount = $bankTransfer->fromAccount;
            $fromAccount->current_balance += $bankTransfer->amount;
            $fromAccount->save();
            
            $toAccount = $bankTransfer->toAccount;
            $toAccount->current_balance -= $bankTransfer->amount;
            $toAccount->save();
            
            // Delete associated cashbook entries
            $bankTransfer->cashbookEntries()->delete();
            
            // Delete the transfer
            $bankTransfer->delete();
            
            DB::commit();
            
            return redirect()->route('accounting.bank-transfers.index')
                ->with('success', 'Bank transfer voided successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to void bank transfer: ' . $e->getMessage());
        }
    }
}