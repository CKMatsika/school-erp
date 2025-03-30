<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'interest_rate',
        'loan_date',
        'loan_end_date',
        'repayment_term',
        'repayment_type',
        'purpose',
        'status',
        'approved_date',
        'activation_date',
        'completion_date',
        'rejection_reason'
    ];

    protected $dates = [
        'loan_date',
        'loan_end_date',
        'approved_date',
        'activation_date',
        'completion_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}