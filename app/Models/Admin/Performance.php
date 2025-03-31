<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staff_id',
        'evaluation_date',
        'evaluation_period_start',
        'evaluation_period_end',
        'evaluator_id',
        'overall_rating',
        'ratings', // JSON with category ratings
        'strengths',
        'areas_for_improvement',
        'comments',
        'goals',
        'staff_comments',
        'status', // 'draft', 'completed', 'acknowledged'
        'acknowledgement_date',
        'next_evaluation_date',
        'template_id',
        'attachments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'evaluation_date' => 'date',
        'evaluation_period_start' => 'date',
        'evaluation_period_end' => 'date',
        'overall_rating' => 'decimal:1',
        'ratings' => 'array',
        'goals' => 'array',
        'acknowledgement_date' => 'date',
        'next_evaluation_date' => 'date',
        'attachments' => 'array',
    ];

    /**
     * Get the staff member being evaluated.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the staff member who evaluated.
     */
    public function evaluator()
    {
        return $this->belongsTo(Staff::class, 'evaluator_id');
    }

    /**
     * Get the evaluation template used.
     */
    public function template()
    {
        return $this->belongsTo(PerformanceTemplate::class, 'template_id');
    }

    /**
     * Scope a query to only include evaluations for a specific staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $staffId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Scope a query to only include evaluations by a specific evaluator.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $evaluatorId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByEvaluator($query, $evaluatorId)
    {
        return $query->where('evaluator_id', $evaluatorId);
    }

    /**
     * Scope a query to only include evaluations for a specific year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForYear($query, $year)
    {
        return $query->whereYear('evaluation_date', $year);
    }

    /**
     * Scope a query to only include evaluations with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include evaluations with ratings above a threshold.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  float  $rating
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRatingAbove($query, $rating)
    {
        return $query->where('overall_rating', '>=', $rating);
    }

    /**
     * Scope a query to only include evaluations with ratings below a threshold.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  float  $rating
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRatingBelow($query, $rating)
    {
        return $query->where('overall_rating', '<=', $rating);
    }

    /**
     * Check if evaluation is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === 'completed' || $this->status === 'acknowledged';
    }

    /**
     * Check if evaluation is acknowledged by staff.
     *
     * @return bool
     */
    public function isAcknowledged()
    {
        return $this->status === 'acknowledged';
    }

    /**
     * Calculate overall rating based on category ratings.
     *
     * @return float
     */
    public function calculateOverallRating()
    {
        if (empty($this->ratings)) {
            return 0;
        }
        
        $total = 0;
        $count = 0;
        
        foreach ($this->ratings as $category => $rating) {
            $total += $rating['score'] * ($rating['weight'] ?? 1);
            $count += ($rating['weight'] ?? 1);
        }
        
        $overallRating = $count > 0 ? round($total / $count, 1) : 0;
        $this->overall_rating = $overallRating;
        $this->save();
        
        return $overallRating;
    }

    /**
     * Complete the evaluation.
     *
     * @param  string|null  $comments
     * @return bool
     */
    public function complete($comments = null)
    {
        if ($this->status !== 'draft') {
            return false;
        }
        
        $this->status = 'completed';
        
        if ($comments) {
            $this->comments = ($this->comments ? $this->comments . "\n" : '') . $comments;
        }
        
        // Calculate overall rating if not already done
        if (!$this->overall_rating) {
            $this->calculateOverallRating();
        }
        
        return $this->save();
    }

    /**
     * Acknowledge the evaluation by staff.
     *
     * @param  string|null  $staffComments
     * @return bool
     */
    public function acknowledge($staffComments = null)
    {
        if ($this->status !== 'completed') {
            return false;
        }
        
        $this->status = 'acknowledged';
        $this->acknowledgement_date = now();
        
        if ($staffComments) {
            $this->staff_comments = $staffComments;
        }
        
        return $this->save();
    }

    /**
     * Add a goal to the evaluation.
     *
     * @param  array  $goalData
     * @return bool
     */
    public function addGoal($goalData)
    {
        $goals = $this->goals ?? [];
        
        $goals[] = [
            'description' => $goalData['description'],
            'target_date' => $goalData['target_date'],
            'priority' => $goalData['priority'] ?? 'medium',
            'metrics' => $goalData['metrics'] ?? null,
            'status' => 'pending',
        ];
        
        $this->goals = $goals;
        
        return $this->save();
    }

    /**
     * Update a goal status.
     *
     * @param  int  $goalIndex
     * @param  string  $status
     * @param  string|null  $notes
     * @return bool
     */
    public function updateGoalStatus($goalIndex, $status, $notes = null)
    {
        if (!isset($this->goals[$goalIndex])) {
            return false;
        }
        
        $this->goals[$goalIndex]['status'] = $status;
        
        if ($notes) {
            $this->goals[$goalIndex]['notes'] = ($this->goals[$goalIndex]['notes'] ?? '') . "\n" . $notes;
        }
        
        return $this->save();
    }

    /**
     * Get rating text based on numeric rating.
     *
     * @param  float  $rating
     * @return string
     */
    public function getRatingText($rating = null)
    {
        $rating = $rating ?? $this->overall_rating;
        
        if ($rating >= 4.5) {
            return 'Outstanding';
        } elseif ($rating >= 3.5) {
            return 'Exceeds Expectations';
        } elseif ($rating >= 2.5) {
            return 'Meets Expectations';
        } elseif ($rating >= 1.5) {
            return 'Needs Improvement';
        } else {
            return 'Unsatisfactory';
        }
    }

    /**
     * Get the rating badge class based on numeric rating.
     *
     * @param  float  $rating
     * @return string
     */
    public function getRatingBadgeClass($rating = null)
    {
        $rating = $rating ?? $this->overall_rating;
        
        if ($rating >= 4.5) {
            return 'badge-success';
        } elseif ($rating >= 3.5) {
            return 'badge-info';
        } elseif ($rating >= 2.5) {
            return 'badge-primary';
        } elseif ($rating >= 1.5) {
            return 'badge-warning';
        } else {
            return 'badge-danger';
        }
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'draft':
                return 'badge-secondary';
            case 'completed':
                return 'badge-warning';
            case 'acknowledged':
                return 'badge-success';
            default:
                return 'badge-dark';
        }
    }
}