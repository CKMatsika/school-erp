<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'registration_number', 
        'make', 
        'model', 
        'year', 
        'type', 
        'capacity', 
        'insurance_expiry', 
        'last_maintenance_date', 
        'current_mileage', 
        'status'
    ];

    /**
     * Get all trips for this vehicle
     */
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Get the assigned driver (if any)
     */
    public function assignedDriver()
    {
        return $this->belongsTo(Driver::class, 'assigned_driver_id');
    }
}