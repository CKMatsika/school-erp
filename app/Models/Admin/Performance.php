<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    protected $fillable = [
        'user_id', 
        'evaluation_period', 
        'performance_metrics', 
        'overall_rating', 
        'comments', 
        'reviewer_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}