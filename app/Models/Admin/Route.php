<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'driver_id', 
        'route_number', 
        'start_location', 
        'end_location', 
        'distance', 
        'travel_time', 
        'status'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}