<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'user_id', 
        'category', 
        'description', 
        'priority', 
        'location', 
        'request_date', 
        'status', 
        'resolved_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}