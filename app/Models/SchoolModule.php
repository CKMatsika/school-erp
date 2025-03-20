<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SchoolModule extends Pivot
{
    use HasFactory;

    protected $table = 'school_modules';

    protected $fillable = [
        'school_id', 'module_id', 'is_active', 'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}