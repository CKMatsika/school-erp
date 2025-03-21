<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'name',
        'description',
        'quantity',
        'price',
        'discount',
        'tax_rate_id',
        'tax_amount',
        'total',
        'account_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function calculateTotals()
    {
        $subtotal = $this->price * $this->quantity;
        
        // Apply discount
        $discountedTotal = $subtotal - $this->discount;
        
        // Calculate tax
        if ($this->tax_rate_id) {
            $this->tax_amount = ($discountedTotal * $this->taxRate->rate) / 100;
        } else {
            $this->tax_amount = 0;
        }
        
        $this->total = $discountedTotal + $this->tax_amount;
        $this->save();
        
        // Update invoice totals
        $this->invoice->calculateTotals();
    }
}