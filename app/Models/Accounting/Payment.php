<?php

namespace App\Models\Accounting;

use App\Models\School;
use App\Models\User;
use App\Models\Student;
use App\Models\PaymentSchedule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'contact_id',
        'student_id',
        'invoice_id',
        'payment_number',
        'receipt_number',
        'payment_method_id',
        'reference',
        'payment_date',
        'amount',
        'notes',
        'status',
        'account_id',
        'pos_terminal_id',
        'pos_session_id',
        'cashier_name',
        'receipt_type',
        'is_printed',
        'printed_at',
        'customer_copy_given',
        'transaction_id',
        'is_pos_transaction',
        'payment_receipt',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'is_pos_transaction' => 'boolean',
        'is_printed' => 'boolean',
        'printed_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
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

    public function posSession()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journal()
    {
        return $this->hasOne(Journal::class, 'document_id')->where('document_type', 'payment');
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function applyToInvoice()
    {
        if ($this->invoice_id && $this->status === 'completed') {
            $invoice = $this->invoice;
            $invoice->amount_paid += $this->amount;
            
            if ($invoice->amount_paid >= $invoice->total) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partial';
            }
            
            $invoice->save();
            
            // Update student debt record if exists
            if ($invoice->student_id && $invoice->studentDebt) {
                $invoice->studentDebt->update([
                    'paid_amount' => $invoice->amount_paid,
                    'balance' => $invoice->total - $invoice->amount_paid,
                    'status' => $invoice->status === 'paid' ? 'paid' : 'partial'
                ]);
            }
        }
    }

    public function applyToPaymentSchedule()
    {
        foreach ($this->paymentSchedules as $schedule) {
            $schedule->recordPayment($this->id, $this->amount, $this->payment_date);
        }
    }

    // Generate receipt number
    public function generateReceiptNumber()
    {
        if (empty($this->receipt_number)) {
            $prefix = 'RCPT';
            $year = date('Y');
            $month = date('m');
            $lastReceipt = self::where('school_id', $this->school_id)
                ->whereNotNull('receipt_number')
                ->where('receipt_number', 'like', "{$prefix}-{$year}{$month}%")
                ->orderBy('receipt_number', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastReceipt) {
                $parts = explode('-', $lastReceipt->receipt_number);
                if (count($parts) > 1) {
                    $lastNumber = substr($parts[1], 6); // Extract number after YYYYMM
                    $nextNumber = intval($lastNumber) + 1;
                }
            }

            $this->receipt_number = $prefix . '-' . $year . $month . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $this->save();
        }
        
        return $this->receipt_number;
    }
}