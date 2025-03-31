<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'category_id',
        'item_code',
        'brand',
        'model',
        'serial_number',
        'supplier_id',
        'purchase_date',
        'purchase_cost',
        'warranty_expiry',
        'current_value',
        'location_id',
        'condition',
        'status',
        'description',
        'photo',
        'quantity',
        'min_quantity',
        'depreciation_rate',
        'depreciation_method',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
    ];

    /**
     * Get the category that owns the asset.
     */
    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    /**
     * Get the supplier that provides the asset.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get the location that houses the asset.
     */
    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    /**
     * Get the asset allocations for the asset.
     */
    public function allocations()
    {
        return $this->hasMany(AssetAllocation::class);
    }

    /**
     * Get the maintenance records for the asset.
     */
    public function maintenanceRecords()
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    /**
     * Scope a query to only include assets of a given category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to only include assets with a given status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include assets in a given location.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $locationId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Scope a query to only include assets that are low in stock.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowStock($query)
    {
        return $query->whereNotNull('min_quantity')
                    ->whereRaw('quantity <= min_quantity');
    }

    /**
     * Get the current replacement cost of the asset.
     *
     * @return float
     */
    public function getReplacementCostAttribute()
    {
        // Logic to calculate replacement cost based on current market value
        return $this->current_value ?? $this->purchase_cost;
    }

    /**
     * Calculate depreciation for the asset.
     *
     * @param  string  $toDate
     * @return float
     */
    public function calculateDepreciation($toDate = null)
    {
        $toDate = $toDate ? new \DateTime($toDate) : now();
        $purchaseDate = new \DateTime($this->purchase_date);
        
        // Calculate years of ownership
        $years = $purchaseDate->diff($toDate)->y;
        
        // Apply depreciation based on method
        if ($this->depreciation_method === 'straight-line') {
            $totalDepreciation = $this->purchase_cost * ($this->depreciation_rate / 100) * $years;
            return $totalDepreciation > $this->purchase_cost ? $this->purchase_cost : $totalDepreciation;
        } elseif ($this->depreciation_method === 'reducing-balance') {
            $value = $this->purchase_cost;
            for ($i = 0; $i < $years; $i++) {
                $value -= $value * ($this->depreciation_rate / 100);
            }
            return $this->purchase_cost - $value;
        }
        
        return 0; // No depreciation if method is 'none'
    }
}