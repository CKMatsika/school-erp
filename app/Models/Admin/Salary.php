<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = [
        'user_id', 
        'salary_grade', 
        'basic_salary', 
        'allowances', 
        'deductions', 
        'effective_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}