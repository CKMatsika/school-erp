<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgeAnalysisSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'is_student_specific',
        'current_days',
        'period_1_days',
        'period_2_days',
        'period_3_days',
        'period_1_label',
        'period_2_label',
        'period_3_label',
        'period_4_label',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_student_specific' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}