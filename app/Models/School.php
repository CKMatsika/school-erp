// app/Models/School.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'address', 'city', 'state', 'country',
        'postal_code', 'phone', 'email', 'website', 'logo',
        'is_active', 'subscription_start', 'subscription_end'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'school_modules')
            ->withPivot('is_active', 'settings')
            ->withTimestamps();
    }

    // Check if subscription is active
    public function isSubscriptionActive()
    {
        if (!$this->subscription_end) {
            return true; // No end date means unlimited
        }
        
        return $this->subscription_end->isFuture();
    }
}