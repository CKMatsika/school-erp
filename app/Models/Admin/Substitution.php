<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Substitution extends Model
{
    protected $fillable = [
        'original_staff_id', 
        'substitute_staff_id', 
        'start_date', 
        'end_date', 
        'reason', 
        'status'
    ];

    public function originalStaff()
    {
        return $this->belongsTo(User::class, 'original_staff_id');
    }

    public function substituteStaff()
    {
        return $this->belongsTo(User::class, 'substitute_staff_id');
    }
}