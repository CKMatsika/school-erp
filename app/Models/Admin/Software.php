<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    protected $fillable = [
        'name', 
        'version', 
        'license_key', 
        'purchase_date', 
        'renewal_date', 
        'status'
    ];
}