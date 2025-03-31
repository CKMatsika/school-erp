<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staff_id',
        'date',
        'status', // 'present', 'absent', 'late', 'leave', 'half-day'
        'check_in_time',
        'check_out_time',
        'working_hours',
        'marked_by',
        'remarks',
        'is_holiday',
        'is_weekend',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'working_hours' => 'decimal:2',
        'is_holiday' => 'boolean',
        'is_weekend' => 'boolean',
    ];

    /**
     * Get the staff member for this attendance record.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the staff member who marked this attendance.
     */
    public function markedBy()
    {
        return $this->belongsTo(Staff::class, 'marked_by');
    }

    /**
     * Scope a query to only include attendance records for a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope a query to only include attendance records for a date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate);
    }

    /**
     * Scope a query to only include present staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope a query to only include absent staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope a query to only include late staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope a query to only include staff on leave.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnLeave($query)
    {
        return $query->where('status', 'leave');
    }

    /**
     * Scope a query to only include attendance for a specific department.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->whereHas('staff', function ($query) use ($departmentId) {
            $query->where('department_id', $departmentId);
        });
    }

    /**
     * Calculate working hours if check-in and check-out times are provided.
     *
     * @return void
     */
    public function calculateWorkingHours()
    {
        if ($this->check_in_time && $this->check_out_time) {
            $checkIn = new \DateTime($this->check_in_time);
            $checkOut = new \DateTime($this->check_out_time);
            
            // Calculate difference in hours
            $interval = $checkIn->diff($checkOut);
            $hours = $interval->h + ($interval->i / 60);
            
            $this->working_hours = $hours;
            $this->save();
        }
    }

    /**
     * Update attendance status based on check-in time.
     *
     * @param  string  $regularCheckInTime  Format: 'H:i'
     * @param  int  $lateThresholdMinutes
     * @return void
     */
    public function updateStatusBasedOnCheckIn($regularCheckInTime, $lateThresholdMinutes = 15)
    {
        if (!$this->check_in_time) {
            return;
        }
        
        // Parse times
        $regularTime = \DateTime::createFromFormat('H:i', $regularCheckInTime);
        $actualTime = new \DateTime($this->check_in_time);
        
        // Calculate difference in minutes
        $diff = ($actualTime->getTimestamp() - $regularTime->getTimestamp()) / 60;
        
        // Update status if needed
        if ($diff > $lateThresholdMinutes && $this->status === 'present') {
            $this->status = 'late';
            $this->save();
        }
    }

    /**
     * Mark check-in time.
     *
     * @param  string  $time  Format: 'H:i:s'
     * @param  string|null  $remarks
     * @return bool
     */
    public function markCheckIn($time, $remarks = null)
    {
        $this->check_in_time = $time;
        
        if ($remarks) {
            $this->remarks = $remarks;
        }
        
        return $this->save();
    }

    /**
     * Mark check-out time.
     *
     * @param  string  $time  Format: 'H:i:s'
     * @param  string|null  $remarks
     * @return bool
     */
    public function markCheckOut($time, $remarks = null)
    {
        $this->check_out_time = $time;
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . $remarks;
        }
        
        $this->calculateWorkingHours();
        
        return $this->save();
    }

    /**
     * Get status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'present':
                return 'badge-success';
            case 'absent':
                return 'badge-danger';
            case 'late':
                return 'badge-warning';
            case 'leave':
                return 'badge-info';
            case 'half-day':
                return 'badge-primary';
            default:
                return 'badge-secondary';
        }
    }
}