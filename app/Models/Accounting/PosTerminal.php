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
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}