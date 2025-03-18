// app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'school_id',
        'user_type',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    // Check if user has role
    public function hasRole($roleSlug)
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    // Check if user has permission
    public function hasPermission($permissionSlug)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('slug', $permissionSlug)->exists()) {
                return true;
            }
        }
        
        return false;
    }
}