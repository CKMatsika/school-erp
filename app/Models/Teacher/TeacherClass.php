<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherClass extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teacher_class';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'is_class_teacher'
    ];

    protected $casts = [
        'is_class_teacher' => 'boolean',
    ];

    /**
     * Get the teacher associated with this record.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the class associated with this record.
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}