<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_name',
        'account_number',
        'bank_name',
        'branch_code',
        'swift_code',
        'current_balance',
        'opening_balance',
        'account_type',
        'currency',
        'is_active',
        'notes',
        'chart_of_account_id',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all cashbook entries for this bank account
     */
    public function cashbookEntries(): HasMany
    {
        return $this->hasMany(CashbookEntry::class);
    }

    /**
     * Get all reconciliations for this bank account
     */
    public function reconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class);
    }

    /**
     * Get all outgoing transfers from this account
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(BankTransfer::class, 'from_account_id');
    }

    /**
     * Get all incoming transfers to this account
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(BankTransfer::class, 'to_account_id');
    }

    /**
     * Get the chart of account associated with this bank account
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    /**
     * Get formatted account name with bank name
     */
    public function getFullAccountNameAttribute()
    {
        return "{$this->account_name} - {$this->bank_name} ({$this->account_number})";
    }
}