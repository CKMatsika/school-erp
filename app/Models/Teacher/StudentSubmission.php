<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_text',
        'file_path',
        'submission_date',
        'marks',
        'feedback',
        'graded_at',
        'graded_by',
        'status'
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'graded_at' => 'datetime',
        'marks' => 'integer',
    ];

    /**
     * Get the assignment that owns this submission.
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student that made this submission.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user (teacher) who graded this submission.
     */
    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}