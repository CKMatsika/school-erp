<?php

namespace App\Models\Accounting;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'contact_id',
        'invoice_number',
        'reference',
        'issue_date',
        'due_date',
        'notes',
        'subtotal',
        'tax_total',
        'discount_total',
        'total',
        'amount_paid',
        'status',
        'type',
        'created_by',
        'fee_schedule_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journal()
    {
        return $this->hasOne(Journal::class, 'document_id')->where('document_type', 'invoice');
    }

    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        $this->discount_total = $this->items->sum(function ($item) {
            return $item->discount;
        });
        
        $this->tax_total = $this->items->sum('tax_amount');
        
        $this->total = $this->subtotal - $this->discount_total + $this->tax_total;
        
        $this->amount_paid = $this->payments->where('status', 'completed')->sum('amount');
        
        if ($this->amount_paid >= $this->total) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partial';
        }
        
        $this->save();
    }

    public function getBalance()
    {
        return $this->total - $this->amount_paid;
    }
}