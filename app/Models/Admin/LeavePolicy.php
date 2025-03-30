<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    protected $fillable = [
        'leave_type', 
        'description', 
        'max_days', 
        'eligibility_criteria'
    ];
}