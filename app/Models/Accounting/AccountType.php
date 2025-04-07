<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'normal_balance',
        'is_active',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get the accounts for the account type.
     */
    public function accounts()
    {
        return $this->hasMany(ChartOfAccount::class);
    }

    /**
     * Get the full list of account types or create defaults if none exist.
     */
    public static function getAccountTypes()
    {
        $types = self::all();
        
        if ($types->isEmpty()) {
            // Create default account types
            self::createDefaultAccountTypes();
            $types = self::all();
        }
        
        return $types;
    }

    /**
     * Create default account types
     */
    public static function createDefaultAccountTypes()
    {
        $defaults = [
            [
                'name' => 'Assets',
                'code' => 'ASSET',
                'description' => 'Resources owned by the business that have value',
                'normal_balance' => 'debit',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Liabilities',
                'code' => 'LIABILITY',
                'description' => 'What the business owes to others',
                'normal_balance' => 'credit',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Equity',
                'code' => 'EQUITY',
                'description' => 'Owner\'s interest in the business',
                'normal_balance' => 'credit',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Revenue',
                'code' => 'REVENUE',
                'description' => 'Income earned from normal business operations',
                'normal_balance' => 'credit',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Expenses',
                'code' => 'EXPENSE',
                'description' => 'Costs incurred in running the business',
                'normal_balance' => 'debit',
                'is_active' => true,
                'is_system' => true,
            ],
        ];

        foreach ($defaults as $type) {
            self::create($type);
        }
    }
}