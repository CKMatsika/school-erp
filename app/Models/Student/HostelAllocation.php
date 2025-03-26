<?php
// File: app/Models/Student/HostelAllocation.php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AcademicYear;

class HostelAllocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'bed_id',
        'academic_year_id',
        'allocation_date',
        'expiry_date',
        'is_current',
        'status',
        'notes'
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'expiry_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Get the student for this allocation.
     */
    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    /**
     * Get the bed for this allocation.
     */
    public function bed()
    {
        return $this->belongsTo(HostelBed::class, 'bed_id');
    }

    /**
     * Get the academic year for this allocation.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Scope a query to only include current allocations.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * End this allocation and free up the bed.
     */
    public function endAllocation()
    {
        // Only process if this is a current allocation
        if (!$this->is_current) {
            return false;
        }

        // Start a transaction
        \DB::beginTransaction();
        
        try {
            // Update the allocation
            $this->update([
                'is_current' => false,
                'status' => 'ended',
                'expiry_date' => now(),
            ]);
            
            // Update the bed status
            $this->bed->update([
                'status' => 'available'
            ]);
            
            // Update the student's boarding status if needed
            $activeAllocations = $this->student->hostelAllocations()->where('is_current', true)->count();
            if ($activeAllocations === 0) {
                $this->student->update([
                    'boarding_status' => 'day_scholar',
                    'current_hostel_allocation_id' => null
                ]);
            }
            
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            return false;
        }
    }

    /**
     * Get the formatted allocation details.
     */
    public function getAllocationDetailsAttribute()
    {
        $bed = $this->bed;
        $room = $bed->room;
        $house = $room->house;
        
        return "House: {$house->name}, Room: {$room->room_number}, Bed: {$bed->bed_number}";
    }
}