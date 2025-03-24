<?php

namespace App\Models;

use App\Models\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableSchoolClass extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'timetable_school_classes';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'level',
        'capacity',
        'teacher_id',
        'room',
        'is_active',
        'academic_year_id',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the school this class belongs to.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the teacher assigned to this class.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the academic year for this class.
     */
    public function academicYear()
    {
        return $this->belongsTo(\App\Models\Student\AcademicYear::class);
    }

    /**
     * Get the students enrolled in this class.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Get the enrollments for this class.
     */
    public function enrollments()
    {
        return $this->hasMany(\App\Models\Student\Enrollment::class, 'class_id');
    }

    /**
     * Get the timetable entries for this class.
     */
    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class, 'class_id');
    }

    /**
     * Get the active student count.
     */
    public function getActiveStudentCountAttribute()
    {
        return $this->students()->where('status', 'active')->count();
    }

    /**
     * Get the formatted name with code.
     */
    public function getFormattedNameAttribute()
    {
        return $this->name . ' (' . $this->code . ')';
    }

    /**
     * Scope a query to only include active classes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}