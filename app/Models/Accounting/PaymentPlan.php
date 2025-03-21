<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'invoice_id',
        'name',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'number_of_installments',
        'start_date',
        'end_date',
        'interval',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function generateSchedule()
    {
        // Logic to generate payment schedule based on the plan
        $installmentAmount = round($this->total_amount / $this->number_of_installments, 2);
        $dueDate = clone $this->start_date;
        
        for ($i = 1; $i <= $this->number_of_installments; $i++) {
            // Adjust last installment to account for rounding differences
            if ($i == $this->number_of_installments) {
                $amount = $this->total_amount - ($installmentAmount * ($this->number_of_installments - 1));
            } else {
                $amount = $installmentAmount;
            }
            
            $this->paymentSchedules()->create([
                'installment_number' => $i,
                'due_date' => clone $dueDate,
                'amount' => $amount,
                'remaining_amount' => $amount,
                'status' => 'pending',
            ]);
            
            // Advance due date based on interval
            switch ($this->interval) {
                case 'daily':
                    $dueDate->addDay();
                    break;
                case 'weekly':
                    $dueDate->addWeek();
                    break;
                case 'biweekly':
                    $dueDate->addWeeks(2);
                    break;
                case 'monthly':
                    $dueDate->addMonth();
                    break;
                case 'quarterly':
                    $dueDate->addMonths(3);
                    break;
            }
        }
    }

    public function updateStatus()
    {
        $this->paid_amount = $this->paymentSchedules()->sum('paid_amount');
        $this->remaining_amount = $this->total_amount - $this->paid_amount;
        
        if ($this->remaining_amount <= 0) {
            $this->status = 'completed';
        } elseif ($this->status == 'draft' && $this->approved_by) {
            $this->status = 'active';
        }
        
        $this->save();
    }
}