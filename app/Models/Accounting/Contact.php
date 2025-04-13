<?php

namespace App\Models\Accounting;

use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contacts';

    protected $fillable = [
        'school_id',
        'contact_type',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_number',
        'is_active',
        'student_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalance()
    {
        $invoiceTotal = $this->invoices()->whereIn('status', ['sent', 'partial'])->sum('total');
        $paidAmount = $this->payments()->where('status', 'completed')->sum('amount');
        
        return $invoiceTotal - $paidAmount;
    }
}