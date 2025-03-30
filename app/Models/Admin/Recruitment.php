<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Recruitment extends Model
{
    protected $fillable = [
        'job_title', 
        'department', 
        'vacancy_count', 
        'description', 
        'requirements', 
        'salary_range', 
        'posting_date', 
        'closing_date', 
        'status'
    ];
}
