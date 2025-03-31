<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'applicable_to', // 'all', 'teaching', 'non-teaching', 'specific'
        'effective_from',
        'effective_to',
        'is_active',
        'created_by',
        'approved_by',
        'approval_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'approval_date' => 'date',
    ];

    /**
     * The departments that belong to the policy.
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'leave_policy_departments');
    }

    /**
     * Get the staff member who created the policy.
     */
    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the staff member who approved the policy.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Get the leave types associated with this policy.
     */
    public function leaveTypes()
    {
        return $this->hasMany(LeaveType::class);
    }

    /**
     * Scope a query to only include active policies.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('effective_from', '<=', now())
                    ->where(function ($query) {
                        $query->whereNull('effective_to')
                              ->orWhere('effective_to', '>=', now());
                    });
    }

    /**
     * Scope a query to only include policies for a specific staff category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCategory($query, $category)
    {
        return $query->where(function ($query) use ($category) {
            $query->where('applicable_to', 'all')
                  ->orWhere('applicable_to', $category);
        });
    }

    /**
     * Scope a query to only include policies for a specific department.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where(function ($query) use ($departmentId) {
            $query->where('applicable_to', '!=', 'specific')
                  ->orWhereHas('departments', function ($query) use ($departmentId) {
                      $query->where('department_id', $departmentId);
                  });
        });
    }

    /**
     * Check if policy is applicable to a staff member.
     *
     * @param  Staff  $staff
     * @return bool
     */
    public function isApplicableTo($staff)
    {
        // Check if policy is active
        if (!$this->is_active || 
            $this->effective_from > now() || 
            ($this->effective_to && $this->effective_to < now())) {
            return false;
        }
        
        // Check applicability based on category
        if ($this->applicable_to === 'all') {
            return true;
        }
        
        if ($this->applicable_to === 'teaching' && $staff->is_teaching_staff) {
            return true;
        }
        
        if ($this->applicable_to === 'non-teaching' && !$staff->is_teaching_staff) {
            return true;
        }
        
        // Check if staff's department is in the policy's departments
        if ($this->applicable_to === 'specific' && $staff->department_id) {
            return $this->departments()->where('department_id', $staff->department_id)->exists();
        }
        
        return false;
    }

    /**
     * Activate the policy.
     *
     * @param  int|null  $approvedBy
     * @return bool
     */
    public function activate($approvedBy = null)
    {
        $this->is_active = true;
        
        if ($approvedBy) {
            $this->approved_by = $approvedBy;
            $this->approval_date = now();
        }
        
        return $this->save();
    }

    /**
     * Deactivate the policy.
     *
     * @return bool
     */
    public function deactivate()
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Get policy status text.
     *
     * @return string
     */
    public function getStatusTextAttribute()
    {
        if (!$this->is_active) {
            return 'Inactive';
        }
        
        if ($this->effective_from > now()) {
            return 'Pending (Future)';
        }
        
        if ($this->effective_to && $this->effective_to < now()) {
            return 'Expired';
        }
        
        return 'Active';
    }

    /**
     * Get policy status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        $status = $this->status_text;
        
        switch ($status) {
            case 'Active':
                return 'badge-success';
            case 'Inactive':
                return 'badge-secondary';
            case 'Pending (Future)':
                return 'badge-info';
            case 'Expired':
                return 'badge-warning';
            default:
                return 'badge-primary';
        }
    }

    /**
     * Get all staff members to whom this policy applies.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApplicableStaffAttribute()
    {
        $query = Staff::query();
        
        if ($this->applicable_to === 'teaching') {
            $query->where('is_teaching_staff', true);
        } elseif ($this->applicable_to === 'non-teaching') {
            $query->where('is_teaching_staff', false);
        } elseif ($this->applicable_to === 'specific') {
            $departmentIds = $this->departments->pluck('id')->toArray();
            $query->whereIn('department_id', $departmentIds);
        }
        
        return $query->get();
    }
}