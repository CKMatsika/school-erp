<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class ITEquipment extends Model
{
    protected $fillable = [
        'name', 
        'model', 
        'serial_number', 
        'purchase_date', 
        'warranty_expiry', 
        'status'
    ];
}