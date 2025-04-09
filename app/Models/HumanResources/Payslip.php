<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Assuming User model namespace
use App\Models\Accounting\Journal; // Assuming Journal model namespace
// use App\Models\HumanResources\Staff; // Same namespace

class Payslip extends Model
{
    use HasFactory;

    protected $table = 'payslips';

    protected $fillable = [
        'staff_id',
        'pay_period',
        'pay_period_start',
        'pay_period_end',
        'basic_salary',
        'total_allowances',
        'total_deductions',
        'gross_pay',
        'net_pay',
        'status',
        'processed_at',
        'paid_at',
        'processed_by',
        'journal_id',
    ];

     protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'processed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // --- Relationships ---

    /**
     * Get the staff member this payslip belongs to.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the items (allowances/deductions) included in this payslip.
     */
    public function items()
    {
        return $this->hasMany(PayslipItem::class);
    }

    /**
     * Get the user who processed this payslip.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the accounting journal entry associated with this payslip (if posted).
     */
    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }
}