<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'vendor',
        'version',
        'license_key',
        'license_type', // 'perpetual', 'subscription', 'open-source', 'freeware'
        'purchase_date',
        'purchase_cost',
        'expiry_date',
        'renewal_date',
        'seats',
        'seats_used',
        'department_id',
        'category', // 'os', 'productivity', 'security', 'development', 'multimedia', 'educational', 'other'
        'installation_media',
        'notes',
        'status', // 'active', 'expired', 'maintenance', 'retired'
        'support_email',
        'support_phone',
        'support_url',
        'documentation_url',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'expiry_date' => 'date',
        'renewal_date' => 'date',
        'seats' => 'integer',
        'seats_used' => 'integer',
    ];

    /**
     * Get the department that owns the software.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the staff member who created this software entry.
     */
    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the staff member who last updated this software entry.
     */
    public function updatedBy()
    {
        return $this->belongsTo(Staff::class, 'updated_by');
    }

    /**
     * Get the installations for this software.
     */
    public function installations()
    {
        return $this->hasMany(SoftwareInstallation::class);
    }

    /**
     * Scope a query to only include active software.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include software with a specific license type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $licenseType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithLicenseType($query, $licenseType)
    {
        return $query->where('license_type', $licenseType);
    }

    /**
     * Scope a query to only include software in a specific category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include software for a specific department.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to only include software with expiring licenses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpiringWithin($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', now())
                    ->whereDate('expiry_date', '<=', now()->addDays($days));
    }

    /**
     * Scope a query to only include software with expired licenses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<', now())
                    ->where('status', '!=', 'expired');
    }

    /**
     * Scope a query to only include software due for renewal.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueForRenewal($query, $days = 30)
    {
        return $query->whereNotNull('renewal_date')
                    ->whereDate('renewal_date', '>=', now())
                    ->whereDate('renewal_date', '<=', now()->addDays($days));
    }

    /**
     * Check if software license is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    /**
     * Check if software license needs renewal.
     *
     * @param  int  $days
     * @return bool
     */
    public function needsRenewal($days = 30)
    {
        return $this->renewal_date && 
               $this->renewal_date <= now()->addDays($days) &&
               $this->renewal_date >= now();
    }

    /**
     * Check if software has available seats.
     *
     * @return bool
     */
    public function hasAvailableSeats()
    {
        return $this->seats > $this->seats_used;
    }

    /**
     * Get the number of available seats.
     *
     * @return int
     */
    public function getAvailableSeatsAttribute()
    {
        return max(0, $this->seats - $this->seats_used);
    }

    /**
     * Get days until license expiry.
     *
     * @return int|null
     */
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        if ($this->expiry_date < now()) {
            return -1 * now()->diffInDays($this->expiry_date); // Negative days if expired
        }
        
        return now()->diffInDays($this->expiry_date);
    }

    /**
     * Update seats count based on installations.
     *
     * @return bool
     */
    public function updateSeatsUsed()
    {
        $this->seats_used = $this->installations()->count();
        return $this->save();
    }

    /**
     * Install software on a device.
     *
     * @param  array  $installationData
     * @return SoftwareInstallation|bool
     */
    public function installOnDevice($installationData)
    {
        // Check if has available seats
        if (!$this->hasAvailableSeats() && $this->seats > 0) {
            return false;
        }
        
        // Create installation
        $installation = $this->installations()->create([
            'device_id' => $installationData['device_id'],
            'installed_by' => $installationData['installed_by'],
            'installation_date' => $installationData['installation_date'] ?? now(),
            'version_installed' => $installationData['version_installed'] ?? $this->version,
            'status' => 'active',
            'notes' => $installationData['notes'] ?? null,
        ]);
        
        // Update seats used
        if ($installation) {
            $this->seats_used += 1;
            $this->save();
        }
        
        return $installation;
    }

    /**
     * Renew software license.
     *
     * @param  array  $renewalData
     * @return bool
     */
    public function renewLicense($renewalData)
    {
        $oldExpiryDate = $this->expiry_date;
        
        $this->license_key = $renewalData['license_key'] ?? $this->license_key;
        $this->expiry_date = $renewalData['expiry_date'];
        $this->renewal_date = $renewalData['renewal_date'] ?? null;
        $this->purchase_date = $renewalData['purchase_date'] ?? now();
        $this->purchase_cost = $renewalData['purchase_cost'] ?? $this->purchase_cost;
        $this->seats = $renewalData['seats'] ?? $this->seats;
        $this->status = 'active';
        
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                        "License renewed on " . now() . ". " . 
                        "Previous expiry date: " . ($oldExpiryDate ? $oldExpiryDate->format('Y-m-d') : 'None');
        
        return $this->save();
    }

    /**
     * Update software version.
     *
     * @param  string  $newVersion
     * @param  string|null  $notes
     * @return bool
     */
    public function updateVersion($newVersion, $notes = null)
    {
        $oldVersion = $this->version;
        $this->version = $newVersion;
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                           "Version updated from {$oldVersion} to {$newVersion} on " . now() . ". {$notes}";
        }
        
        return $this->save();
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'active':
                return 'badge-success';
            case 'expired':
                return 'badge-danger';
            case 'maintenance':
                return 'badge-warning';
            case 'retired':
                return 'badge-secondary';
            default:
                return 'badge-info';
        }
    }

    /**
     * Get licensing status.
     *
     * @return string
     */
    public function getLicensingStatusAttribute()
    {
        if ($this->license_type === 'open-source' || $this->license_type === 'freeware') {
            return 'Free';
        }
        
        if ($this->isExpired()) {
            return 'Expired';
        }
        
        if ($this->needsRenewal()) {
            return 'Due for Renewal';
        }
        
        return 'Licensed';
    }
}