<?php

namespace App\Models\Accounting; // Or App\Models if preferred

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Add if using soft deletes
use App\Models\School; // Assuming relationship
// use App\Models\Student; // Use correct Student model namespace if linking students

class SchoolClass extends Model
{
    use HasFactory, SoftDeletes; // Add SoftDeletes if your table has it

    // Explicitly define the table name
    protected $table = 'timetable_school_classes';

    protected $fillable = [
        'school_id',
        'name',
        'level',
        'capacity',
        'home_room',
        'notes',
        'is_active', // Corrected from 'active' based on previous step
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function students()
    {
         // Use correct Student model namespace and foreign key
         return $this->hasMany(\App\Models\Student::class, 'current_class_id');
    }

    // Add relationships to sections, teachers, etc. if needed
}