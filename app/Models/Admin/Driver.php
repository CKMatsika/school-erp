<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name', 
        'contact_number', 
        'license_number', 
        'license_expiry', 
        'status'
    ];

    public function routes()
    {
        return $this->hasMany(Route::class);
    }
}