<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkingScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'teacher_id',
        'class_id',
        'subject_id',
        'assessment_type',
        'total_marks',
        'passing_marks',
        'description',
        'notes',
        'file_path',
        'status'
    ];

    protected $casts = [
        'total_marks' => 'integer',
        'passing_marks' => 'integer',
    ];

    /**
     * Get the teacher that created the marking scheme.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the class associated with this marking scheme.
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the subject associated with this marking scheme.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the criteria for this marking scheme.
     */
    public function criteria()
    {
        return $this->hasMany(Criterion::class);
    }

    /**
     * Get the assignments using this marking scheme.
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}