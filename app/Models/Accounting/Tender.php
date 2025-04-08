<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Tender extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_number',
        'title',
        'description',
        'publication_date',
        'closing_date',
        'status',
        'estimated_value',
        'awarded_to',
        'award_date',
        'award_notes',
        'created_by',
        'academic_year_id',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'closing_date' => 'date',
        'award_date' => 'date',
        'estimated_value' => 'decimal:2',
    ];

    /**
     * Get the awarded supplier
     */
    public function awardedSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'awarded_to');
    }

    /**
     * Get the user who created this tender
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the academic year
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get all bids for this tender
     */
    public function bids(): HasMany
    {
        return $this->hasMany(TenderBid::class);
    }

    /**
     * Get the contracts created from this tender
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(ProcurementContract::class);
    }

    /**
     * Check if the tender is active
     */
    public function isActive(): bool
    {
        return $this->status === 'published' && now()->lte($this->closing_date);
    }

    /**
     * Check if the tender is closed for bids
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed' || $this->status === 'awarded' || $this->status === 'cancelled' || ($this->status === 'published' && now()->gt($this->closing_date));
    }

    /**
     * Check if the tender can be awarded
     */
    public function canBeAwarded(): bool
    {
        return $this->isClosed() && $this->status !== 'awarded' && $this->status !== 'cancelled' && $this->bids()->count() > 0;
    }
}