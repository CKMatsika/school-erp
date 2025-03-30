<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'teacher_id',
        'class_id',
        'subject_id',
        'description',
        'instructions',
        'total_marks',
        'due_date',
        'file_path',
        'marking_scheme_id',
        'status',
        'allow_late_submissions',
        'late_submission_deadline',
        'late_submission_deduction'
    ];

    protected $casts = [
        'due_date' => 'date',
        'late_submission_deadline' => 'date',
        'total_marks' => 'integer',
        'late_submission_deduction' => 'integer',
        'allow_late_submissions' => 'boolean',
    ];

    /**
     * Get the teacher that created the assignment.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the class associated with this assignment.
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the subject associated with this assignment.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the marking scheme associated with this assignment.
     */
    public function markingScheme()
    {
        return $this->belongsTo(MarkingScheme::class);
    }

    /**
     * Get the submissions for this assignment.
     */
    public function submissions()
    {
        return $this->hasMany(StudentSubmission::class);
    }
}