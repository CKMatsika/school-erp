<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $fillable = [
        'user_id', 
        'date', 
        'check_in_time', 
        'check_out_time', 
        'status', 
        'remarks'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

