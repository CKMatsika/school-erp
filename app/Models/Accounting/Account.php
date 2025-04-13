<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Assuming you have these models too:
use App\Models\Accounting\AccountType;
use App\Models\School; // Assuming multi-tenancy based on School ERP context

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * Adjust this list based on your actual database columns for the 'accounts' table.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',         // If multi-tenant
        'account_type_id',
        'name',
        'account_code',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the account type that owns the account.
     */
    public function accountType(): BelongsTo
    {
        // Assumes AccountType model exists at App\Models\Accounting\AccountType
        // Assumes foreign key is 'account_type_id'
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Get the school that owns the account (if multi-tenant).
     */
    public function school(): BelongsTo
    {
        // Assumes School model exists at App\Models\School
        // Assumes foreign key is 'school_id'
        return $this->belongsTo(School::class);
    }

     /**
      * Get the fee structure items associated with this account (as income account).
      * This is the inverse of the relationship defined in FeeStructureItem.
      */
     public function feeStructureItems(): HasMany
     {
         // Assumes the foreign key in 'fee_structure_items' is 'income_account_id'
         return $this->hasMany(FeeStructureItem::class, 'income_account_id');
     }

    // Add other relationships here as needed, e.g., to Journal Entries, Ledger, etc.
    // public function journalEntries(): HasMany
    // {
    //     // Example assuming a JournalEntry model and appropriate foreign keys
    //     // return $this->hasMany(JournalEntry::class, 'debit_account_id'); // Or credit, or both via pivot?
    // }
}