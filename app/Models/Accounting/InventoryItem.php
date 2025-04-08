<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'description',
        'category_id',
        'unit_of_measure',
        'current_stock',
        'reorder_level',
        'is_active',
        'barcode',
        'unit_cost',
        'location',
        'notes',
        'last_received_date',
        'is_fixed_asset',
        'asset_type',
        'depreciation_method',
        'useful_life',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_fixed_asset' => 'boolean',
        'current_stock' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'last_received_date' => 'date',
    ];

    /**
     * Get the category that this item belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    /**
     * Get stock movements for this item
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'item_id');
    }

    /**
     * Get purchase order items for this inventory item
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'item_id');
    }

    /**
     * Get purchase request items for this inventory item
     */
    public function purchaseRequestItems(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class, 'item_id');
    }

    /**
     * Check if the item is low on stock
     */
    public function isLowOnStock(): bool
    {
        if ($this->reorder_level === null) {
            return false;
        }
        
        return $this->current_stock <= $this->reorder_level;
    }

    /**
     * Calculate the total value of this inventory item
     */
    public function getTotalValueAttribute()
    {
        return $this->current_stock * $this->unit_cost;
    }
}