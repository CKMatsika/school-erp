<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'license_number',
        'license_expiry',
        'phone',
        'address',
        'date_of_birth',
        'joining_date',
        'staff_id', // If the driver is also a staff member
        'photo',
        'status', // 'active', 'on-leave', 'inactive', 'terminated'
        'blood_group',
        'emergency_contact_name',
        'emergency_contact_phone',
        'salary',
        'driving_experience',
        'vehicle_expertise', // Comma-separated list or JSON
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'license_expiry' => 'date',
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'salary' => 'decimal:2',
        'driving_experience' => 'integer',
    ];

    /**
     * Get the staff record associated with the driver.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the vehicles assigned to the driver.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the routes assigned to the driver.
     */
    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Get the attendance records for the driver.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(DriverAttendance::class);
    }

    /**
     * Get the documents uploaded for the driver.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Scope a query to only include active drivers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include drivers with expiring licenses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithExpiringLicense($query, $days = 30)
    {
        return $query->whereDate('license_expiry', '>=', now())
                    ->whereDate('license_expiry', '<=', now()->addDays($days));
    }

    /**
     * Scope a query to only include drivers with expired licenses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithExpiredLicense($query)
    {
        return $query->whereDate('license_expiry', '<', now());
    }

    /**
     * Scope a query to only include drivers who have attended on a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAttendedOn($query, $date)
    {
        return $query->whereHas('attendanceRecords', function ($query) use ($date) {
            $query->whereDate('date', $date)
                  ->where('status', 'present');
        });
    }

    /**
     * Scope a query to only include drivers who were absent on a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAbsentOn($query, $date)
    {
        return $query->whereHas('attendanceRecords', function ($query) use ($date) {
            $query->whereDate('date', $date)
                  ->where('status', 'absent');
        });
    }

    /**
     * Check if driver's license is expired.
     *
     * @return bool
     */
    public function isLicenseExpired()
    {
        return $this->license_expiry && $this->license_expiry < now();
    }

    /**
     * Get days until license expiry.
     *
     * @return int|null
     */
    public function getDaysUntilLicenseExpiryAttribute()
    {
        if (!$this->license_expiry) {
            return null;
        }
        
        if ($this->license_expiry < now()) {
            return -1 * now()->diffInDays($this->license_expiry); // Negative days if expired
        }
        
        return now()->diffInDays($this->license_expiry);
    }

    /**
     * Check if driver is available (active and not assigned to other trips for a specific date/time).
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
        
        // Check attendance
        $attendance = $this->attendanceRecords()
                          ->whereDate('date', $date)
                          ->first();
                          
        if ($attendance && $attendance->status !== 'present') {
            return false;
        }
        
        // Check if driver is not assigned to any trip during this time
        $conflictingTrips = Trip::where('driver_id', $this->id)
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
     * Get trips assigned to this driver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Upload a document for this driver.
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
     * Mark driver attendance.
     *
     * @param  string  $date
     * @param  string  $status
     * @param  string|null  $remarks
     * @return DriverAttendance
     */
    public function markAttendance($date, $status, $remarks = null)
    {
        return $this->attendanceRecords()->updateOrCreate(
            ['date' => $date],
            [
                'status' => $status,
                'remarks' => $remarks,
            ]
        );
    }
}