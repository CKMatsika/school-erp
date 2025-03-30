<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AssetAllocation extends Model
{
    protected $fillable = [
        'asset_id', 
        'allocated_to', 
        'allocation_date', 
        'return_date', 
        'status'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function allocatedTo()
    {
        return $this->belongsTo(User::class, 'allocated_to');
    }
}