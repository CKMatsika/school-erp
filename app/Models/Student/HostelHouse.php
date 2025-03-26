<?php
// File: app/Models/Student/HostelHouse.php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelHouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'gender',
        'capacity',
        'status'
    ];

    /**
     * Get the rooms for the house.
     */
    public function rooms()
    {
        return $this->hasMany(HostelRoom::class, 'house_id');
    }

    /**
     * Get all beds in this house through rooms.
     */
    public function beds()
    {
        return $this->hasManyThrough(HostelBed::class, HostelRoom::class, 'house_id', 'room_id');
    }
}