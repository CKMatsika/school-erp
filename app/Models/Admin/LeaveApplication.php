<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'reason',
        'address_during_leave',
        'contact_during_leave',
        'attachment',
        'status', // 'pending', 'approved', 'rejected', 'cancelled'
        'approved_by',
        'approved_date',
        'remarks',
        'half_day',
        'substitute_staff_id',
        'handover_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_date' => 'date',
        'half_day' => 'boolean',
    ];

    /**
     * Get the staff member who applied for leave.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the leave type for this application.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the staff member who approved the leave.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Get the substitute staff for this leave.
     */
    public function substituteStaff()
    {
        return $this->belongsTo(Staff::class, 'substitute_staff_id');
    }

    /**
     * Scope a query to only include pending applications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved applications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected applications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include applications for a specific staff.
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
     * Scope a query to only include applications for a specific leave type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $leaveTypeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $leaveTypeId)
    {
        return $query->where('leave_type_id', $leaveTypeId);
    }

    /**
     * Scope a query to only include current or upcoming leaves.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrentOrUpcoming($query)
    {
        return $query->where('status', 'approved')
                    ->where('end_date', '>=', now());
    }

    /**
     * Scope a query to only include leaves for a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnDate($query, $date)
    {
        return $query->where('status', 'approved')
                    ->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date);
    }

    /**
     * Calculate the number of days for this leave application.
     *
     * @return float
     */
    public function getDaysCountAttribute()
    {
        if ($this->half_day) {
            return 0.5;
        }
        
        return $this->start_date->diffInDaysFiltered(function (\Carbon\Carbon $date) {
            return !$date->isWeekend(); // Exclude weekends
        }, $this->end_date) + 1; // +1 to include both start and end dates
    }

    /**
     * Approve the leave application.
     *
     * @param  int  $approvedBy
     * @param  string|null  $remarks
     * @return bool
     */
    public function approve($approvedBy, $remarks = null)
    {
        $this->status = 'approved';
        $this->approved_by = $approvedBy;
        $this->approved_date = now();
        
        if ($remarks) {
            $this->remarks = $remarks;
        }
        
        $saved = $this->save();
        
        if ($saved) {
            // Update leave balance
            $this->updateLeaveBalance();
        }
        
        return $saved;
    }

    /**
     * Reject the leave application.
     *
     * @param  int  $rejectedBy
     * @param  string|null  $remarks
     * @return bool
     */
    public function reject($rejectedBy, $remarks = null)
    {
        $this->status = 'rejected';
        $this->approved_by = $rejectedBy; // Using the same field for rejected_by
        $this->approved_date = now();
        
        if ($remarks) {
            $this->remarks = $remarks;
        }
        
        return $this->save();
    }

    /**
     * Cancel the leave application.
     *
     * @param  string|null  $remarks
     * @return bool
     */
    public function cancel($remarks = null)
    {
        // Can only cancel pending or approved leaves
        if (!in_array($this->status, ['pending', 'approved'])) {
            return false;
        }
        
        $oldStatus = $this->status;
        $this->status = 'cancelled';
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . 
                            "Cancelled on " . now() . ". Reason: {$remarks}";
        }
        
        $saved = $this->save();
        
        // If cancelling an approved leave, restore leave balance
        if ($saved && $oldStatus === 'approved') {
            $this->restoreLeaveBalance();
        }
        
        return $saved;
    }

    /**
     * Update leave balance after approval.
     *
     * @return bool
     */
    protected function updateLeaveBalance()
    {
        $leaveBalance = LeaveBalance::firstOrCreate(
            [
                'staff_id' => $this->staff_id,
                'leave_type_id' => $this->leave_type_id,
                'year' => date('Y')
            ]
        );
        
        $leaveBalance->used_days += $this->days_count;
        $leaveBalance->available_days -= $this->days_count;
        
        return $leaveBalance->save();
    }

    /**
     * Restore leave balance after cancellation.
     *
     * @return bool
     */
    protected function restoreLeaveBalance()
    {
        $leaveBalance = LeaveBalance::where('staff_id', $this->staff_id)
                                    ->where('leave_type_id', $this->leave_type_id)
                                    ->where('year', date('Y'))
                                    ->first();
        
        if ($leaveBalance) {
            $leaveBalance->used_days -= $this->days_count;
            $leaveBalance->available_days += $this->days_count;
            
            return $leaveBalance->save();
        }
        
        return false;
    }

    /**
     * Check if application is editable.
     *
     * @return bool
     */
    public function isEditable()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if application is cancellable.
     *
     * @return bool
     */
    public function isCancellable()
    {
        return in_array($this->status, ['pending', 'approved']) && 
               $this->start_date > now();
    }

    /**
     * Get status badge class.
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
            case 'cancelled':
                return 'badge-secondary';
            default:
                return 'badge-info';
        }
    }
}