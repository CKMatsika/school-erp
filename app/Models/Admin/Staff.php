<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $fillable = [
        'user_id', 
        'employee_number', 
        'department', 
        'designation', 
        'hire_date', 
        'employment_type', 
        'work_location'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
