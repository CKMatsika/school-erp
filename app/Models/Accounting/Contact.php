<?php

namespace App\Models\Accounting;

use App\Models\School;
use App\Models\Student; // Assuming Student model exists if student_id FK is used
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Required for Notifications (like SMS/Email potentially)
use Illuminate\Notifications\Notifiable;

class Contact extends Model
{
    // Use Notifiable trait if you plan to send notifications TO contacts
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     * Explicitly set, confirmed by Tinker.
     * @var string
     */
    protected $table = 'contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'contact_type', // Crucial: Must be 'student' for ledger query
        'name',
        'company_name', // Added common field
        'email',
        'phone',         // Crucial for SMS notifications
        'address_line_1', // Split address for better structure
        'address_line_2',
        // 'address',    // Consider removing if using line_1/line_2
        'city',
        'state',
        'postal_code',
        'country',
        'tax_number',
        'is_active',    // Crucial: Must be true for ledger query
        'student_id',   // Optional: If linking to a separate students table
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the school that owns the contact (if applicable).
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the student record associated with this contact (if using separate student table).
     */
    public function student(): BelongsTo
    {
        // Assumes 'student_id' is the foreign key in the 'contacts' table
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the invoices associated with this contact.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the payments associated with this contact.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Calculate the current outstanding balance for the contact.
     * Note: This might be resource-intensive if called frequently for many contacts.
     * Consider calculating/storing balance separately if performance becomes an issue.
     *
     * @return float
     */
    public function getBalance(): float
    {
        // Sum totals of non-draft/void invoices
        $invoiceTotal = $this->invoices()
                             ->whereNotIn('status', ['draft', 'void'])
                             ->sum('total');

        // Sum completed payments
        $paidAmount = $this->payments()
                           ->where('status', 'completed')
                           ->sum('amount');

        return (float)$invoiceTotal - (float)$paidAmount;
    }

    // --- Notification Routing (Example for SMS) ---

    /**
     * Route notifications for the SMS channel.
     *
     * @param  \Illuminate\Notifications\Notification|null $notification
     * @return string|null The phone number to send the SMS to.
     */
    public function routeNotificationForSms($notification = null): ?string
    {
        // Use the 'phone' column, adjust if necessary (e.g., 'mobile_phone')
        return $this->phone;
    }

    // Add routeNotificationForVonage, routeNotificationForTwilio etc. if using specific drivers
}