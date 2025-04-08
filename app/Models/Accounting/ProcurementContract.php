<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ProcurementContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_number',
        'title',
        'description',
        'supplier_id',
        'tender_id',
        'start_date',
        'end_date',
        'contract_value',
        'status',
        'document_path',
        'terms_and_conditions',
        'created_by',
        'academic_year_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:2',
    ];

    /**
     * Get the supplier for this contract
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the tender that this contract is based on
     */
    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    /**
     * Get the user who created this contract
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
     * Check if the contract is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && now()->between($this->start_date, $this->end_date);
    }

    /**
     * Check if the contract is expired
     */
    public function isExpired(): bool
    {
        return now()->gt($this->end_date);
    }

    /**
     * Calculate the remaining days
     */
    public function getRemainingDaysAttribute()
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->end_date, false);
    }
}