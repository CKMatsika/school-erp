<?php

namespace App\Models\Accounting;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'is_cash',
        'is_bank',
        'is_pos',
        'is_active',
    ];

    protected $casts = [
        'is_cash' => 'boolean',
        'is_bank' => 'boolean',
        'is_pos' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}