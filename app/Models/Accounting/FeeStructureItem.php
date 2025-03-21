<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructureItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_structure_id',
        'fee_category_id',
        'grade_level',
        'amount',
        'frequency',
        'due_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }
}