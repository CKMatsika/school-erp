<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class BankReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'statement_date',
        'statement_balance',
        'system_balance',
        'difference',
        'status',
        'notes',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'statement_balance' => 'decimal:2',
        'system_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the bank account associated with this reconciliation.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the user who completed this reconciliation.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get the reconciliation items associated with this reconciliation.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BankReconciliationItem::class);
    }

    /**
     * Get the statement entries associated with this reconciliation.
     */
    public function statementEntries(): HasMany
    {
        return $this->hasMany(BankStatementEntry::class);
    }
    
    /**
     * Check if the reconciliation is balanced
     */
    public function isBalanced(): bool
    {
        return abs($this->difference) < 0.01; // Allow for tiny rounding differences
    }
    
    /**
     * Calculate the current difference between statement and system
     */
    public function calculateDifference()
    {
        $this->difference = $this->statement_balance - $this->system_balance;
        return $this->difference;
    }
    
    /**
     * Get status label for display
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}