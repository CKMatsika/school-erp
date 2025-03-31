<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staff_id',
        'loan_type', // 'salary-advance', 'education', 'housing', 'personal', 'medical', 'other'
        'amount',
        'interest_rate',
        'loan_date',
        'start_repayment_date',
        'end_repayment_date',
        'tenure_months',
        'repayment_amount',
        'repayment_frequency', // 'monthly', 'quarterly', 'semi-annually', 'annually'
        'purpose',
        'reference_number',
        'status', // 'pending', 'approved', 'rejected', 'active', 'completed', 'defaulted', 'cancelled'
        'approved_by',
        'approval_date',
        'remarks',
        'guarantor_id',
        'documents',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'loan_date' => 'date',
        'start_repayment_date' => 'date',
        'end_repayment_date' => 'date',
        'tenure_months' => 'integer',
        'repayment_amount' => 'decimal:2',
        'approval_date' => 'date',
        'documents' => 'array',
    ];

    /**
     * Get the staff member who took the loan.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the staff member who approved the loan.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Get the staff member who guaranteed the loan.
     */
    public function guarantor()
    {
        return $this->belongsTo(Staff::class, 'guarantor_id');
    }

    /**
     * Get the staff member who created the loan.
     */
    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the repayments for the loan.
     */
    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    /**
     * Scope a query to only include loans with a specific status.
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
     * Scope a query to only include pending loans.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include active loans.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include completed loans.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include defaulted loans.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaulted($query)
    {
        return $query->where('status', 'defaulted');
    }

    /**
     * Scope a query to only include loans for a specific staff.
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
     * Scope a query to only include loans of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('loan_type', $type);
    }

    /**
     * Calculate total repayment amount (principal + interest).
     *
     * @return float
     */
    public function calculateTotalRepaymentAmount()
    {
        // Simple interest calculation for the entire tenure
        $principal = $this->amount;
        $rate = $this->interest_rate / 100; // Convert percentage to decimal
        $time = $this->tenure_months / 12; // Convert months to years
        
        $interest = $principal * $rate * $time;
        $totalAmount = $principal + $interest;
        
        return $totalAmount;
    }

    /**
     * Calculate monthly repayment amount.
     *
     * @return float
     */
    public function calculateMonthlyRepaymentAmount()
    {
        $totalAmount = $this->calculateTotalRepaymentAmount();
        $monthlyAmount = $totalAmount / $this->tenure_months;
        
        return round($monthlyAmount, 2);
    }

    /**
     * Calculate repayment schedule.
     *
     * @return array
     */
    public function calculateRepaymentSchedule()
    {
        $schedule = [];
        $totalAmount = $this->calculateTotalRepaymentAmount();
        $installmentAmount = $this->calculateMonthlyRepaymentAmount();
        $balance = $totalAmount;
        $date = $this->start_repayment_date;
        
        for ($i = 1; $i <= $this->tenure_months; $i++) {
            $balance -= $installmentAmount;
            
            $schedule[] = [
                'installment_number' => $i,
                'due_date' => (clone $date)->format('Y-m-d'),
                'amount' => $installmentAmount,
                'balance' => max(0, round($balance, 2)),
            ];
            
            // Move to next month
            $date->addMonth();
        }
        
        return $schedule;
    }

    /**
     * Calculate total amount paid so far.
     *
     * @return float
     */
    public function getTotalPaidAttribute()
    {
        return $this->repayments()->sum('amount');
    }

    /**
     * Calculate remaining balance.
     *
     * @return float
     */
    public function getRemainingBalanceAttribute()
    {
        return max(0, $this->calculateTotalRepaymentAmount() - $this->total_paid);
    }

    /**
     * Calculate repayment progress percentage.
     *
     * @return float
     */
    public function getRepaymentProgressAttribute()
    {
        $totalAmount = $this->calculateTotalRepaymentAmount();
        
        if ($totalAmount <= 0) {
            return 100;
        }
        
        return min(100, round(($this->total_paid / $totalAmount) * 100, 2));
    }

    /**
     * Get the number of installments paid.
     *
     * @return int
     */
    public function getInstallmentsPaidAttribute()
    {
        return $this->repayments()->count();
    }

    /**
     * Get the number of remaining installments.
     *
     * @return int
     */
    public function getRemainingInstallmentsAttribute()
    {
        return max(0, $this->tenure_months - $this->installments_paid);
    }

    /**
     * Check if the loan is overdue.
     *
     * @return bool
     */
    public function isOverdue()
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        // Check if any installment is overdue
        $schedule = $this->calculateRepaymentSchedule();
        $paidInstallments = $this->installments_paid;
        
        // If there are more installments in the schedule than what's paid
        if (count($schedule) > $paidInstallments) {
            $nextInstallment = $schedule[$paidInstallments];
            return strtotime($nextInstallment['due_date']) < time();
        }
        
        return false;
    }

    /**
     * Check if the loan is fully paid.
     *
     * @return bool
     */
    public function isFullyPaid()
    {
        return $this->remaining_balance <= 0;
    }

    /**
     * Record a repayment.
     *
     * @param  array  $repaymentData
     * @return LoanRepayment
     */
    public function recordRepayment($repaymentData)
    {
        $repayment = $this->repayments()->create([
            'amount' => $repaymentData['amount'],
            'payment_date' => $repaymentData['payment_date'] ?? now(),
            'payment_method' => $repaymentData['payment_method'],
            'reference_number' => $repaymentData['reference_number'] ?? null,
            'remarks' => $repaymentData['remarks'] ?? null,
            'recorded_by' => $repaymentData['recorded_by'] ?? auth()->id(),
        ]);
        
        // Update loan status if fully paid
        if ($this->isFullyPaid()) {
            $this->status = 'completed';
            $this->save();
        }
        
        return $repayment;
    }

    /**
     * Approve the loan.
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
     * Reject the loan.
     *
     * @param  int  $rejectedBy  Staff ID
     * @param  string  $reason
     * @return bool
     */
    public function reject($rejectedBy, $reason)
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        $this->status = 'rejected';
        $this->approved_by = $rejectedBy;
        $this->approval_date = now();
        $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Rejection reason: {$reason}";
        
        return $this->save();
    }

    /**
     * Activate the loan.
     *
     * @param  \DateTime|string|null  $loanDate
     * @param  string|null  $remarks
     * @return bool
     */
    public function activate($loanDate = null, $remarks = null)
    {
        if ($this->status !== 'approved') {
            return false;
        }
        
        $this->status = 'active';
        $this->loan_date = $loanDate ?? now();
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Activation remarks: {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Mark the loan as defaulted.
     *
     * @param  string  $reason
     * @return bool
     */
    public function markAsDefaulted($reason)
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        $this->status = 'defaulted';
        $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Defaulted reason: {$reason}";
        
        return $this->save();
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
                return 'badge-info';
            case 'rejected':
                return 'badge-danger';
            case 'active':
                return $this->isOverdue() ? 'badge-warning' : 'badge-primary';
            case 'completed':
                return 'badge-success';
            case 'defaulted':
                return 'badge-danger';
            case 'cancelled':
                return 'badge-secondary';
            default:
                return 'badge-dark';
        }
    }
}