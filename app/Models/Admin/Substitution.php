<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Substitution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'teacher_id',
        'substitute_teacher_id',
        'class_id',
        'section_id',
        'subject_id',
        'date',
        'period_ids', // JSON array of period IDs
        'reason',
        'status', // 'pending', 'approved', 'rejected', 'completed'
        'remarks',
        'created_by',
        'approved_by',
        'approval_date',
        'leave_application_id',
        'is_paid',
        'payment_amount',
        'payment_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'period_ids' => 'array',
        'approval_date' => 'date',
        'is_paid' => 'boolean',
        'payment_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the teacher who is being substituted.
     */
    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }

    /**
     * Get the substitute teacher.
     */
    public function substituteTeacher()
    {
        return $this->belongsTo(Staff::class, 'substitute_teacher_id');
    }

    /**
     * Get the class for this substitution.
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the section for this substitution.
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the subject for this substitution.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the staff member who created this substitution.
     */
    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the staff member who approved this substitution.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Get the leave application related to this substitution.
     */
    public function leaveApplication()
    {
        return $this->belongsTo(LeaveApplication::class);
    }

    /**
     * Get the periods for this substitution.
     */
    public function periods()
    {
        // Assuming there's a Period model
        return Period::whereIn('id', $this->period_ids ?? [])->get();
    }

    /**
     * Scope a query to only include substitutions for a specific date.
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
     * Scope a query to only include substitutions with a specific status.
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
     * Scope a query to only include pending substitutions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved substitutions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include completed substitutions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include substitutions for a specific teacher.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $teacherId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope a query to only include substitutions by a specific substitute teacher.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $substituteTeacherId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySubstituteTeacher($query, $substituteTeacherId)
    {
        return $query->where('substitute_teacher_id', $substituteTeacherId);
    }

    /**
     * Approve the substitution.
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
     * Reject the substitution.
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
     * Mark the substitution as completed.
     *
     * @param  string|null  $remarks
     * @return bool
     */
    public function markAsCompleted($remarks = null)
    {
        if ($this->status !== 'approved') {
            return false;
        }
        
        $this->status = 'completed';
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Completion remarks: {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Mark payment for the substitution.
     *
     * @param  float  $amount
     * @param  \DateTime|string|null  $paymentDate
     * @param  string|null  $remarks
     * @return bool
     */
    public function markAsPaid($amount, $paymentDate = null, $remarks = null)
    {
        if ($this->status !== 'completed') {
            return false;
        }
        
        $this->is_paid = true;
        $this->payment_amount = $amount;
        $this->payment_date = $paymentDate ?? now();
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Payment remarks: {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Check if the substitution is for today.
     *
     * @return bool
     */
    public function isForToday()
    {
        return $this->date->isToday();
    }

    /**
     * Check if the substitution is upcoming.
     *
     * @return bool
     */
    public function isUpcoming()
    {
        return $this->date > now();
    }

    /**
     * Check if the substitution is past.
     *
     * @return bool
     */
    public function isPast()
    {
        return $this->date < now();
    }

    /**
     * Get the number of periods for this substitution.
     *
     * @return int
     */
    public function getPeriodsCountAttribute()
    {
        return count($this->period_ids ?? []);
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
            case 'completed':
                return $this->is_paid ? 'badge-primary' : 'badge-info';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get formatted periods list.
     *
     * @return string
     */
    public function getFormattedPeriodsAttribute()
    {
        $periods = $this->periods();
        if ($periods->isEmpty()) {
            return 'No periods';
        }
        
        return $periods->pluck('name')->implode(', ');
    }
}