<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAllocation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'asset_id',
        'allocated_to_type', // 'staff', 'department', 'student'
        'allocated_to_id',
        'allocated_date',
        'expected_return_date',
        'actual_return_date',
        'return_condition',
        'notes',
        'issued_by',
        'received_by',
        'condition_at_allocation',
        'quantity',
        'status', // 'allocated', 'returned', 'overdue', 'lost'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'allocated_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
    ];

    /**
     * Get the asset that is allocated.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the staff member who issued the asset.
     */
    public function issuedBy()
    {
        return $this->belongsTo(Staff::class, 'issued_by');
    }

    /**
     * Get the staff member who received the returned asset.
     */
    public function receivedBy()
    {
        return $this->belongsTo(Staff::class, 'received_by');
    }

    /**
     * Get the allocated entity (staff, department, or student).
     */
    public function allocatedTo()
    {
        if ($this->allocated_to_type === 'staff') {
            return $this->belongsTo(Staff::class, 'allocated_to_id');
        } elseif ($this->allocated_to_type === 'department') {
            return $this->belongsTo(Department::class, 'allocated_to_id');
        } elseif ($this->allocated_to_type === 'student') {
            return $this->belongsTo(Student::class, 'allocated_to_id');
        }
    }

    /**
     * Scope a query to only include current allocations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query)
    {
        return $query->whereNull('actual_return_date');
    }

    /**
     * Scope a query to only include overdue allocations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->whereNull('actual_return_date')
                    ->where('expected_return_date', '<', now());
    }

    /**
     * Scope a query to only include allocations by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $type)
    {
        return $query->where('allocated_to_type', $type);
    }

    /**
     * Check if allocation is overdue.
     *
     * @return bool
     */
    public function isOverdue()
    {
        return !$this->actual_return_date && $this->expected_return_date < now();
    }

    /**
     * Get allocation duration in days.
     *
     * @return int
     */
    public function getDurationAttribute()
    {
        $returnDate = $this->actual_return_date ?? now();
        return $this->allocated_date->diffInDays($returnDate);
    }

    /**
     * Mark the allocation as returned.
     *
     * @param  array  $returnData
     * @return bool
     */
    public function markAsReturned($returnData)
    {
        $this->actual_return_date = $returnData['return_date'] ?? now();
        $this->return_condition = $returnData['return_condition'] ?? 'good';
        $this->received_by = $returnData['received_by'] ?? null;
        $this->notes = isset($returnData['notes']) ? $this->notes . "\n" . $returnData['notes'] : $this->notes;
        $this->status = 'returned';
        
        return $this->save();
    }
}