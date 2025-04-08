<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_reconciliation_id',
        'cashbook_entry_id',
        'bank_statement_entry_id',
        'is_reconciled',
        'notes',
    ];

    protected $casts = [
        'is_reconciled' => 'boolean',
    ];

    /**
     * Get the reconciliation this item belongs to.
     */
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class, 'bank_reconciliation_id');
    }

    /**
     * Get the cashbook entry associated with this reconciliation item.
     */
    public function cashbookEntry(): BelongsTo
    {
        return $this->belongsTo(CashbookEntry::class);
    }

    /**
     * Get the bank statement entry associated with this reconciliation item.
     */
    public function statementEntry(): BelongsTo
    {
        return $this->belongsTo(BankStatementEntry::class, 'bank_statement_entry_id');
    }
}