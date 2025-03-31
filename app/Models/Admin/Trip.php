<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'trip_number',
        'route_id',
        'vehicle_id',
        'driver_id',
        'date',
        'start_time',
        'end_time',
        'distance',
        'fuel_consumption',
        'trip_type', // 'regular', 'field-trip', 'special', 'emergency', 'other'
        'purpose',
        'authorized_by',
        'authorization_date',
        'status', // 'scheduled', 'in-progress', 'completed', 'cancelled'
        'departure_odometer',
        'arrival_odometer',
        'start_location',
        'end_location',
        'passengers_count',
        'notes',
        'attachments',
        'created_by',
        'weather_conditions',
        'expenses',
        'companion_staff',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'distance' => 'decimal:2',
        'fuel_consumption' => 'decimal:2',
        'authorization_date' => 'date',
        'departure_odometer' => 'integer',
        'arrival_odometer' => 'integer',
        'passengers_count' => 'integer',
        'attachments' => 'array',
        'expenses' => 'array',
        'companion_staff' => 'array',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($trip) {
            // Generate trip number if not provided
            if (!$trip->trip_number) {
                $prefix = 'TRIP-';
                $date = date('Ymd');
                $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $trip->trip_number = $prefix . $date . '-' . $random;
            }
        });
    }

    /**
     * Get the route for this trip.
     */
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the vehicle for this trip.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver for this trip.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the staff member who authorized the trip.
     */
    public function authorizedBy()
    {
        return $this->belongsTo(Staff::class, 'authorized_by');
    }

    /**
     * Get the staff member who created the trip.
     */
    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the passengers for this trip.
     */
    public function passengers()
    {
        return $this->hasMany(TripPassenger::class);
    }

    /**
     * Get the checkpoints for this trip.
     */
    public function checkpoints()
    {
        return $this->hasMany(TripCheckpoint::class)->orderBy('sequence');
    }

    /**
     * Get the incidents for this trip.
     */
    public function incidents()
    {
        return $this->hasMany(TripIncident::class);
    }

    /**
     * Scope a query to only include trips for a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope a query to only include trips for a date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate);
    }

    /**
     * Scope a query to only include trips with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include scheduled trips.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope a query to only include in-progress trips.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    /**
     * Scope a query to only include completed trips.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include trips for a specific vehicle.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $vehicleId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    /**
     * Scope a query to only include trips for a specific driver.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $driverId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    /**
     * Scope a query to only include trips for a specific route.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $routeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForRoute($query, $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    /**
     * Scope a query to only include trips of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('trip_type', $type);
    }

    /**
     * Check if the trip is for today.
     *
     * @return bool
     */
    public function isForToday()
    {
        return $this->date->isToday();
    }

    /**
     * Check if the trip is upcoming.
     *
     * @return bool
     */
    public function isUpcoming()
    {
        return $this->date > now() || 
               ($this->date->isToday() && $this->start_time > date('H:i:s'));
    }

    /**
     * Check if the trip is past.
     *
     * @return bool
     */
    public function isPast()
    {
        return $this->date < now() || 
               ($this->date->isToday() && $this->end_time < date('H:i:s'));
    }

    /**
     * Calculate trip duration in minutes.
     *
     * @return int|null
     */
    public function getTripDurationAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }
        
        $start = \DateTime::createFromFormat('H:i:s', $this->start_time);
        $end = \DateTime::createFromFormat('H:i:s', $this->end_time);
        
        // If end is before start, assume it crosses midnight
        if ($end < $start) {
            $end->modify('+1 day');
        }
        
        return $start->diff($end)->h * 60 + $start->diff($end)->i;
    }

    /**
     * Calculate trip duration in hours (formatted).
     *
     * @return string|null
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->trip_duration) {
            return null;
        }
        
        $hours = floor($this->trip_duration / 60);
        $minutes = $this->trip_duration % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Calculate distance traveled based on odometer readings.
     *
     * @return float|null
     */
    public function getDistanceTraveledAttribute()
    {
        if ($this->departure_odometer && $this->arrival_odometer) {
            return max(0, $this->arrival_odometer - $this->departure_odometer);
        }
        
        return $this->distance;
    }

    /**
     * Calculate fuel efficiency (km/l or mpg).
     *
     * @param  string  $unit  'km/l' or 'mpg'
     * @return float|null
     */
    public function getFuelEfficiencyAttribute($unit = 'km/l')
    {
        if (!$this->distance_traveled || !$this->fuel_consumption || $this->fuel_consumption <= 0) {
            return null;
        }
        
        $efficiency = $this->distance_traveled / $this->fuel_consumption;
        
        if ($unit === 'mpg') {
            // Convert km/l to mpg (1 km/l ≈ 2.352 mpg)
            $efficiency *= 2.352;
        }
        
        return round($efficiency, 2);
    }

    /**
     * Start the trip.
     *
     * @param  array  $startData
     * @return bool
     */
    public function startTrip($startData)
    {
        if ($this->status !== 'scheduled') {
            return false;
        }
        
        $this->status = 'in-progress';
        $this->departure_odometer = $startData['departure_odometer'] ?? null;
        $this->start_time = $startData['start_time'] ?? date('H:i:s');
        $this->weather_conditions = $startData['weather_conditions'] ?? null;
        
        if (isset($startData['notes'])) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Trip start notes: {$startData['notes']}";
        }
        
        return $this->save();
    }

    /**
     * Complete the trip.
     *
     * @param  array  $endData
     * @return bool
     */
    public function completeTrip($endData)
    {
        if ($this->status !== 'in-progress') {
            return false;
        }
        
        $this->status = 'completed';
        $this->arrival_odometer = $endData['arrival_odometer'] ?? null;
        $this->end_time = $endData['end_time'] ?? date('H:i:s');
        $this->fuel_consumption = $endData['fuel_consumption'] ?? null;
        
        // Calculate distance if not provided but odometer readings are available
        if (!$this->distance && $this->departure_odometer && $this->arrival_odometer) {
            $this->distance = max(0, $this->arrival_odometer - $this->departure_odometer);
        }
        
        if (isset($endData['notes'])) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Trip end notes: {$endData['notes']}";
        }
        
        return $this->save();
    }

    /**
     * Cancel the trip.
     *
     * @param  string  $reason
     * @return bool
     */
    public function cancelTrip($reason)
    {
        if (!in_array($this->status, ['scheduled', 'in-progress'])) {
            return false;
        }
        
        $this->status = 'cancelled';
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Cancellation reason: {$reason}";
        
        return $this->save();
    }

    /**
     * Add a passenger to the trip.
     *
     * @param  array  $passengerData
     * @return TripPassenger
     */
    public function addPassenger($passengerData)
    {
        $passenger = $this->passengers()->create([
            'passenger_type' => $passengerData['passenger_type'], // 'student', 'staff', 'other'
            'passenger_id' => $passengerData['passenger_id'] ?? null,
            'name' => $passengerData['name'] ?? null,
            'pickup_location' => $passengerData['pickup_location'] ?? null,
            'dropoff_location' => $passengerData['dropoff_location'] ?? null,
            'status' => 'confirmed',
            'notes' => $passengerData['notes'] ?? null,
        ]);
        
        // Update passengers count
        $this->passengers_count = $this->passengers()->count();
        $this->save();
        
        return $passenger;
    }

    /**
     * Add a checkpoint to the trip.
     *
     * @param  array  $checkpointData
     * @return TripCheckpoint
     */
    public function addCheckpoint($checkpointData)
    {
        // Get the highest sequence number
        $maxSequence = $this->checkpoints()->max('sequence') ?? 0;
        
        return $this->checkpoints()->create([
            'location' => $checkpointData['location'],
            'sequence' => $checkpointData['sequence'] ?? ($maxSequence + 1),
            'scheduled_time' => $checkpointData['scheduled_time'] ?? null,
            'actual_time' => $checkpointData['actual_time'] ?? null,
            'status' => $checkpointData['status'] ?? 'pending', // 'pending', 'reached', 'skipped'
            'notes' => $checkpointData['notes'] ?? null,
            'coordinates' => $checkpointData['coordinates'] ?? null,
        ]);
    }

    /**
     * Record an incident during the trip.
     *
     * @param  array  $incidentData
     * @return TripIncident
     */
    public function recordIncident($incidentData)
    {
        return $this->incidents()->create([
            'incident_type' => $incidentData['incident_type'], // 'accident', 'breakdown', 'delay', 'passenger', 'other'
            'incident_time' => $incidentData['incident_time'] ?? now(),
            'location' => $incidentData['location'] ?? null,
            'description' => $incidentData['description'],
            'severity' => $incidentData['severity'] ?? 'medium', // 'low', 'medium', 'high'
            'action_taken' => $incidentData['action_taken'] ?? null,
            'reported_by' => $incidentData['reported_by'] ?? null,
            'attachments' => $incidentData['attachments'] ?? null,
        ]);
    }

    /**
     * Add an expense to the trip.
     *
     * @param  array  $expenseData
     * @return bool
     */
    public function addExpense($expenseData)
    {
        $expenses = $this->expenses ?? [];
        
        $expenses[] = [
            'expense_type' => $expenseData['expense_type'], // 'fuel', 'toll', 'parking', 'maintenance', 'food', 'other'
            'amount' => $expenseData['amount'],
            'date' => $expenseData['date'] ?? now()->format('Y-m-d'),
            'description' => $expenseData['description'] ?? null,
            'receipt' => $expenseData['receipt'] ?? null,
        ];
        
        $this->expenses = $expenses;
        
        return $this->save();
    }

    /**
     * Calculate total expenses.
     *
     * @return float
     */
    public function getTotalExpensesAttribute()
    {
        if (!$this->expenses) {
            return 0;
        }
        
        $total = 0;
        foreach ($this->expenses as $expense) {
            $total += $expense['amount'] ?? 0;
        }
        
        return $total;
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'scheduled':
                return 'badge-info';
            case 'in-progress':
                return 'badge-primary';
            case 'completed':
                return 'badge-success';
            case 'cancelled':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get the trip type badge class.
     *
     * @return string
     */
    public function getTripTypeBadgeClassAttribute()
    {
        switch ($this->trip_type) {
            case 'regular':
                return 'badge-secondary';
            case 'field-trip':
                return 'badge-success';
            case 'special':
                return 'badge-primary';
            case 'emergency':
                return 'badge-danger';
            default:
                return 'badge-info';
        }
    }
}