<?php

namespace App\Models\Accounting;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'is_mandatory',
        'is_recurring',
        'account_id',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function feeStructureItems()
    {
        return $this->hasMany(FeeStructureItem::class);
    }
}