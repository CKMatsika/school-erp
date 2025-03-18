// app/Models/Module.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'key', 'description', 'is_active', 'is_core', 'dependencies', 'version'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'dependencies' => 'array',
    ];

    // Relationships
    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_modules')
            ->withPivot('is_active', 'settings')
            ->withTimestamps();
    }

    // Get permissions related to this module
    public function permissions()
    {
        return Permission::where('module', $this->key)->get();
    }
}