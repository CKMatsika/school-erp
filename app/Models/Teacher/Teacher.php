<?php

namespace App\Models\Teacher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'title',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'department',
        'join_date',
        'qualifications',
        'experience',
        'specialization',
        'linkedin',
        'twitter',
        'website',
        'bio',
        'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'experience' => 'float',
    ];

    /**
     * Get the user that owns the teacher profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the classes taught by the teacher.
     */
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_class', 'teacher_id', 'class_id')
                    ->withPivot('is_class_teacher')
                    ->withTimestamps();
    }

    /**
     * Get the subjects taught by the teacher.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id')
                    ->withTimestamps();
    }

    /**
     * Get the lesson plans created by the teacher.
     */
    public function lessonPlans()
    {
        return $this->hasMany(LessonPlan::class);
    }

    /**
     * Get the assignments created by the teacher.
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get the online classes created by the teacher.
     */
    public function onlineClasses()
    {
        return $this->hasMany(OnlineClass::class);
    }

    /**
     * Get the marking schemes created by the teacher.
     */
    public function markingSchemes()
    {
        return $this->hasMany(MarkingScheme::class);
    }

    /**
     * Get the schemes of work created by the teacher.
     */
    public function schemesOfWork()
    {
        return $this->hasMany(SchemeOfWork::class);
    }
}