<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'date', 
        'amount', 
        'category', 
        'description', 
        'payment_method', 
        'status'
    ];
}