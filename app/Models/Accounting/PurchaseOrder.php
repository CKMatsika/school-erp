<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'purchase_request_id',
        'date_issued',
        'date_expected',
        'status',
        'notes',
        'tax_rate',
        'shipping_cost',
        'payment_terms',
        'delivery_terms',
        'created_by',
        'approved_by',
        'approved_at',
        'reference',
        'academic_year_id',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'date_expected' => 'date',
        'approved_at' => 'datetime',
        'tax_rate' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    /**
     * Get the supplier for this purchase order
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the purchase request that this PO is based on
     */
    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    /**
     * Get the user who created this purchase order
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this purchase order
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the academic year for this purchase order
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get all items in this purchase order
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get all goods receipts for this purchase order
     */
    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    /**
     * Calculate the subtotal
     */
    public function getSubtotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    /**
     * Calculate the tax amount
     */
    public function getTaxAmountAttribute()
    {
        return $this->subtotal * ($this->tax_rate / 100);
    }

    /**
     * Calculate the total amount
     */
    public function getTotalAmountAttribute()
    {
        return $this->subtotal + $this->tax_amount + $this->shipping_cost;
    }

    /**
     * Calculate the amount received
     */
    public function getAmountReceivedAttribute()
    {
        $receivedItems = $this->goodsReceipts->flatMap(function ($receipt) {
            return $receipt->items;
        });
        
        $totalReceived = 0;
        foreach ($this->items as $item) {
            $received = $receivedItems->where('purchase_order_item_id', $item->id)->sum('quantity_received');
            $totalReceived += $received * $item->unit_price;
        }
        
        return $totalReceived;
    }

    /**
     * Calculate the percentage received
     */
    public function getPercentageReceivedAttribute()
    {
        if ($this->subtotal == 0) {
            return 0;
        }
        
        return min(100, round(($this->amount_received / $this->subtotal) * 100));
    }

    /**
     * Check if the PO is fully received
     */
    public function isFullyReceived()
    {
        return $this->percentage_received >= 100;
    }

    /**
     * Check if the PO can be approved
     */
    public function canBeApproved()
    {
        return $this->status === 'pending' && $this->items()->count() > 0;
    }

    /**
     * Check if the PO can be issued
     */
    public function canBeIssued()
    {
        return $this->status === 'approved';
    }
}