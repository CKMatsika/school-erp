<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'quantity_received',
        'quantity_rejected',
        'rejection_reason',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:2',
        'quantity_rejected' => 'decimal:2',
    ];

    /**
     * Get the goods receipt that this item belongs to
     */
    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    /**
     * Get the purchase order item
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * Get the total quantity (received + rejected)
     */
    public function getTotalQuantityAttribute()
    {
        return $this->quantity_received + $this->quantity_rejected;
    }
}