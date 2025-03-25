<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelBed extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hostel_room_id',
        'bed_number',
        'status',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the room that this bed belongs to.
     */
    public function room()
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id');
    }

    /**
     * Get the current allocation for this bed.
     */
    public function currentAllocation()
    {
        return $this->hasOne(HostelAllocation::class)->where('is_current', true);
    }

    /**
     * Get all allocations for this bed.
     */
    public function allocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }

    /**
     * Get the current student allocated to this bed.
     */
    public function student()
    {
        return $this->currentAllocation?->student;
    }

    /**
     * Scope a query to only include available beds.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    /**
     * Scope a query to only include occupied beds.
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope a query to filter by gender of the house.
     */
    public function scopeGender($query, $gender)
    {
        if ($gender) {
            return $query->whereHas('room.house', function ($query) use ($gender) {
                $query->where('gender', $gender)->orWhere('gender', 'mixed');
            });
        }
        
        return $query;
    }

    /**
     * Get the full location of this bed (House, Room, Bed)
     */
    public function getFullLocationAttribute()
    {
        return $this->room->house->name . ' - Room ' . $this->room->room_number . ' - Bed ' . $this->bed_number;
    }
}