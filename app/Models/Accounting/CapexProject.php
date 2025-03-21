<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CapexProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'budget_id',
        'name',
        'project_code',
        'description',
        'start_date',
        'expected_completion_date',
        'actual_completion_date',
        'total_budget',
        'total_spent',
        'remaining_budget',
        'status',
        'project_manager_id',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'total_budget' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function budgetItems()
    {
        return $this->hasMany(ProjectBudgetItem::class);
    }

    public function calculateRemainingBudget()
    {
        $this->total_spent = $this->budgetItems()->sum('actual_amount');
        $this->remaining_budget = $this->total_budget - $this->total_spent;
        $this->save();
    }
}