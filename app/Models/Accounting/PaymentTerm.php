<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTerm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'days',
        'is_end_of_month',
        'description',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'days' => 'integer',
        'is_end_of_month' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function calculateDueDate($invoiceDate)
    {
        $dueDate = clone $invoiceDate;
        $dueDate->addDays($this->days);
        
        if ($this->is_end_of_month) {
            $dueDate->endOfMonth();
        }
        
        return $dueDate;
    }
}