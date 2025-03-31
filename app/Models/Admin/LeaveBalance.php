<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
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
        'year',
        'allocated_days',
        'used_days',
        'available_days',
        'carried_forward_days',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'allocated_days' => 'decimal:1',
        'used_days' => 'decimal:1',
        'available_days' => 'decimal:1',
        'carried_forward_days' => 'decimal:1',
    ];

    /**
     * Get the staff member for this leave balance.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the leave type for this balance.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Scope a query to only include balances for a specific staff.
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
     * Scope a query to only include balances for a specific leave type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $leaveTypeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLeaveType($query, $leaveTypeId)
    {
        return $query->where('leave_type_id', $leaveTypeId);
    }

    /**
     * Scope a query to only include balances for a specific year.
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
     * Scope a query to only include balances with available days.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAvailableDays($query)
    {
        return $query->where('available_days', '>', 0);
    }

    /**
     * Check if staff has sufficient balance for requested days.
     *
     * @param  float  $requestedDays
     * @return bool
     */
    public function hasSufficientBalance($requestedDays)
    {
        return $this->available_days >= $requestedDays;
    }

    /**
     * Allocate additional leave days.
     *
     * @param  float  $days
     * @param  string|null  $notes
     * @return bool
     */
    public function allocateAdditionalDays($days, $notes = null)
    {
        $this->allocated_days += $days;
        $this->available_days += $days;
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          "Additional {$days} days allocated on " . now() . ". {$notes}";
        }
        
        return $this->save();
    }

    /**
     * Adjust used days.
     *
     * @param  float  $days
     * @param  string  $operation 'add' or 'subtract'
     * @param  string|null  $notes
     * @return bool
     */
    public function adjustUsedDays($days, $operation, $notes = null)
    {
        if ($operation === 'add') {
            $this->used_days += $days;
            $this->available_days -= $days;
        } else if ($operation === 'subtract') {
            $this->used_days -= $days;
            $this->available_days += $days;
        }
        
        // Ensure values don't go below zero
        $this->used_days = max(0, $this->used_days);
        $this->available_days = max(0, $this->available_days);
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          "Adjusted used days ({$operation} {$days}) on " . now() . ". {$notes}";
        }
        
        return $this->save();
    }

    /**
     * Reset balance for new year with carryforward.
     *
     * @param  int  $newYear
     * @param  float|null  $maxCarryForwardDays
     * @return LeaveBalance|null
     */
    public function resetForNewYear($newYear, $maxCarryForwardDays = null)
    {
        // Calculate carried forward days based on available days and max allowed
        $carryForwardDays = $this->available_days;
        
        if ($maxCarryForwardDays !== null) {
            $carryForwardDays = min($carryForwardDays, $maxCarryForwardDays);
        }
        
        // Get default allocation for this leave type
        $defaultAllocation = $this->leaveType ? $this->leaveType->days_allowed : 0;
        
        // Create new balance for the new year
        return self::create([
            'staff_id' => $this->staff_id,
            'leave_type_id' => $this->leave_type_id,
            'year' => $newYear,
            'allocated_days' => $defaultAllocation,
            'used_days' => 0,
            'available_days' => $defaultAllocation + $carryForwardDays,
            'carried_forward_days' => $carryForwardDays,
            'notes' => "Initial allocation with {$carryForwardDays} days carried forward from " . $this->year,
        ]);
    }

    /**
     * Get usage percentage.
     *
     * @return float
     */
    public function getUsagePercentageAttribute()
    {
        if ($this->allocated_days + $this->carried_forward_days <= 0) {
            return 0;
        }
        
        return ($this->used_days / ($this->allocated_days + $this->carried_forward_days)) * 100;
    }

    /**
     * Get pending applications for this balance.
     */
    public function pendingApplications()
    {
        return LeaveApplication::where('staff_id', $this->staff_id)
                              ->where('leave_type_id', $this->leave_type_id)
                              ->where('status', 'pending')
                              ->get();
    }

    /**
     * Calculate pending days in applications.
     *
     * @return float
     */
    public function getPendingDaysAttribute()
    {
        return $this->pendingApplications()->sum('days_count');
    }

    /**
     * Get effective available days (available minus pending).
     *
     * @return float
     */
    public function getEffectiveAvailableDaysAttribute()
    {
        return max(0, $this->available_days - $this->pending_days);
    }
}