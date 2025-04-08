<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderBid extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_id',
        'supplier_id',
        'bid_date',
        'bid_amount',
        'technical_score',
        'financial_score',
        'total_score',
        'is_compliant',
        'notes',
        'document_path',
    ];

    protected $casts = [
        'bid_date' => 'date',
        'bid_amount' => 'decimal:2',
        'technical_score' => 'integer',
        'financial_score' => 'integer',
        'total_score' => 'integer',
        'is_compliant' => 'boolean',
    ];

    /**
     * Get the tender that this bid belongs to
     */
    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    /**
     * Get the supplier who submitted this bid
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Calculate the financial score based on the lowest bid
     */
    public static function calculateFinancialScores($tenderId)
    {
        $bids = self::where('tender_id', $tenderId)
            ->where('is_compliant', true)
            ->get();
            
        if ($bids->isEmpty()) {
            return;
        }
        
        $lowestBid = $bids->min('bid_amount');
        
        foreach ($bids as $bid) {
            if ($bid->bid_amount > 0) {
                $bid->financial_score = min(40, round(($lowestBid / $bid->bid_amount) * 40));
                $bid->total_score = ($bid->technical_score ?? 0) + $bid->financial_score;
                $bid->save();
            }
        }
    }
}