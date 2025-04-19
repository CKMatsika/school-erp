<?php

namespace App\Models\Accounting;

// Add missing relationship imports for type hinting
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// Existing Model Imports
use App\Models\School;
use App\Models\User;
use App\Models\Student; // Assuming this is App\Models\Student\Student or similar
use App\Models\StudentDebt; // Assuming this is App\Models\Student\StudentDebt or similar
use App\Models\PaymentPlan; // Assuming this is App\Models\Student\PaymentPlan or similar
use App\Models\Accounting\Payment; // Assuming this is App\Models\Accounting\Payment

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Specify if not the default 'invoices'
     * @var string
     */
    // protected $table = 'invoices'; // Uncomment and set if your table name is different

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'contact_id',
        'student_id', // Added based on relationships below
        'invoice_number',
        'reference',
        'issue_date',
        'due_date',
        'notes',
        'terms', // Added based on controller usage
        'subtotal',
        // 'tax_total', // Replaced by tax_amount based on controller logic
        'tax_amount', // Renamed based on controller logic
        // 'discount_total', // Replaced by discount_amount based on controller logic
        'discount_amount', // Renamed based on controller logic
        'total',
        'amount_paid',
        'status',
        // 'type', // Renamed to invoice_type based on controller logic
        'invoice_type', // Renamed based on controller logic
        'created_by',
        'fee_schedule_id', // Added based on previous context, remove if not needed
        'journal_id', // Added as journal relationship exists
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2', // Renamed from tax_total
        'discount_amount' => 'decimal:2', // Renamed from discount_total
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    /**
     * Get the school that owns the invoice.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the contact associated with the invoice.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the student associated with the invoice (if applicable).
     */
    public function student(): BelongsTo
    {
        // Consider making this nullable if not every invoice has a student
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the items associated with the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the payments recorded against the invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the user who created the invoice.
     * <----- RENAMED FROM creator() to createdBy() ----->
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the journal entry associated with this invoice.
     * Updated relationship to reflect controller's usage and potential journal_id field
     */
    public function journal(): BelongsTo // Changed from HasOne based on controller logic
    {
        // Assumes you have a 'journal_id' foreign key on the 'invoices' table
        // If the relationship is defined the other way (journal has document_id/type),
        // keep your original hasOne logic but ensure it works with controller createJournalForInvoice
        return $this->belongsTo(Journal::class);

        // --- OR --- Keep original if Journal has document_id / document_type
        // return $this->hasOne(Journal::class, 'document_id')->where('document_type', self::class); // Use self::class for morph map safety
    }

    /**
     * Get the student debt record associated with this invoice (if applicable).
     */
    public function studentDebt(): HasOne // Assuming one debt record per invoice
    {
        return $this->hasOne(StudentDebt::class);
    }

    /**
     * Get the payment plan associated with this invoice (if applicable).
     */
    public function paymentPlan(): HasOne // Assuming one payment plan per invoice
    {
        return $this->hasOne(PaymentPlan::class);
    }

    /**
     * Calculate and save totals based on items and payments.
     * Note: Directly calling save() might trigger observers unexpectedly.
     * Consider returning calculated values and letting controller handle saving,
     * or use saveQuietly() if no events should be fired.
     * ALIGNED fields with controller usage (unit_price, tax_amount, discount_amount).
     */
    public function calculateAndSaveTotals(bool $save = true): void
    {
        // Reload relationships to ensure fresh data if items/payments might have changed
        $this->loadMissing(['items', 'payments']);

        $calculatedSubtotal = 0;
        $calculatedDiscount = 0;
        $calculatedTax = 0;

        foreach($this->items as $item) {
            $lineSubtotal = (float)($item->quantity ?? 0) * (float)($item->unit_price ?? 0); // Use unit_price
            $lineDiscount = (float)($item->discount ?? 0); // Item-level discount amount
            $lineTax = (float)($item->tax_amount ?? 0); // Use tax_amount from item

            $calculatedSubtotal += $lineSubtotal;
            $calculatedDiscount += $lineDiscount; // Sum of item-level discounts
            $calculatedTax += $lineTax;
        }

        $this->subtotal = $calculatedSubtotal;
        $this->discount_amount = $calculatedDiscount; // Use discount_amount
        $this->tax_amount = $calculatedTax; // Use tax_amount
        $this->total = $this->subtotal - $this->discount_amount + $this->tax_amount;

        // Calculate amount paid based on completed payments
        $this->amount_paid = $this->payments() // Use relationship method for potential query constraints
                                 ->where('status', 'completed') // Example status check
                                 ->sum('amount');

        // Update status based on calculated totals and payment
        $balance = $this->total - $this->amount_paid;
        $tolerance = 0.01; // Tolerance for floating point comparisons

        if ($balance <= $tolerance) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > $tolerance) {
            $this->status = 'partial';
        } elseif ($this->due_date < now()->startOfDay()) { // Check if past due date
             $this->status = 'overdue'; // Set overdue only if past due and not paid/partial
        } else {
             // If not paid, not partial, and not overdue, it's likely draft or sent
             // Keep existing status unless logic dictates otherwise (e.g., reset to 'draft'?)
             // $this->status = 'draft'; // Or keep current status
        }


        if ($save) {
            // Use saveQuietly to prevent triggering 'updated' events if calculateTotals
            // is called FROM within an 'updating' or 'saving' event/observer.
             $this->saveQuietly();
            // Otherwise, just use save():
            // $this->save();
        }
    }


    /**
     * Get the outstanding balance on the invoice.
     *
     * @return float
     */
    public function getBalance(): float
    {
        // Ensure total and amount_paid are treated as numbers
        return (float) $this->total - (float) $this->amount_paid;
    }

    /**
     * Check if the invoice is overdue.
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        // Check if due date is past AND it's not fully paid
        return $this->due_date // Ensure due_date is set
               && $this->due_date < now()->startOfDay() // Compare with start of today
               && !in_array($this->status, ['paid', 'void']); // Consider 'void' status too
    }

    /**
     * Get the number of days the invoice is overdue.
     * Accessor: $invoice->days_overdue
     *
     * @return int
     */
    public function getDaysOverdueAttribute(): int
    {
        if ($this->isOverdue() && $this->due_date) {
            return now()->startOfDay()->diffInDays($this->due_date->startOfDay());
        }
        return 0;
    }

    /**
     * Create or update the related StudentDebt record.
     *
     * @return StudentDebt|null
     */
    public function createOrUpdateStudentDebt(): ?StudentDebt
    {
        if ($this->student_id) {
            $balance = $this->getBalance();
            $isOverdue = $this->isOverdue();
            $status = 'current'; // Default

            if ($this->status === 'paid') {
                 $status = 'paid';
            } elseif ($this->status === 'partial') {
                 $status = 'partial';
            } elseif ($isOverdue) {
                 $status = 'overdue';
            }

            // Only create/update debt if there's a balance or if it was previously recorded
            if ($balance > 0.01 || StudentDebt::where('invoice_id', $this->id)->exists()) {
                return StudentDebt::updateOrCreate(
                    ['invoice_id' => $this->id], // Unique identifier for the debt related to this invoice
                    [
                        'school_id' => $this->school_id,
                        'student_id' => $this->student_id,
                        'due_date' => $this->due_date,
                        'amount' => (float) $this->total,
                        'paid_amount' => (float) $this->amount_paid,
                        'balance' => $balance,
                        'days_overdue' => $this->days_overdue, // Use the accessor
                        'status' => $status, // Use the determined status
                    ]
                );
            } elseif ($balance <= 0.01) {
                 // Optional: Delete the debt record if the invoice gets fully paid
                 // StudentDebt::where('invoice_id', $this->id)->delete();
            }

        }
        return null; // No student ID or no balance
    }
}