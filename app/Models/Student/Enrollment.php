<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student; // Add this import for the Student model

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'class_id',
        'academic_year_id',
        'status',
        'enrollment_date',
        'notes',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
    ];

    // Student relationship
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Class relationship
    public function class()
    {
        return $this->belongsTo(\App\Models\TimetableSchoolClass::class, 'class_id');
    }

    // Academic Year relationship
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Scope for active enrollments
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}