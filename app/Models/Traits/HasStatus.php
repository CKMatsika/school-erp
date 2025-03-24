<?php

namespace App\Models\Traits;

trait HasStatus
{
    /**
     * Check if the model has the given status.
     *
     * @param string|array $status
     * @return bool
     */
    public function hasStatus($status)
    {
        if (is_array($status)) {
            return in_array($this->status, $status);
        }
        
        return $this->status === $status;
    }

    /**
     * Scope a query to only include models with the given status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }
        
        return $query->where('status', $status);
    }

    /**
     * Update the model's status.
     *
     * @param string $status
     * @return bool
     */
    public function updateStatus($status)
    {
        return $this->update(['status' => $status]);
    }
}