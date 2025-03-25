<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hostel_house_id',
        'room_number',
        'floor',
        'room_type',
        'capacity',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    /**
     * Get the house that this room belongs to.
     */
    public function house()
    {
        return $this->belongsTo(HostelHouse::class, 'hostel_house_id');
    }

    /**
     * Get the beds in this room.
     */
    public function beds()
    {
        return $this->hasMany(HostelBed::class);
    }

    /**
     * Get the currently occupied beds in this room.
     */
    public function occupiedBeds()
    {
        return $this->beds()->where('status', 'occupied');
    }

    /**
     * Get the currently available beds in this room.
     */
    public function availableBeds()
    {
        return $this->beds()->where('status', 'available');
    }

    /**
     * Get the current occupancy count.
     */
    public function getOccupancyCountAttribute()
    {
        return $this->occupiedBeds()->count();
    }

    /**
     * Get the vacancy count.
     */
    public function getVacancyCountAttribute()
    {
        return $this->capacity - $this->occupancy_count;
    }

    /**
     * Get the occupancy percentage.
     */
    public function getOccupancyPercentageAttribute()
    {
        if ($this->capacity === 0) {
            return 0;
        }
        
        return round(($this->occupancy_count / $this->capacity) * 100, 2);
    }

    /**
     * Scope a query to only include active rooms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to include rooms with available beds.
     */
    public function scopeHasAvailableBeds($query)
    {
        return $query->whereHas('beds', function ($query) {
            $query->where('status', 'available');
        });
    }
}