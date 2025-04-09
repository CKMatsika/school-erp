<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Assuming User model namespace
// use App\Models\HumanResources\Staff; // Already in same namespace

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $table = 'attendance_records';

    protected $fillable = [
        'staff_id',
        'attendance_date',
        'clock_in_time',
        'clock_out_time',
        'status',
        'source',
        'work_duration',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        // Store time as string or use datetime casts carefully
        // 'clock_in_time' => 'datetime:H:i:s', // Might cause issues if DB type is TIME
        // 'clock_out_time' => 'datetime:H:i:s', // Might cause issues if DB type is TIME
        'work_duration' => 'decimal:2',
    ];

    // --- Relationships ---

    /**
     * Get the staff member this record belongs to.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the user who manually recorded this entry (if applicable).
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}