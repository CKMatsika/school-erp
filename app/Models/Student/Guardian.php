<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'first_name',
        'last_name',
        'relationship',
        'email',
        'phone_primary',
        'phone_secondary',
        'address',
        'occupation',
        'is_primary_contact',
        'receives_communication',
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
        'receives_communication' => 'boolean',
    ];

    // Get the guardian's full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // School relationship
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Students relationship
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
                    ->withPivot('is_emergency_contact')
                    ->withTimestamps();
    }
}