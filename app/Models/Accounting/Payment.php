<?php

namespace App\Models\Accounting;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'contact_id',
        'invoice_id',
        'payment_number',
        'payment_method_id',
        'reference',
        'payment_date',
        'amount',
        'notes',
        'status',
        'account_id',
        'pos_terminal_id',
        'transaction_id',
        'is_pos_transaction',
        'payment_receipt',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'is_pos_transaction' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function posTerminal()
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journal()
    {
        return $this->hasOne(Journal::class, 'document_id')->where('document_type', 'payment');
    }

    public function posSession()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }
}