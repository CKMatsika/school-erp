<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo; // <-- Import MorphTo

class InvoiceItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * Note: 'unit_price' is used here assuming the migration uses that name,
     * matching the controller logic. Adjust if your column is named 'price'.
     * Added 'item_source_id' and 'item_source_type' for the polymorphic relation.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'name',          // Usually auto-filled from source, but can be overridden
        'description',
        'quantity',
        'unit_price',    // Changed from 'price' to match updated controller logic
        'discount',
        'tax_rate_id',
        'tax_amount',    // Calculated tax amount for the line
        'total',         // Calculated total for the line (after discount, including tax)
        'account_id',    // The income account credited for this item
        'item_source_id',   // <-- Added: ID of the source (FeeStructureItem or InventoryItem)
        'item_source_type', // <-- Added: Class name of the source
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity'   => 'decimal:4', // Allow more precision for quantity if needed
        'unit_price' => 'decimal:2', // Changed from 'price'
        'discount'   => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the tax rate associated with the item (if any).
     */
    public function taxRate(): BelongsTo
    {
        // Use optional() or handle null tax_rate_id if 'No Tax' is common
        return $this->belongsTo(TaxRate::class);
    }

    /**
     * Get the income chart of account associated with the item.
     */
    public function account(): BelongsTo
    {
        // Explicit foreign key is good practice
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Get the source model instance (FeeStructureItem or InventoryItem).
     * This defines the polymorphic relationship based on item_source_id/type columns.
     */
    public function itemSource(): MorphTo
    {
        // The name 'itemSource' corresponds to the 'item_source' used in nullableMorphs()
        return $this->morphTo();
    }


    /**
     * Calculate and potentially save the totals for this specific item line.
     *
     * Note: Calling save() here and triggering invoice update might be inefficient
     * if done frequently (e.g., during bulk creation in the controller).
     * Consider moving the save logic to an observer or the controller if needed.
     *
     * This method calculates based on the item's current attributes.
     *
     * @param bool $saveItem Should the item itself be saved after calculation?
     * @param bool $updateInvoice Should the parent invoice totals be updated?
     * @return void
     */
    public function calculateAndSaveTotals(bool $saveItem = true, bool $updateInvoice = true): void
    {
        $quantity = (float) $this->quantity;
        $unitPrice = (float) $this->unit_price;
        $discount = (float) $this->discount;

        $subtotalBeforeDiscount = $unitPrice * $quantity;
        $subtotalAfterDiscount = max(0, $subtotalBeforeDiscount - $discount); // Ensure non-negative

        // Calculate tax based on the subtotal *after* discount
        if ($this->tax_rate_id && $this->relationLoaded('taxRate') && $this->taxRate) {
            // Ensure taxRate relationship is loaded for efficiency if called multiple times
            $rate = (float) $this->taxRate->rate;
            $this->tax_amount = ($subtotalAfterDiscount * $rate) / 100;
        } else {
            // Handle case where tax rate is not set or relationship not loaded
            // If tax_rate_id is set but relation isn't loaded, you might need to fetch it:
            // $taxRateModel = TaxRate::find($this->tax_rate_id);
            // $rate = $taxRateModel ? (float) $taxRateModel->rate : 0;
            // $this->tax_amount = ($subtotalAfterDiscount * $rate) / 100;
            $this->tax_amount = 0; // Default to 0 if no tax_rate_id
        }

        // Calculate final total for the line item
        $this->total = $subtotalAfterDiscount + $this->tax_amount;

        if ($saveItem) {
            $this->save(); // Save the calculated tax_amount and total on the item
        }

        // Update invoice totals (ensure Invoice model has this method)
        if ($updateInvoice && $this->relationLoaded('invoice') && $this->invoice) {
            // Check if the Invoice model has a method to recalculate its own totals
            if (method_exists($this->invoice, 'calculateAndSaveTotals')) {
                $this->invoice->calculateAndSaveTotals();
            } else {
                // Log a warning if the method doesn't exist on the Invoice model
                \Illuminate\Support\Facades\Log::warning("Invoice model missing calculateAndSaveTotals method. Called from InvoiceItem ID: {$this->id}");
            }
        }
    }
}