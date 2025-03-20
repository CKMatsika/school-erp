<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'is_system', 'school_id'
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    // Relationships
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}