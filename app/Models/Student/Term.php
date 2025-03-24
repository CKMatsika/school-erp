<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Term extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
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

    // Academic Year relationship
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Get school through academic year
    public function school()
    {
        return $this->academicYear->school;
    }

    // Scope for current term
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}