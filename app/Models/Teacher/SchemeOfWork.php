<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchemeOfWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'teacher_id',
        'class_id',
        'subject_id',
        'term',
        'academic_year',
        'description',
        'objectives',
        'start_date',
        'end_date',
        'file_path'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'term' => 'integer',
    ];

    /**
     * Get the teacher that created the scheme of work.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the class associated with this scheme of work.
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the subject associated with this scheme of work.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the weeks for this scheme of work.
     */
    public function weeks()
    {
        return $this->hasMany(SchemeWeek::class);
    }
}