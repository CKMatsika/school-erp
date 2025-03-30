<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title', 
        'description', 
        'room_id', 
        'start_time', 
        'end_time', 
        'event_type', 
        'organizer', 
        'expected_attendees', 
        'requirements', 
        'status'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}