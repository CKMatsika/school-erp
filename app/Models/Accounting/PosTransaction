<?php

namespace App\Models\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_session_id',
        'payment_id',
        'invoice_id',
        'amount',
        'payment_method',
        'reference',
        'card_type',
        'card_last_four',
        'transaction_id',
        'authorization_code',
        'response_code',
        'response_message',
        'status',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the session this transaction belongs to
     */
    public function session()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    /**
     * Get related payment record
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * Get related invoice record
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Get user who created this transaction
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Create transaction for a card payment
     */
    public static function createCardTransaction($sessionId, $paymentId, $invoiceId, $amount, $reference, $cardData, $userId)
    {
        return self::create([
            'pos_session_id' => $sessionId,
            'payment_id' => $paymentId,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'payment_method' => 'card',
            'reference' => $reference,
            'card_type' => $cardData['card_type'] ?? null,
            'card_last_four' => $cardData['last_four'] ?? null,
            'transaction_id' => $cardData['transaction_id'] ?? null,
            'authorization_code' => $cardData['authorization_code'] ?? null,
            'response_code' => $cardData['response_code'] ?? null,
            'response_message' => $cardData['response_message'] ?? null,
            'status' => $cardData['status'] ?? 'completed',
            'created_by' => $userId,
        ]);
    }

    /**
     * Create transaction for a cash payment
     */
    public static function createCashTransaction($sessionId, $paymentId, $invoiceId, $amount, $reference, $userId)
    {
        return self::create([
            'pos_session_id' => $sessionId,
            'payment_id' => $paymentId,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'payment_method' => 'cash',
            'reference' => $reference,
            'status' => 'completed',
            'created_by' => $userId,
        ]);
    }
}