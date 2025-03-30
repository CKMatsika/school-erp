<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id', 
        'leave_type', 
        'total_days', 
        'used_days', 
        'remaining_days'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
