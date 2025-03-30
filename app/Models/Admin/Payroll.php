<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'user_id', 
        'pay_period', 
        'basic_salary', 
        'allowances', 
        'deductions', 
        'net_salary', 
        'payment_date', 
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
