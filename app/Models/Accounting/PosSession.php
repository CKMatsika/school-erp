<?php

namespace App\Models\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PosSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'terminal_id',
        'user_id',
        'opening_time',
        'closing_time',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'difference',
        'notes',
        'status',
        'z_reading_number',
        'card_transaction_count',
        'card_transaction_total',
        'cash_transaction_count',
        'cash_transaction_total',
        'started_by',
        'closed_by',
        'approval_status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'opening_time' => 'datetime',
        'closing_time' => 'datetime',
        'approved_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'card_transaction_total' => 'decimal:2',
        'cash_transaction_total' => 'decimal:2',
    ];

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'terminal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function startedByUser()
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'pos_session_id');
    }

    public function transactions()
    {
        return $this->hasMany(PosTransaction::class, 'pos_session_id');
    }

    public function calculateExpectedBalance()
    {
        $totalPayments = $this->payments()->where('status', 'completed')->sum('amount');
        $this->expected_balance = $this->opening_balance + $totalPayments;
        $this->difference = $this->closing_balance - $this->expected_balance;
        
        // Update transaction counts and totals
        $cardStats = $this->transactions()->where('payment_method', 'card')->get();
        $this->card_transaction_count = $cardStats->count();
        $this->card_transaction_total = $cardStats->sum('amount');
        
        $cashStats = $this->transactions()->where('payment_method', 'cash')->get();
        $this->cash_transaction_count = $cashStats->count();
        $this->cash_transaction_total = $cashStats->sum('amount');
        
        $this->save();
        
        return $this;
    }
    
    /**
     * Start a new POS session
     */
    public static function startSession($terminalId, $userId, $openingBalance, $notes = null)
    {
        // Check if there's already an active session
        $existingSession = self::where('terminal_id', $terminalId)
            ->where('status', 'open')
            ->first();
            
        if ($existingSession) {
            throw new \Exception('This terminal already has an active session.');
        }
        
        // Create new session
        return self::create([
            'terminal_id' => $terminalId,
            'user_id' => $userId,
            'opening_time' => Carbon::now(),
            'opening_balance' => $openingBalance,
            'status' => 'open',
            'notes' => $notes,
            'started_by' => $userId,
        ]);
    }
    
    /**
     * Close the current POS session
     */
    public function closeSession($closingBalance, $notes = null, $closedBy = null)
    {
        if ($this->status !== 'open') {
            throw new \Exception('This session is not open.');
        }
        
        $this->closing_time = Carbon::now();
        $this->closing_balance = $closingBalance;
        $this->notes = $notes ? ($this->notes ? $this->notes . "\n\n" . $notes : $notes) : $this->notes;
        $this->status = 'closed';
        $this->closed_by = $closedBy ?? $this->user_id;
        
        // Calculate expected balance and differences
        $this->calculateExpectedBalance();
        
        return $this;
    }
    
    /**
     * Approve a closed session
     */
    public function approveSession($approvedBy, $notes = null)
    {
        if ($this->status !== 'closed') {
            throw new \Exception('Only closed sessions can be approved.');
        }
        
        $this->approval_status = 'approved';
        $this->approved_by = $approvedBy;
        $this->approved_at = Carbon::now();
        
        if ($notes) {
            $this->notes = $this->notes ? $this->notes . "\n\n" . $notes : $notes;
        }
        
        $this->save();
        
        return $this;
    }
    
    /**
     * Get session duration in minutes
     */
    public function getDurationInMinutes()
    {
        $start = $this->opening_time;
        $end = $this->status === 'open' ? Carbon::now() : $this->closing_time;
        
        return $start->diffInMinutes($end);
    }
    
    /**
     * Check if session has a significant cash discrepancy
     */
    public function hasSignificantDiscrepancy($threshold = 10)
    {
        // If difference is more than $threshold (default 10)
        return abs($this->difference) > $threshold;
    }
    
    /**
     * Generate a Z-reading report
     */
    public function generateZReading()
    {
        if ($this->status === 'open') {
            throw new \Exception('Session must be closed to generate Z-reading.');
        }
        
        // Generate Z-reading number if not already set
        if (!$this->z_reading_number) {
            $this->z_reading_number = 'Z' . Carbon::now()->format('YmdHis') . rand(1000, 9999);
            $this->save();
        }
        
        $report = [
            'session_id' => $this->id,
            'z_reading_number' => $this->z_reading_number,
            'terminal_id' => $this->terminal->terminal_id,
            'terminal_name' => $this->terminal->terminal_name,
            'cashier' => $this->user->name,
            'open_time' => $this->opening_time->format('Y-m-d H:i:s'),
            'close_time' => $this->closing_time->format('Y-m-d H:i:s'),
            'duration' => $this->getDurationInMinutes() . ' minutes',
            'opening_balance' => $this->opening_balance,
            'closing_balance' => $this->closing_balance,
            'expected_balance' => $this->expected_balance,
            'difference' => $this->difference,
            'cash_count' => $this->cash_transaction_count,
            'cash_total' => $this->cash_transaction_total,
            'card_count' => $this->card_transaction_count,
            'card_total' => $this->card_transaction_total,
            'total_transactions' => $this->card_transaction_count + $this->cash_transaction_count,
            'total_amount' => $this->card_transaction_total + $this->cash_transaction_total,
            'has_discrepancy' => $this->hasSignificantDiscrepancy(),
            'transactions' => $this->transactions()->orderBy('created_at')->get()->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'time' => $transaction->created_at->format('H:i:s'),
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount,
                    'method' => $transaction->payment_method,
                ];
            }),
        ];
        
        return $report;
    }
}