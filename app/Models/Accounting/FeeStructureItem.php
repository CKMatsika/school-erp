<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo

// Import the related models for clarity and type hinting
use App\Models\Accounting\Account;
use App\Models\Accounting\FeeCategory;
use App\Models\Accounting\FeeStructure;


class FeeStructureItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fee_structure_id',
        'fee_category_id',
        'grade_level',      // Added based on previous context, remove if not needed
        'name',             // Added based on previous 'name' error context
        'amount',
        'frequency',        // Added based on previous context, remove if not needed
        'due_date',         // Added based on previous context, remove if not needed
        'income_account_id', // <-- Added foreign key for income account
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * Get the fee structure that owns the item.
     */
    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    /**
     * Get the fee category associated with this item.
     */
    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    /**
     * Get the income account associated with this fee item.
     * <----- NEW RELATIONSHIP ----->
     */
    public function incomeAccount(): BelongsTo
    {
        // Assumes the foreign key column is 'income_account_id'
        // Assumes the related model is App\Models\Accounting\Account
        return $this->belongsTo(Account::class, 'income_account_id');
    }

    // Note: I also added 'name', 'grade_level', 'frequency', 'due_date'
    // back to $fillable based on potential context from your previous errors/schema.
    // Please REVIEW and REMOVE any fields from $fillable that are NOT actually
    // present in your 'fee_structure_items' table or shouldn't be mass assignable.
}