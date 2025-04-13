<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PosSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_terminal_id', // This should match your migration/table field
        'terminal_id',     // Add this if your table has 'terminal_id' instead
        'opening_balance',
        'cashier_id',
        'started_at',
        'status',
        'school_id',
        'started_by',
        'closing_balance',
        'cash_counted',
        'expected_closing_balance',
        'discrepancy',
        'notes',
        'ended_at',
        'ended_by'
    ];

    // Make sure this relationship matches your database structure
    public function terminal()
    {
        // Use the same field name your database uses
        return $this->belongsTo(PosTerminal::class, 'terminal_id');
        // Or if your db uses pos_terminal_id, use this instead:
        // return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function startedBy()
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function endedBy()
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    public function transactions()
    {
        return $this->hasMany(PosTransaction::class);
    }
}