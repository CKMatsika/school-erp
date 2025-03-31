<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ITEquipment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'it_equipment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type', // 'computer', 'printer', 'scanner', 'server', 'network', 'peripheral', 'other'
        'model',
        'manufacturer',
        'serial_number',
        'status', // 'in-use', 'available', 'maintenance', 'repair', 'retired'
        'purchase_date',
        'purchase_cost',
        'warranty_expiry',
        'supplier_id',
        'location_id',
        'department_id',
        'assigned_to',
        'ip_address',
        'mac_address',
        'operating_system',
        'specs',
        'last_maintenance_date',
        'next_maintenance_date',
        'notes',
        'photo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_cost' => 'decimal:2',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'specs' => 'array',
    ];

    /**
     * Get the supplier that provides the equipment.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the location of the equipment.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the department that owns the equipment.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the staff member assigned to the equipment.
     */
    public function assignedTo()
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    /**
     * Get the maintenance records for the equipment.
     */
    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRequest::class, 'equipment_id')
                    ->where('equipment_type', 'it');
    }

    /**
     * Get the support tickets for the equipment.
     */
    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'equipment_id')
                    ->where('equipment_type', 'it');
    }

    /**
     * Scope a query to only include equipment of a given type.
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
     * Scope a query to only include equipment with a given status.
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
     * Scope a query to only include equipment assigned to a specific staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $staffId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedTo($query, $staffId)
    {
        return $query->where('assigned_to', $staffId);
    }

    /**
     * Scope a query to only include equipment that needs maintenance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNeedsMaintenance($query)
    {
        return $query->whereNotNull('next_maintenance_date')
                    ->where('next_maintenance_date', '<=', now());
    }

    /**
     * Scope a query to only include equipment under warranty.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnderWarranty($query)
    {
        return $query->whereNotNull('warranty_expiry')
                    ->where('warranty_expiry', '>=', now());
    }

    /**
     * Check if equipment is under warranty.
     *
     * @return bool
     */
    public function isUnderWarranty()
    {
        return $this->warranty_expiry && $this->warranty_expiry >= now();
    }

    /**
     * Check if equipment needs maintenance.
     *
     * @return bool
     */
    public function needsMaintenance()
    {
        return $this->next_maintenance_date && $this->next_maintenance_date <= now();
    }

    /**
     * Calculate warranty remaining days.
     *
     * @return int|null
     */
    public function getWarrantyRemainingDaysAttribute()
    {
        if (!$this->warranty_expiry) {
            return null;
        }
        
        if ($this->warranty_expiry < now()) {
            return 0;
        }
        
        return now()->diffInDays($this->warranty_expiry);
    }

    /**
     * Calculate equipment age in days.
     *
     * @return int|null
     */
    public function getAgeInDaysAttribute()
    {
        if (!$this->purchase_date) {
            return null;
        }
        
        return $this->purchase_date->diffInDays(now());
    }

    /**
     * Set equipment status to maintenance.
     *
     * @param  string|null  $reason
     * @return bool
     */
    public function setToMaintenance($reason = null)
    {
        $this->status = 'maintenance';
        
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          "Set to maintenance on " . now() . ". Reason: {$reason}";
        }
        
        return $this->save();
    }

    /**
     * Assign equipment to staff.
     *
     * @param  int  $staffId
     * @param  string|null  $notes
     * @return bool
     */
    public function assignToStaff($staffId, $notes = null)
    {
        $this->assigned_to = $staffId;
        $this->status = 'in-use';
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          "Assigned to staff ID {$staffId} on " . now() . ". {$notes}";
        }
        
        return $this->save();
    }

    /**
     * Unassign equipment.
     *
     * @param  string|null  $reason
     * @return bool
     */
    public function unassign($reason = null)
    {
        $previousAssignee = $this->assigned_to;
        $this->assigned_to = null;
        $this->status = 'available';
        
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          "Unassigned from staff ID {$previousAssignee} on " . now() . ". Reason: {$reason}";
        }
        
        return $this->save();
    }

    /**
     * Schedule next maintenance.
     *
     * @param  \DateTime|string  $date
     * @return bool
     */
    public function scheduleNextMaintenance($date)
    {
        $this->next_maintenance_date = $date;
        return $this->save();
    }
}