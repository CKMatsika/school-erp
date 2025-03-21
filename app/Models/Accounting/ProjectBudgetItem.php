<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectBudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'capex_project_id',
        'name',
        'category',
        'description',
        'budgeted_amount',
        'actual_amount',
        'variance',
        'quantity',
        'unit_price',
        'supplier',
        'expected_purchase_date',
        'actual_purchase_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'variance' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'expected_purchase_date' => 'date',
        'actual_purchase_date' => 'date',
    ];

    public function capexProject()
    {
        return $this->belongsTo(CapexProject::class);
    }
}