<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id', 
        'category', 
        'subject', 
        'description', 
        'priority', 
        'status', 
        'assigned_to', 
        'resolution_details'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}