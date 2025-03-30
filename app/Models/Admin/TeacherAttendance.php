<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TeacherAttendance extends Model
{
    protected $fillable = [
        'user_id', 
        'date', 
        'check_in_time', 
        'check_out_time', 
        'status', 
        'remarks', 
        'latitude', 
        'longitude', 
        'ip_address'
    ];

    /**
     * Get the user associated with the attendance record
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}