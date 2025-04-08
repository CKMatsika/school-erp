<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class BankTransfer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transfer_date',
        'from_account_id',
        'to_account_id',
        'amount',
        'reference',
        'notes',
        'created_by',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the source account for this transfer.
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'from_account_id');
    }

    /**
     * Get the destination account for this transfer.
     */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'to_account_id');
    }

    /**
     * Get the user who created this transfer.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the cashbook entries associated with this transfer.
     */
    public function cashbookEntries(): HasMany
    {
        return $this->hasMany(CashbookEntry::class, 'bank_transfer_id');
    }
    
    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }
}