<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollElement extends Model
{
    use HasFactory;

    protected $table = 'payroll_elements';

    protected $fillable = [
        'name',
        'type',
        'calculation_type',
        'default_amount_or_rate',
        'is_taxable',
        'is_active',
        'description',
    ];

    protected $casts = [
        'default_amount_or_rate' => 'decimal:4', // Allow 4 decimal places for rates
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Staff members this element is assigned to.
     */
    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'staff_payroll_elements')
                    ->withPivot('amount_or_rate', 'start_date', 'end_date')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include active elements.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}