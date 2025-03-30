<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teacher_subject';

    protected $fillable = [
        'teacher_id',
        'subject_id'
    ];

    /**
     * Get the teacher associated with this record.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the subject associated with this record.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}