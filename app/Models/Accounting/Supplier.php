<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_number',
        'registration_number',
        'website',
        'notes',
        'is_active',
        'payment_terms',
        'account_number',
        'bank_name',
        'bank_branch_code',
        'bank_swift_code',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all purchase orders for this supplier
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get all contracts for this supplier
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(ProcurementContract::class);
    }

    /**
     * Get all tender bids from this supplier
     */
    public function tenderBids(): HasMany
    {
        return $this->hasMany(TenderBid::class);
    }

    /**
     * Get the full address attribute
     */
    public function getFullAddressAttribute()
    {
        $parts = [];
        
        if ($this->address) $parts[] = $this->address;
        if ($this->city) $parts[] = $this->city;
        if ($this->state) $parts[] = $this->state;
        if ($this->postal_code) $parts[] = $this->postal_code;
        if ($this->country) $parts[] = $this->country;
        
        return implode(', ', $parts);
    }
}