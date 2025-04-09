<?php

namespace App\Models\Accounting;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTerminal extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'terminal_name',
        'terminal_id',
        'location',
        'default_payment_method_id',
        'cashier_user_id',
        'is_active',
        'payment_processor',
        'api_key',
        'api_secret',
        'merchant_id',
        'device_model',
        'serial_number',
        'firmware_version',
        'connection_type',
        'last_maintenance_date',
        'maintenance_notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_maintenance_date' => 'date',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function defaultPaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'default_payment_method_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_user_id');
    }

    public function sessions()
    {
        return $this->hasMany(PosSession::class, 'terminal_id');
    }

    public function getCurrentSession()
    {
        return $this->sessions()->where('status', 'open')->latest()->first();
    }
    
    public function transactions()
    {
        return $this->hasManyThrough(
            PosTransaction::class,
            PosSession::class,
            'terminal_id',
            'pos_session_id'
        );
    }
    
    /**
     * Check if terminal is ready for card transactions
     */
    public function isCardReady()
    {
        return $this->is_active && 
               $this->payment_processor && 
               ($this->api_key || $this->merchant_id);
    }
    
    /**
     * Get terminal transaction statistics for a given period
     */
    public function getTransactionStats($startDate = null, $endDate = null)
    {
        $query = $this->transactions();
        
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        
        $stats = [
            'total_transactions' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'card_transactions' => $query->where('payment_method', 'card')->count(),
            'card_amount' => $query->where('payment_method', 'card')->sum('amount'),
            'cash_transactions' => $query->where('payment_method', 'cash')->count(),
            'cash_amount' => $query->where('payment_method', 'cash')->sum('amount'),
        ];
        
        return $stats;
    }
}