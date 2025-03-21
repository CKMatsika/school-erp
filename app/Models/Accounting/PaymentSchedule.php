<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_plan_id',
        'installment_number',
        'due_date',
        'amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'payment_id',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function recordPayment($paymentId, $amount, $paymentDate)
    {
        $this->payment_id = $paymentId;
        $this->paid_amount += $amount;
        $this->remaining_amount = $this->amount - $this->paid_amount;
        $this->payment_date = $paymentDate;
        
        if ($this->remaining_amount <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date < now()) {
            $this->status = 'overdue';
        }
        
        $this->save();
        
        // Update parent payment plan
        $this->paymentPlan->updateStatus();
    }
}