// app/Models/Permission.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'module'
    ];

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
