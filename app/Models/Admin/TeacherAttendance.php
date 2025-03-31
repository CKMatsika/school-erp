<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'teacher_id',
        'date',
        'status', // 'present', 'absent', 'late', 'leave', 'half-day'
        'check_in_time',
        'check_out_time',
        'working_hours',
        'marked_by',
        'remarks',
        'is_holiday',
        'is_weekend',
        'leave_application_id',
        'substituted',
        'substitute_teacher_id',
        'periods_missed', // JSON array of period IDs
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
        'substituted' => 'boolean',
        'periods_missed' => 'array',
    ];

    /**
     * Get the teacher for this attendance record.
     */
    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }

    /**
     * Get the staff member who marked this attendance.
     */
    public function markedBy()
    {
        return $this->belongsTo(Staff::class, 'marked_by');
    }

    /**
     * Get the leave application related to this attendance.
     */
    public function leaveApplication()
    {
        return $this->belongsTo(LeaveApplication::class);
    }

    /**
     * Get the substitute teacher.
     */
    public function substituteTeacher()
    {
        return $this->belongsTo(Staff::class, 'substitute_teacher_id');
    }

    /**
     * Get the periods missed.
     */
    public function periodsMissed()
    {
        // Assuming there's a Period model
        return Period::whereIn('id', $this->periods_missed ?? [])->get();
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
     * Scope a query to only include present teachers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope a query to only include absent teachers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope a query to only include late teachers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope a query to only include teachers on leave.
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
        return $query->whereHas('teacher', function ($query) use ($departmentId) {
            $query->where('department_id', $departmentId);
        });
    }

    /**
     * Scope a query to only include attendance with substitution.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSubstitution($query)
    {
        return $query->where('substituted', true);
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
     * Mark teacher as absent with substitution.
     *
     * @param  int  $substituteTeacherId
     * @param  array  $periodsMissed
     * @param  string|null  $remarks
     * @return bool
     */
    public function markAbsentWithSubstitution($substituteTeacherId, $periodsMissed = [], $remarks = null)
    {
        $this->status = 'absent';
        $this->substituted = true;
        $this->substitute_teacher_id = $substituteTeacherId;
        $this->periods_missed = $periodsMissed;
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . $remarks;
        }
        
        return $this->save();
    }

    /**
     * Mark teacher as on leave based on leave application.
     *
     * @param  int  $leaveApplicationId
     * @param  int|null  $substituteTeacherId
     * @param  string|null  $remarks
     * @return bool
     */
    public function markOnLeave($leaveApplicationId, $substituteTeacherId = null, $remarks = null)
    {
        $this->status = 'leave';
        $this->leave_application_id = $leaveApplicationId;
        
        if ($substituteTeacherId) {
            $this->substituted = true;
            $this->substitute_teacher_id = $substituteTeacherId;
        }
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . $remarks;
        }
        
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

    /**
     * Get the formatted check-in and check-out times.
     *
     * @return string
     */
    public function getFormattedTimesAttribute()
    {
        if (!$this->check_in_time && !$this->check_out_time) {
            return 'N/A';
        }
        
        $checkIn = $this->check_in_time ? date('h:i A', strtotime($this->check_in_time)) : 'N/A';
        $checkOut = $this->check_out_time ? date('h:i A', strtotime($this->check_out_time)) : 'N/A';
        
        return "{$checkIn} - {$checkOut}";
    }

    /**
     * Get the formatted periods missed.
     *
     * @return string
     */
    public function getFormattedPeriodsMissedAttribute()
    {
        $periods = $this->periodsMissed();
        if ($periods->isEmpty()) {
            return 'None';
        }
        
        return $periods->pluck('name')->implode(', ');
    }
}