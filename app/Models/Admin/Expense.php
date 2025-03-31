<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'expense_category_id',
        'amount',
        'expense_date',
        'payment_method',
        'reference_number',
        'description',
        'payee',
        'department_id',
        'staff_id',
        'approved_by',
        'approval_date',
        'status', // 'pending', 'approved', 'rejected', 'paid'
        'receipt',
        'invoice_number',
        'tax_amount',
        'notes',
        'recurring', // boolean
        'recurring_frequency', // 'daily', 'weekly', 'monthly', 'quarterly', 'yearly'
        'recurring_end_date',
        'budget_year',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'expense_date' => 'date',
        'approval_date' => 'date',
        'recurring_end_date' => 'date',
        'recurring' => 'boolean',
    ];

    /**
     * Get the expense category for this expense.
     */
    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Get the department for this expense.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the staff member who created this expense.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the staff member who approved this expense.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Scope a query to only include expenses for a specific category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    /**
     * Scope a query to only include expenses for a specific department.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to only include expenses for a specific date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereDate('expense_date', '>=', $startDate)
                    ->whereDate('expense_date', '<=', $endDate);
    }

    /**
     * Scope a query to only include expenses with a specific status.
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
     * Scope a query to only include pending expenses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved expenses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include recurring expenses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecurring($query)
    {
        return $query->where('recurring', true);
    }

    /**
     * Get total amount including tax.
     *
     * @return float
     */
    public function getTotalAmountAttribute()
    {
        return $this->amount + ($this->tax_amount ?? 0);
    }

    /**
     * Approve this expense.
     *
     * @param  int  $approvedBy  Staff ID
     * @param  string|null  $notes
     * @return bool
     */
    public function approve($approvedBy, $notes = null)
    {
        $this->status = 'approved';
        $this->approved_by = $approvedBy;
        $this->approval_date = now();
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Approval notes: {$notes}";
        }
        
        return $this->save();
    }

    /**
     * Reject this expense.
     *
     * @param  int  $rejectedBy  Staff ID
     * @param  string  $reason
     * @return bool
     */
    public function reject($rejectedBy, $reason)
    {
        $this->status = 'rejected';
        $this->approved_by = $rejectedBy;
        $this->approval_date = now();
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Rejection reason: {$reason}";
        
        return $this->save();
    }

    /**
     * Mark this expense as paid.
     *
     * @param  string  $paymentMethod
     * @param  string  $referenceNumber
     * @param  string|null  $notes
     * @return bool
     */
    public function markAsPaid($paymentMethod, $referenceNumber, $notes = null)
    {
        if ($this->status !== 'approved') {
            return false;
        }
        
        $this->status = 'paid';
        $this->payment_method = $paymentMethod;
        $this->reference_number = $referenceNumber;
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Payment notes: {$notes}";
        }
        
        return $this->save();
    }

    /**
     * Generate next recurring expense based on this expense.
     *
     * @return Expense|null
     */
    public function generateNextRecurring()
    {
        if (!$this->recurring) {
            return null;
        }
        
        // Calculate next expense date based on frequency
        $nextDate = null;
        switch ($this->recurring_frequency) {
            case 'daily':
                $nextDate = $this->expense_date->addDay();
                break;
            case 'weekly':
                $nextDate = $this->expense_date->addWeek();
                break;
            case 'monthly':
                $nextDate = $this->expense_date->addMonth();
                break;
            case 'quarterly':
                $nextDate = $this->expense_date->addMonths(3);
                break;
            case 'yearly':
                $nextDate = $this->expense_date->addYear();
                break;
        }
        
        // Check if we've reached the end date for recurring
        if ($this->recurring_end_date && $nextDate > $this->recurring_end_date) {
            return null;
        }
        
        // Create the new expense record
        $newExpense = $this->replicate(['status', 'approved_by', 'approval_date', 'receipt']);
        $newExpense->expense_date = $nextDate;
        $newExpense->status = 'pending';
        $newExpense->save();
        
        return $newExpense;
    }

    /**
     * Get the receipt URL.
     *
     * @return string|null
     */
    public function getReceiptUrlAttribute()
    {
        return $this->receipt ? url('storage/' . $this->receipt) : null;
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
            case 'rejected':
                return 'badge-danger';
            case 'paid':
                return 'badge-primary';
            default:
                return 'badge-secondary';
        }
    }
}