<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_number',
        'date_requested',
        'date_required',
        'department',
        'requested_by',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'academic_year_id',
    ];

    protected $casts = [
        'date_requested' => 'date',
        'date_required' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the user who requested this purchase request
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved this purchase request
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected this purchase request
     */
    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the academic year for this purchase request
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get the items in this purchase request
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    /**
     * Get the purchase orders created from this PR
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'purchase_request_id');
    }

    /**
     * Get the total amount for this purchase request
     */
    public function getTotalAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->estimated_unit_cost;
        });
    }

    /**
     * Check if the PR can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending' && $this->items()->count() > 0;
    }

    /**
     * Check if the PR can be rejected
     */
    public function canBeRejected(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the PR can be converted to a PO
     */
    public function canBeConvertedToPO(): bool
    {
        return $this->status === 'approved' && !$this->hasActivePO();
    }

    /**
     * Check if the PR already has an active PO
     */
    public function hasActivePO(): bool
    {
        return $this->purchaseOrders()->whereIn('status', ['draft', 'pending', 'approved', 'issued'])->exists();
    }
}