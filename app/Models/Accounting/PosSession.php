<?php

namespace App\Models\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'opening_time' => 'datetime',
        'closing_time' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'terminal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'pos_session_id');
    }

    public function calculateExpectedBalance()
    {
        $totalPayments = $this->payments()->where('status', 'completed')->sum('amount');
        $this->expected_balance = $this->opening_balance + $totalPayments;
        $this->difference = $this->closing_balance - $this->expected_balance;
        $this->save();
    }
}