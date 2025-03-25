<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelHouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'gender',
        'description',
        'location',
        'warden_name',
        'warden_contact',
        'is_active',
        'capacity',
        'floor_count'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
        'floor_count' => 'integer',
    ];

    /**
     * Get the rooms in this house.
     */
    public function rooms()
    {
        return $this->hasMany(HostelRoom::class);
    }

    /**
     * Get the school that this house belongs to.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the current occupancy count.
     */
    public function getOccupancyCountAttribute()
    {
        return $this->rooms()
            ->withCount(['beds' => function ($query) {
                $query->where('status', 'occupied');
            }])
            ->get()
            ->sum('beds_count');
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
     * Scope a query to only include active houses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by gender.
     */
    public function scopeGender($query, $gender)
    {
        if ($gender) {
            return $query->where('gender', $gender);
        }
        
        return $query;
    }
}