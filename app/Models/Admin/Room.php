<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name', 
        'type', 
        'capacity', 
        'location', 
        'facilities', 
        'status'
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}