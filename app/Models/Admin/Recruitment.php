<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recruitment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_title',
        'position_id',
        'department_id',
        'vacancies',
        'description',
        'requirements',
        'responsibilities',
        'salary_range_min',
        'salary_range_max',
        'employment_type', // 'full-time', 'part-time', 'contract', 'temporary'
        'experience_required',
        'education_required',
        'location',
        'application_deadline',
        'posting_date',
        'status', // 'draft', 'published', 'closed', 'cancelled'
        'created_by',
        'approved_by',
        'approval_date',
        'external_posting_urls',
        'internal_posting',
        'skills_required',
        'benefits',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'vacancies' => 'integer',
        'salary_range_min' => 'decimal:2',
        'salary_range_max' => 'decimal:2',
        'application_deadline' => 'date',
        'posting_date' => 'date',
        'approval_date' => 'date',
        'external_posting_urls' => 'array',
        'internal_posting' => 'boolean',
        'skills_required' => 'array',
        'benefits' => 'array',
    ];

    /**
     * Get the position for this recruitment.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the department for this recruitment.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the staff member who created this recruitment.
     */
    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the staff member who approved this recruitment.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Get the applications for this recruitment.
     */
    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get the interviews related to this recruitment.
     */
    public function interviews()
    {
        return $this->hasManyThrough(Interview::class, JobApplication::class);
    }

    /**
     * Scope a query to only include active recruitments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'published')
                    ->where('application_deadline', '>=', now());
    }

    /**
     * Scope a query to only include expired recruitments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'published')
                    ->where('application_deadline', '<', now());
    }

    /**
     * Scope a query to only include internal postings.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInternal($query)
    {
        return $query->where('internal_posting', true);
    }

    /**
     * Scope a query to only include recruitments for a specific department.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to only include recruitments with a specific employment type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    /**
     * Check if the recruitment is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'published' && $this->application_deadline >= now();
    }

    /**
     * Check if the recruitment is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->application_deadline < now();
    }

    /**
     * Get the number of applications received.
     *
     * @return int
     */
    public function getApplicationsCountAttribute()
    {
        return $this->applications()->count();
    }

    /**
     * Get the number of shortlisted applications.
     *
     * @return int
     */
    public function getShortlistedCountAttribute()
    {
        return $this->applications()->where('status', 'shortlisted')->count();
    }

    /**
     * Get the number of interviewed applications.
     *
     * @return int
     */
    public function getInterviewedCountAttribute()
    {
        return $this->applications()->where('status', 'interview')->count();
    }

    /**
     * Get the number of hired applications.
     *
     * @return int
     */
    public function getHiredCountAttribute()
    {
        return $this->applications()->where('status', 'hired')->count();
    }

    /**
     * Get days until application deadline.
     *
     * @return int|null
     */
    public function getDaysUntilDeadlineAttribute()
    {
        if (!$this->application_deadline) {
            return null;
        }
        
        if ($this->application_deadline < now()) {
            return -1 * now()->diffInDays($this->application_deadline); // Negative days if passed
        }
        
        return now()->diffInDays($this->application_deadline);
    }

    /**
     * Publish this recruitment.
     *
     * @param  int|null  $approvedBy
     * @return bool
     */
    public function publish($approvedBy = null)
    {
        if ($this->status !== 'draft') {
            return false;
        }
        
        $this->status = 'published';
        $this->posting_date = now();
        
        if ($approvedBy) {
            $this->approved_by = $approvedBy;
            $this->approval_date = now();
        }
        
        return $this->save();
    }

    /**
     * Close this recruitment.
     *
     * @param  string|null  $reason
     * @return bool
     */
    public function close($reason = null)
    {
        if ($this->status !== 'published') {
            return false;
        }
        
        $this->status = 'closed';
        
        if ($reason) {
            $this->description = ($this->description ? $this->description . "\n" : '') . "Closing reason: {$reason}";
        }
        
        return $this->save();
    }

    /**
     * Cancel this recruitment.
     *
     * @param  string  $reason
     * @return bool
     */
    public function cancel($reason)
    {
        if (!in_array($this->status, ['draft', 'published'])) {
            return false;
        }
        
        $this->status = 'cancelled';
        $this->description = ($this->description ? $this->description . "\n" : '') . "Cancellation reason: {$reason}";
        
        return $this->save();
    }

    /**
     * Get the formatted salary range.
     *
     * @return string
     */
    public function getFormattedSalaryRangeAttribute()
    {
        if (!$this->salary_range_min && !$this->salary_range_max) {
            return 'Negotiable';
        }
        
        if ($this->salary_range_min && !$this->salary_range_max) {
            return 'From ' . number_format($this->salary_range_min, 2);
        }
        
        if (!$this->salary_range_min && $this->salary_range_max) {
            return 'Up to ' . number_format($this->salary_range_max, 2);
        }
        
        return number_format($this->salary_range_min, 2) . ' - ' . number_format($this->salary_range_max, 2);
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'draft':
                return 'badge-secondary';
            case 'published':
                return $this->isExpired() ? 'badge-warning' : 'badge-success';
            case 'closed':
                return 'badge-primary';
            case 'cancelled':
                return 'badge-danger';
            default:
                return 'badge-dark';
        }
    }
}