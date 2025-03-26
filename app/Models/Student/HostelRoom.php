<?php
// File: app/Models/Student/HostelRoom.php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'house_id',
        'room_number',
        'capacity',
        'description',
        'status'
    ];

    /**
     * Get the house that this room belongs to.
     */
    public function house()
    {
        return $this->belongsTo(HostelHouse::class, 'house_id');
    }

    /**
     * Get the beds for this room.
     */
    public function beds()
    {
        return $this->hasMany(HostelBed::class, 'room_id');
    }
}