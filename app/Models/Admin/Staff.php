<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'department_id',
        'designation_id',
        'joining_date',
        'employment_type', // 'full-time', 'part-time', 'contract', 'temporary'
        'basic_salary',
        'photo',
        'username',
        'password',
        'status', // 'active', 'on-leave', 'inactive', 'terminated'
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'is_teaching_staff',
        'employee_id',
        'bank_account_number',
        'bank_name',
        'tax_id',
        'qualifications',
        'experience_years',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'basic_salary' => 'decimal:2',
        'is_teaching_staff' => 'boolean',
        'experience_years' => 'integer',
        'last_login_at' => 'datetime',
        'qualifications' => 'array',
    ];

    /**
     * Get the full name of the staff member.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the department of the staff member.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the designation of the staff member.
     */
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Get the attendance records for the staff member.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(StaffAttendance::class);
    }

    /**
     * Get the leave applications of the staff member.
     */
    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    /**
     * Get the leave balances of the staff member.
     */
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get the performance evaluations of the staff member.
     */
    public function performanceEvaluations()
    {
        return $this->hasMany(Performance::class, 'staff_id');
    }

    /**
     * Get the salary records of the staff member.
     */
    public function salaryRecords()
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * Get the documents uploaded for the staff member.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Scope a query to only include active staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include teaching staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTeachingStaff($query)
    {
        return $query->where('is_teaching_staff', true);
    }

    /**
     * Scope a query to only include non-teaching staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonTeachingStaff($query)
    {
        return $query->where('is_teaching_staff', false);
    }

    /**
     * Scope a query to only include staff of a specific department.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to only include staff with a specific designation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $designationId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithDesignation($query, $designationId)
    {
        return $query->where('designation_id', $designationId);
    }

    /**
     * Scope a query to only include staff who joined after a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinedAfter($query, $date)
    {
        return $query->whereDate('joining_date', '>=', $date);
    }

    /**
     * Scope a query to only include staff who are currently on leave.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnLeave($query)
    {
        return $query->where('status', 'on-leave');
    }

    /**
     * Check if staff is on leave for a specific date.
     *
     * @param  string  $date
     * @return bool
     */
    public function isOnLeaveOn($date)
    {
        return $this->leaveApplications()
                    ->where('status', 'approved')
                    ->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->exists();
    }

    /**
     * Get attendance status for a specific date.
     *
     * @param  string  $date
     * @return string|null
     */
    public function getAttendanceStatusOn($date)
    {
        $attendance = $this->attendanceRecords()
                          ->whereDate('date', $date)
                          ->first();
                          
        return $attendance ? $attendance->status : null;
    }

    /**
     * Mark attendance for a specific date.
     *
     * @param  string  $date
     * @param  string  $status
     * @param  string|null  $remarks
     * @return StaffAttendance
     */
    public function markAttendance($date, $status, $remarks = null)
    {
        return $this->attendanceRecords()->updateOrCreate(
            ['date' => $date],
            [
                'status' => $status,
                'remarks' => $remarks,
            ]
        );
    }

    /**
     * Apply for leave.
     *
     * @param  array  $leaveData
     * @return LeaveApplication
     */
    public function applyForLeave($leaveData)
    {
        return $this->leaveApplications()->create([
            'leave_type_id' => $leaveData['leave_type_id'],
            'start_date' => $leaveData['start_date'],
            'end_date' => $leaveData['end_date'],
            'reason' => $leaveData['reason'],
            'address_during_leave' => $leaveData['address_during_leave'] ?? null,
            'contact_during_leave' => $leaveData['contact_during_leave'] ?? null,
            'attachment' => $leaveData['attachment'] ?? null,
            'status' => 'pending',
            'half_day' => $leaveData['half_day'] ?? false,
            'substitute_staff_id' => $leaveData['substitute_staff_id'] ?? null,
            'handover_notes' => $leaveData['handover_notes'] ?? null,
        ]);
    }

    /**
     * Upload a document for this staff member.
     *
     * @param  array  $documentData
     * @return Document
     */
    public function uploadDocument($documentData)
    {
        return $this->documents()->create([
            'document_type' => $documentData['document_type'],
            'file_path' => $documentData['file_path'],
            'file_name' => $documentData['file_name'],
            'expiry_date' => $documentData['expiry_date'] ?? null,
            'remarks' => $documentData['remarks'] ?? null,
        ]);
    }

    /**
     * Get age in years.
     *
     * @return int|null
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    /**
     * Get years of service.
     *
     * @return float
     */
    public function getYearsOfServiceAttribute()
    {
        return $this->joining_date ? $this->joining_date->diffInDays(now()) / 365 : 0;
    }

    /**
     * Check if staff has leave balance for a specific leave type.
     *
     * @param  int  $leaveTypeId
     * @param  float  $days
     * @return bool
     */
    public function hasLeaveBalance($leaveTypeId, $days)
    {
        $balance = $this->leaveBalances()
                        ->where('leave_type_id', $leaveTypeId)
                        ->where('year', date('Y'))
                        ->first();
                        
        return $balance && $balance->available_days >= $days;
    }

    /**
     * Get leave balance for a specific leave type.
     *
     * @param  int  $leaveTypeId
     * @return LeaveBalance|null
     */
    public function getLeaveBalance($leaveTypeId)
    {
        return $this->leaveBalances()
                    ->where('leave_type_id', $leaveTypeId)
                    ->where('year', date('Y'))
                    ->first();
    }
}