<?php
// File: app/Models/Student/HostelBed.php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelBed extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_id',
        'bed_number',
        'description',
        'status'
    ];

    /**
     * Get the room that this bed belongs to.
     */
    public function room()
    {
        return $this->belongsTo(HostelRoom::class, 'room_id');
    }

    /**
     * Get the current allocation for this bed.
     */
    public function currentAllocation()
    {
        return $this->hasOne(HostelAllocation::class, 'bed_id')
            ->where('is_current', true);
    }

    /**
     * Get all allocations for this bed.
     */
    public function allocations()
    {
        return $this->hasMany(HostelAllocation::class, 'bed_id');
    }
}