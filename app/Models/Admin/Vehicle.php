<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type', // 'bus', 'van', 'car', 'truck', etc.
        'registration_number',
        'model',
        'make',
        'year',
        'seating_capacity',
        'fuel_type',
        'insurance_expiry',
        'registration_expiry',
        'purchase_date',
        'purchase_price',
        'current_mileage',
        'status', // 'active', 'maintenance', 'out-of-service'
        'driver_id',
        'gps_enabled',
        'camera_enabled',
        'ac_enabled',
        'notes',
        'photos', // JSON array of photo paths
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'insurance_expiry' => 'date',
        'registration_expiry' => 'date',
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_mileage' => 'integer',
        'seating_capacity' => 'integer',
        'year' => 'integer',
        'gps_enabled' => 'boolean',
        'camera_enabled' => 'boolean',
        'ac_enabled' => 'boolean',
        'photos' => 'array',
    ];

    /**
     * Get the driver assigned to the vehicle.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the routes for the vehicle.
     */
    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Get the maintenance records for the vehicle.
     */
    public function maintenanceRecords()
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    /**
     * Get the fuel records for the vehicle.
     */
    public function fuelRecords()
    {
        return $this->hasMany(VehicleFuel::class);
    }

    /**
     * Get the documents uploaded for the vehicle.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get the trips assigned to this vehicle.
     */
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Scope a query to only include active vehicles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include vehicles with expiring insurance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithExpiringInsurance($query, $days = 30)
    {
        return $query->whereDate('insurance_expiry', '>=', now())
                    ->whereDate('insurance_expiry', '<=', now()->addDays($days));
    }

    /**
     * Scope a query to only include vehicles with expiring registration.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithExpiringRegistration($query, $days = 30)
    {
        return $query->whereDate('registration_expiry', '>=', now())
                    ->whereDate('registration_expiry', '<=', now()->addDays($days));
    }

    /**
     * Scope a query to only include vehicles of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include vehicles that need maintenance soon.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNeedingMaintenance($query, $days = 7)
    {
        return $query->whereHas('maintenanceRecords', function ($query) use ($days) {
            $query->whereDate('next_maintenance_date', '>=', now())
                  ->whereDate('next_maintenance_date', '<=', now()->addDays($days));
        });
    }

    /**
     * Check if vehicle is available (active and not assigned to other trips for a specific date/time).
     *
     * @param  string  $date
     * @param  string  $startTime
     * @param  string  $endTime
     * @return bool
     */
    public function isAvailableFor($date, $startTime, $endTime)
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        // Check if vehicle is not assigned to any trip during this time
        $conflictingTrips = Trip::where('vehicle_id', $this->id)
                               ->whereDate('date', $date)
                               ->where(function ($query) use ($startTime, $endTime) {
                                   $query->where(function ($q) use ($startTime, $endTime) {
                                       $q->where('start_time', '<=', $startTime)
                                         ->where('end_time', '>=', $startTime);
                                   })->orWhere(function ($q) use ($startTime, $endTime) {
                                       $q->where('start_time', '<=', $endTime)
                                         ->where('end_time', '>=', $endTime);
                                   })->orWhere(function ($q) use ($startTime, $endTime) {
                                       $q->where('start_time', '>=', $startTime)
                                         ->where('end_time', '<=', $endTime);
                                   });
                               })
                               ->count();
                               
        return $conflictingTrips === 0;
    }

    /**
     * Check if insurance is expired.
     *
     * @return bool
     */
    public function isInsuranceExpired()
    {
        return $this->insurance_expiry && $this->insurance_expiry < now();
    }

    /**
     * Check if registration is expired.
     *
     * @return bool
     */
    public function isRegistrationExpired()
    {
        return $this->registration_expiry && $this->registration_expiry < now();
    }

    /**
     * Get days until insurance expiry.
     *
     * @return int|null
     */
    public function getDaysUntilInsuranceExpiryAttribute()
    {
        if (!$this->insurance_expiry) {
            return null;
        }
        
        if ($this->insurance_expiry < now()) {
            return -1 * now()->diffInDays($this->insurance_expiry); // Negative days if expired
        }
        
        return now()->diffInDays($this->insurance_expiry);
    }

    /**
     * Get days until registration expiry.
     *
     * @return int|null
     */
    public function getDaysUntilRegistrationExpiryAttribute()
    {
        if (!$this->registration_expiry) {
            return null;
        }
        
        if ($this->registration_expiry < now()) {
            return -1 * now()->diffInDays($this->registration_expiry); // Negative days if expired
        }
        
        return now()->diffInDays($this->registration_expiry);
    }

    /**
     * Calculate vehicle age in years.
     *
     * @return float|null
     */
    public function getAgeInYearsAttribute()
    {
        if (!$this->purchase_date) {
            return null;
        }
        
        return $this->purchase_date->diffInDays(now()) / 365;
    }

    /**
     * Upload a document for this vehicle.
     *
     * @param  array  $documentData
     * @return Document
     */
    public function uploadDocument($documentData)
    {
        return $this->documents()->create([
            'document_type' => $documentData['document_type'],
            'file_path' => $documentData['file_path'],
            'file_name' => $documentData['file_name'],
            'expiry_date' => $documentData['expiry_date'] ?? null,
            'remarks' => $documentData['remarks'] ?? null,
        ]);
    }

    /**
     * Add maintenance record for this vehicle.
     *
     * @param  array  $maintenanceData
     * @return VehicleMaintenance
     */
    public function addMaintenance($maintenanceData)
    {
        return $this->maintenanceRecords()->create([
            'maintenance_type' => $maintenanceData['maintenance_type'],
            'maintenance_date' => $maintenanceData['maintenance_date'],
            'performed_by' => $maintenanceData['performed_by'],
            'cost' => $maintenanceData['cost'],
            'description' => $maintenanceData['description'],
            'next_maintenance_date' => $maintenanceData['next_maintenance_date'] ?? null,
            'odometer_reading' => $maintenanceData['odometer_reading'],
            'attachments' => $maintenanceData['attachments'] ?? null,
        ]);
    }

    /**
     * Add fuel record for this vehicle.
     *
     * @param  array  $fuelData
     * @return VehicleFuel
     */
    public function addFuel($fuelData)
    {
        return $this->fuelRecords()->create([
            'date' => $fuelData['date'],
            'fuel_quantity' => $fuelData['fuel_quantity'],
            'fuel_cost' => $fuelData['fuel_cost'],
            'odometer_reading' => $fuelData['odometer_reading'],
            'filled_by' => $fuelData['filled_by'],
            'fuel_station' => $fuelData['fuel_station'] ?? null,
            'remarks' => $fuelData['remarks'] ?? null,
        ]);
    }
}