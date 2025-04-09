<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\HumanResources\Payslip; // Same namespace
// use App\Models\HumanResources\PayrollElement; // Same namespace

class PayslipItem extends Model
{
    use HasFactory;

    protected $table = 'payslip_items';

    protected $fillable = [
        'payslip_id',
        'payroll_element_id',
        'type',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // --- Relationships ---

    /**
     * Get the payslip this item belongs to.
     */
    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }

    /**
     * Get the payroll element definition this item is based on (optional).
     */
    public function payrollElement()
    {
        return $this->belongsTo(PayrollElement::class);
    }
}