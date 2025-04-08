<?php

namespace App\Models;

use App\Models\Accounting\Contact;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'student_id', // Student ID/Roll Number
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'address',
        'city',
        'state',
        'zip',
        'phone',
        'email',
        'grade_level',
        'enrollment_date',
        'graduation_date',
        'status', // 'active', 'inactive', 'graduated', 'transferred', etc.
        'guardian_id', // Foreign key to parent/guardian
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'graduation_date' => 'date',
    ];

    /**
     * Get the school that the student belongs to.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the contact record associated with the student.
     */
    public function contact()
    {
        return $this->hasOne(Contact::class);
    }

    /**
     * Get all invoices for the student through contact.
     */
    public function invoices()
    {
        return $this->hasManyThrough(
            Invoice::class, 
            Contact::class,
            'student_id', // Foreign key on contacts table
            'contact_id', // Foreign key on invoices table
            'id', // Local key on students table
            'id' // Local key on contacts table
        );
    }

    /**
     * Get all payments for the student through contact.
     */
    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class, 
            Contact::class,
            'student_id', // Foreign key on contacts table
            'contact_id', // Foreign key on payments table
            'id', // Local key on students table
            'id' // Local key on contacts table
        );
    }

    /**
     * Get all payment plans for the student.
     */
    public function paymentPlans()
    {
        return $this->hasMany(PaymentPlan::class);
    }

    /**
     * Get the student's full name.
     */
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Calculate the current balance for the student.
     */
    public function getCurrentBalanceAttribute()
    {
        $totalInvoiced = $this->invoices()
            ->whereIn('status', ['sent', 'partial', 'paid', 'overdue'])
            ->sum('total');
            
        $totalPaid = $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
            
        return $totalInvoiced - $totalPaid;
    }
}