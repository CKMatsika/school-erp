<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'type',
        'term',
        'fiscal_year',
        'start_date',
        'end_date',
        'total_amount',
        'description',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function capexProjects()
    {
        return $this->hasMany(CapexProject::class);
    }

    public function getTotalBudgetedAttribute()
    {
        return $this->budgetItems()->sum('budgeted_amount');
    }

    public function getTotalActualAttribute()
    {
        return $this->budgetItems()->sum('actual_amount');
    }

    public function getTotalVarianceAttribute()
    {
        return $this->budgetItems()->sum('variance');
    }
}