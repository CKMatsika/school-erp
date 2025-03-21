<?php

namespace App\Models\Accounting;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'rate',
        'is_compound',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_compound' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}