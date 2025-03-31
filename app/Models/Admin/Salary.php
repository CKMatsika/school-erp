<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staff_id',
        'payroll_id',
        'month',
        'year',
        'basic_salary',
        'work_days',
        'leave_days',
        'absent_days',
        'overtime_hours',
        'allowances', // JSON with allowance types and amounts
        'deductions', // JSON with deduction types and amounts
        'gross_salary',
        'total_deductions',
        'net_salary',
        'payment_date',
        'payment_method',
        'transaction_id',
        'remarks',
        'status', // 'pending', 'approved', 'paid', 'cancelled'
        'approved_by',
        'approval_date',
        'salary_slip_generated',
        'salary_slip_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'basic_salary' => 'decimal:2',
        'work_days' => 'decimal:1',
        'leave_days' => 'decimal:1',
        'absent_days' => 'decimal:1',
        'overtime_hours' => 'decimal:1',
        'allowances' => 'array',
        'deductions' => 'array',
        'gross_salary' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
        'approval_date' => 'date',
        'salary_slip_generated' => 'boolean',
    ];

    /**
     * Get the staff member for this salary.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the payroll for this salary.
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Get the staff member who approved this salary.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Scope a query to only include salaries for a specific staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $staffId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Scope a query to only include salaries for a specific month and year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $month
     * @param  int  $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMonthYear($query, $month, $year)
    {
        return $query->where('month', $month)
                    ->where('year', $year);
    }

    /**
     * Scope a query to only include salaries for a specific year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope a query to only include salaries with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include paid salaries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending or approved salaries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendingOrApproved($query)
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    /**
     * Get formatted month and year.
     *
     * @return string
     */
    public function getFormattedMonthYearAttribute()
    {
        return date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }

    /**
     * Calculate total earnings (basic salary + allowances).
     *
     * @return float
     */
    public function getTotalEarningsAttribute()
    {
        $allowancesTotal = 0;
        
        if (is_array($this->allowances)) {
            foreach ($this->allowances as $allowance => $amount) {
                $allowancesTotal += $amount;
            }
        }
        
        return $this->basic_salary + $allowancesTotal;
    }

    /**
     * Calculate total deductions.
     *
     * @return float
     */
    public function calculateTotalDeductions()
    {
        $total = 0;
        
        if (is_array($this->deductions)) {
            foreach ($this->deductions as $category => $amount) {
                if (is_numeric($amount)) {
                    $total += $amount;
                }
            }
        }
        
        $this->total_deductions = $total;
        return $total;
    }

    /**
     * Calculate net salary.
     *
     * @return float
     */
    public function calculateNetSalary()
    {
        $this->gross_salary = $this->total_earnings;
        $this->calculateTotalDeductions();
        $this->net_salary = $this->gross_salary - $this->total_deductions;
        
        return $this->net_salary;
    }

    /**
     * Approve this salary.
     *
     * @param  int  $approvedBy  Staff ID
     * @param  string|null  $remarks
     * @return bool
     */
    public function approve($approvedBy, $remarks = null)
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        $this->status = 'approved';
        $this->approved_by = $approvedBy;
        $this->approval_date = now();
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Approval remarks: {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Mark this salary as paid.
     *
     * @param  string  $paymentMethod
     * @param  string|null  $transactionId
     * @param  \DateTime|string|null  $paymentDate
     * @param  string|null  $remarks
     * @return bool
     */
    public function markAsPaid($paymentMethod, $transactionId = null, $paymentDate = null, $remarks = null)
    {
        if ($this->status !== 'approved') {
            return false;
        }
        
        $this->status = 'paid';
        $this->payment_method = $paymentMethod;
        $this->transaction_id = $transactionId;
        $this->payment_date = $paymentDate ?? now();
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Payment remarks: {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Cancel this salary.
     *
     * @param  string  $reason
     * @return bool
     */
    public function cancel($reason)
    {
        if (!in_array($this->status, ['pending', 'approved'])) {
            return false;
        }
        
        $this->status = 'cancelled';
        $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Cancellation reason: {$reason}";
        
        return $this->save();
    }

    /**
     * Generate salary slip.
     *
     * @return string|null  Path to generated slip
     */
    public function generateSalarySlip()
    {
        if (!in_array($this->status, ['approved', 'paid'])) {
            return null;
        }
        
        // This is a placeholder. The actual implementation would:
        // 1. Generate PDF or other format for the salary slip
        // 2. Save it to storage
        
        $fileName = 'salary_slip_' . $this->staff_id . '_' . $this->year . '_' . $this->month . '.pdf';
        $filePath = 'salary_slips/' . $fileName;
        
        // Update salary record
        $this->salary_slip_generated = true;
        $this->salary_slip_path = $filePath;
        $this->save();
        
        return $filePath;
    }

    /**
     * Add or update an allowance.
     *
     * @param  string  $allowanceType
     * @param  float  $amount
     * @return bool
     */
    public function updateAllowance($allowanceType, $amount)
    {
        $allowances = $this->allowances ?? [];
        $allowances[$allowanceType] = $amount;
        $this->allowances = $allowances;
        
        $this->calculateNetSalary();
        
        return $this->save();
    }

    /**
     * Add or update a deduction.
     *
     * @param  string  $deductionType
     * @param  float  $amount
     * @return bool
     */
    public function updateDeduction($deductionType, $amount)
    {
        $deductions = $this->deductions ?? [];
        $deductions[$deductionType] = $amount;
        $this->deductions = $deductions;
        
        $this->calculateNetSalary();
        
        return $this->save();
    }

    /**
     * Get overtime pay.
     *
     * @param  float|null  $hourlyRate
     * @return float
     */
    public function getOvertimePay($hourlyRate = null)
    {
        if (!$this->overtime_hours) {
            return 0;
        }
        
        if (!$hourlyRate) {
            // Calculate hourly rate based on basic salary
            // Assuming 160 work hours in a month (8 hours x 20 days)
            $hourlyRate = $this->basic_salary / 160;
        }
        
        return $this->overtime_hours * $hourlyRate * 1.5; // Assuming 1.5x for overtime
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'badge-warning';
            case 'approved':
                return 'badge-success';
            case 'paid':
                return 'badge-primary';
            case 'cancelled':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get the salary slip URL.
     *
     * @return string|null
     */
    public function getSalarySlipUrlAttribute()
    {
        return $this->salary_slip_path ? url('storage/' . $this->salary_slip_path) : null;
    }
}