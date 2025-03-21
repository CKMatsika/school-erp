<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_type_id',
        'account_code',
        'name',
        'description',
        'is_active',
        'is_bank_account',
        'opening_balance',
        'balance_date',
        'is_system',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_bank_account' => 'boolean',
        'opening_balance' => 'decimal:2',
        'balance_date' => 'date',
        'is_system' => 'boolean',
    ];

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'account_id');
    }

    public function getCurrentBalance()
    {
        $debits = $this->journalEntries()->sum('debit');
        $credits = $this->journalEntries()->sum('credit');
        
        // Logic depends on account type
        if (in_array($this->accountType->code, ['ASSET', 'EXPENSE'])) {
            return $this->opening_balance + $debits - $credits;
        } else {
            return $this->opening_balance + $credits - $debits;
        }
    }
}