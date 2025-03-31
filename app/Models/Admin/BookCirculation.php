<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCirculation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'book_id',
        'member_id',
        'issue_date',
        'due_date',
        'return_date',
        'fine_amount',
        'fine_paid',
        'fine_paid_date',
        'condition_on_issue',
        'condition_on_return',
        'remarks',
        'issued_by',
        'received_by',
        'status', // 'issued', 'returned', 'overdue', 'lost', 'damaged'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_paid_date' => 'date',
        'fine_amount' => 'decimal:2',
        'fine_paid' => 'boolean',
    ];

    /**
     * Get the book that was circulated.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the library member who borrowed the book.
     */
    public function member()
    {
        return $this->belongsTo(LibraryMember::class);
    }

    /**
     * Get the staff member who issued the book.
     */
    public function issuedBy()
    {
        return $this->belongsTo(Staff::class, 'issued_by');
    }

    /**
     * Get the staff member who received the book return.
     */
    public function receivedBy()
    {
        return $this->belongsTo(Staff::class, 'received_by');
    }

    /**
     * Scope a query to only include current circulations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query)
    {
        return $query->whereNull('return_date');
    }

    /**
     * Scope a query to only include overdue circulations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->whereNull('return_date')
                    ->where('due_date', '<', now());
    }

    /**
     * Scope a query to only include circulations by member.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $memberId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Scope a query to only include circulations with unpaid fines.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithUnpaidFines($query)
    {
        return $query->where('fine_amount', '>', 0)
                    ->where('fine_paid', false);
    }

    /**
     * Calculate fine amount for overdue book.
     *
     * @param  float  $fineRate  Daily fine rate
     * @param  int  $gracePeriod  Grace period in days
     * @return float
     */
    public function calculateFine($fineRate = 1.0, $gracePeriod = 0)
    {
        if ($this->return_date) {
            // Already returned, calculate based on actual return date
            $daysLate = max(0, $this->return_date->diffInDays($this->due_date) - $gracePeriod);
        } else {
            // Not yet returned, calculate based on current date
            $daysLate = max(0, now()->diffInDays($this->due_date) - $gracePeriod);
        }
        
        return $daysLate * $fineRate;
    }

    /**
     * Check if circulation is overdue.
     *
     * @return bool
     */
    public function isOverdue()
    {
        return !$this->return_date && $this->due_date < now();
    }

    /**
     * Get days overdue.
     *
     * @return int
     */
    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return now()->diffInDays($this->due_date);
    }

    /**
     * Process book return.
     *
     * @param  array  $returnData
     * @return bool
     */
    public function processReturn($returnData)
    {
        $this->return_date = $returnData['return_date'] ?? now();
        $this->condition_on_return = $returnData['condition'] ?? 'good';
        $this->received_by = $returnData['received_by'] ?? null;
        $this->remarks = isset($returnData['remarks']) ? $this->remarks . "\n" . $returnData['remarks'] : $this->remarks;
        
        // Calculate fine if returned late
        $fineRate = $returnData['fine_rate'] ?? 1.0;
        $gracePeriod = $returnData['grace_period'] ?? 0;
        
        if ($this->return_date > $this->due_date) {
            $this->fine_amount = $this->calculateFine($fineRate, $gracePeriod);
        }
        
        // Set status based on condition
        if ($this->condition_on_return === 'lost') {
            $this->status = 'lost';
        } elseif ($this->condition_on_return === 'damaged') {
            $this->status = 'damaged';
        } else {
            $this->status = 'returned';
        }
        
        // Add additional fine for damaged or lost books if specified
        if (isset($returnData['additional_fine']) && ($this->status === 'damaged' || $this->status === 'lost')) {
            $this->fine_amount += $returnData['additional_fine'];
        }
        
        // Update book available quantity
        if ($this->status === 'returned') {
            $this->book->updateAvailableQuantity(1, 'return');
        }
        
        return $this->save();
    }

    /**
     * Mark fine as paid.
     *
     * @