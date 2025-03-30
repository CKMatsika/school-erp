<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'teacher_id',
        'class_id',
        'subject_id',
        'platform',
        'meeting_link',
        'password',
        'start_time',
        'end_time',
        'actual_start_time',
        'actual_end_time',
        'description',
        'instructions',
        'status',
        'recording_url',
        'recurring',
        'recurrence_pattern',
        'recurrence_end_date',
        'parent_class_id',
        'send_reminder'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'recurrence_end_date' => 'date',
        'recurring' => 'boolean',
        'send_reminder' => 'boolean',
    ];

    /**
     * Get the teacher that created the online class.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the class associated with this online class.
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the subject associated with this online class.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the parent online class for recurring classes.
     */
    public function parentClass()
    {
        return $this->belongsTo(OnlineClass::class, 'parent_class_id');
    }

    /**
     * Get the child online classes for recurring classes.
     */
    public function childClasses()
    {
        return $this->hasMany(OnlineClass::class, 'parent_class_id');
    }

    /**
     * Get the attendees for this online class.
     */
    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    /**
     * Get the materials for this online class.
     */
    public function materials()
    {
        return $this->hasMany(OnlineClassMaterial::class);
    }
}