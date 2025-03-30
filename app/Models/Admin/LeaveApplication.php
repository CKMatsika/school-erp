<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $fillable = [
        'user_id', 
        'leave_type', 
        'start_date', 
        'end_date', 
        'total_days', 
        'reason', 
        'status', 
        'approved_by', 
        'approved_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
