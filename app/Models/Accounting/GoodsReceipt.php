<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'receipt_date',
        'delivery_note',
        'received_by',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    /**
     * Get the purchase order for this goods receipt
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the user who created this goods receipt
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the items in this goods receipt
     */
    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    /**
     * Check if this goods receipt can be completed
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'draft' && $this->items()->count() > 0;
    }
}