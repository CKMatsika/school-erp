<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'vehicle_id', 
        'driver_id', 
        'start_location', 
        'end_location', 
        'start_time', 
        'end_time', 
        'distance', 
        'purpose', 
        'status', 
        'fuel_consumed', 
        'route_description'
    ];

    /**
     * Get the vehicle associated with the trip
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver associated with the trip
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}