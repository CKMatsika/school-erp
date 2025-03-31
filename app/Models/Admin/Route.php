<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'start_location',
        'end_location',
        'distance',
        'estimated_time',
        'vehicle_id',
        'driver_id',
        'description',
        'status', // 'active', 'inactive', 'suspended'
        'morning_start_time',
        'morning_end_time',
        'afternoon_start_time',
        'afternoon_end_time',
        'days_of_week', // JSON array like ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'distance' => 'decimal:2',
        'days_of_week' => 'array',
    ];

    /**
     * Get the vehicle assigned to the route.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver assigned to the route.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the stops for the route.
     */
    public function stops()
    {
        return $this->hasMany(RouteStop::class)->orderBy('sequence');
    }

    /**
     * Get the students assigned to this route.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'route_students')
                    ->withPivot('pickup_stop_id', 'dropoff_stop_id', 'pickup_time', 'dropoff_time', 'status');
    }

    /**
     * Get the trips for this route.
     */
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Scope a query to only include active routes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include routes that run on a specific day.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $day
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRunsOn($query, $day)
    {
        return $query->whereRaw("JSON_CONTAINS(days_of_week, '\"$