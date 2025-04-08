<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'purchase_request_item_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'quantity_received',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'quantity_received' => 'decimal:2',
    ];

    /**
     * Get the purchase order that this item belongs to
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the inventory item
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    /**
     * Get the purchase request item
     */
    public function purchaseRequestItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }

    /**
     * Get the goods receipt items for this PO item
     */
    public function goodsReceiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    /**
     * Get the total amount for this line
     */
    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Get the remaining quantity to be received
     */
    public function getRemainingQuantityAttribute()
    {
        return max(0, $this->quantity - $this->quantity_received);
    }

    /**
     * Check if the item is fully received
     */
    public function isFullyReceived(): bool
    {
        return $this->remaining_quantity <= 0;
    }
}