<?php

namespace App\Models\StudentProfile;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentPlan;
use App\Models\Accounting\StudentDebt;
use App\Models\Traits\HasStatus;
use App\Models\Traits\LogsActivity; // Add this import
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentProfile extends Model
{
    use HasFactory, SoftDeletes, HasStatus, LogsActivity; // Add LogsActivity trait here

    protected $fillable = [
        'school_id',
        'admission_number',
        'first_name',
        'last_name',
        'other_names',
        'date_of_birth',
        'gender',
        'email',
        'phone',
        'address',
        'photo',
        'class_id',
        'status',
        'enrollment_date',
        'graduation_date',
        'is_boarder',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'graduation_date' => 'date',
        'is_boarder' => 'boolean',
    ];

    /**
     * Get the student's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the school that the student belongs to.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the student's class.
     */
    public function class()
    {
        return $this->belongsTo(TimetableSchoolClass::class, 'class_id');
    }

    /**
     * Get the student's guardians.
     */
    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot('is_emergency_contact')
            ->withTimestamps();
    }

    /**
     * Get the student's enrollments.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the student's current enrollment.
     */
    public function currentEnrollment()
    {
        return $this->hasOne(Enrollment::class)
            ->where('status', 'active')
            ->orderByDesc('created_at');
    }

    /**
     * Get the student's documents.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get the student's invoices.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the student's payments.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the student debts.
     */
    public function studentDebts()
    {
        return $this->hasMany(StudentDebt::class);
    }

    /**
     * Get the student's payment plans.
     */
    public function paymentPlans()
    {
        return $this->hasMany(PaymentPlan::class);
    }

    /**
     * Scope a query to only include active students.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the student's current balance.
     *
     * @return float
     */
    public function getCurrentBalanceAttribute()
    {
        $totalInvoiced = $this->invoices->sum('total');
        $totalPaid = $this->payments->sum('amount');
        return $totalInvoiced - $totalPaid;
    }

    /**
     * Scope a query to filter students by various criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('admission_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        });

        $query->when($filters['class_id'] ?? null, function ($query, $classId) {
            $query->where('class_id', $classId);
        });

        $query->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        });

        $query->when($filters['gender'] ?? null, function ($query, $gender) {
            $query->where('gender', $gender);
        });

        $query->when(isset($filters['is_boarder']), function ($query) use ($filters) {
            $query->where('is_boarder', $filters['is_boarder'] == 1);
        });

        return $query;
    }
}