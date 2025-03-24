<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    // School relationship
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Terms relationship
    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    // Enrollments relationship
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Applications relationship
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // Scope for current academic year
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}