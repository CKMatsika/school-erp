<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'maintenance_date',
        'performed_by',
        'cost',
        'notes',
        'next_maintenance_date',
        'attachments',
        'status', // 'scheduled', 'in-progress', 'completed', 'cancelled'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
        'attachments' => 'array',
    ];

    /**
     * Get the asset that owns the maintenance record.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Scope a query to only include scheduled maintenance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope a query to only include upcoming maintenance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('status', 'scheduled')
                    ->whereDate('maintenance_date', '>=', now())
                    ->whereDate('maintenance_date', '<=', now()->addDays($days));
    }

    /**
     * Scope a query to only include maintenance by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $type)
    {
        return $query->where('maintenance_type', $type);
    }

    /**
     * Scope a query to only include completed maintenance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if maintenance is overdue.
     *
     * @return bool
     */
    public function isOverdue()
    {
        return $this->status !== 'completed' && $this->maintenance_date < now();
    }

    /**
     * Mark the maintenance as completed.
     *
     * @param  array  $completionData
     * @return bool
     */
    public function markAsCompleted($completionData)
    {
        $this->performed_by = $completionData['performed_by'] ?? $this->performed_by;
        $this->cost = $completionData['cost'] ?? $this->cost;
        $this->notes = isset($completionData['notes']) ? $this->notes . "\n" . $completionData['notes'] : $this->notes;
        $this->next_maintenance_date = $completionData['next_maintenance_date'] ?? null;
        $this->status = 'completed';
        
        // Add attachments if provided
        if (isset($completionData['attachments']) && is_array($completionData['attachments'])) {
            $attachments = $this->attachments ?? [];
            foreach ($completionData['attachments'] as $attachment) {
                $attachments[] = $attachment;
            }
            $this->attachments = $attachments;
        }
        
        return $this->save();
    }

    /**
     * Schedule next maintenance based on current record.
     *
     * @param  int  $intervalDays
     * @return AssetMaintenance
     */
    public function scheduleNextMaintenance($intervalDays = null)
    {
        // If next maintenance date is set, use that, otherwise calculate from interval
        $nextDate = $this->next_maintenance_date;
        if (!$nextDate && $intervalDays) {
            $nextDate = $this->maintenance_date->addDays($intervalDays);
        }

        if ($nextDate) {
            return self::create([
                'asset_id' => $this->asset_id,
                'maintenance_type' => $this->maintenance_type,
                'maintenance_date' => $nextDate,
                'status' => 'scheduled',
                'notes' => "Scheduled from previous maintenance (ID: {$this->id})",
            ]);
        }
        
        return null;
    }
}