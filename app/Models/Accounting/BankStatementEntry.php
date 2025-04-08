<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_reconciliation_id',
        'transaction_date',
        'description',
        'reference',
        'amount',
        'is_debit',
        'running_balance',
        'is_matched',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'running_balance' => 'decimal:2',
        'is_debit' => 'boolean',
        'is_matched' => 'boolean',
    ];

    /**
     * Get the reconciliation this statement entry belongs to.
     */
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class, 'bank_reconciliation_id');
    }
    
    /**
     * Get the associated reconciliation item if any
     */
    public function reconciliationItem()
    {
        return $this->hasOne(BankReconciliationItem::class, 'bank_statement_entry_id');
    }
    
    /**
     * Get signed amount (positive for credit, negative for debit)
     */
    public function getSignedAmountAttribute()
    {
        return $this->is_debit ? -$this->amount : $this->amount;
    }
}