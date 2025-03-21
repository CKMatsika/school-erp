<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creditor extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'contact_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'paid_amount',
        'balance',
        'days_overdue',
        'status',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function contact()
    {
        return $this->belongsTo(AccountingContact::class, 'contact_id');
    }

    public function updateStatus()
    {
        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0 && $this->paid_amount < $this->amount) {
            $this->status = 'partial';
        } elseif ($this->days_overdue > 0) {
            $this->status = 'overdue';
        } else {
            $this->status = 'current';
        }
        $this->save();
    }
}