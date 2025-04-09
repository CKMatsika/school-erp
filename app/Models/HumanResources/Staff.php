<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User; // Assuming User model is in App\Models
use App\Models\Settings\AcademicYear; // Assuming AcademicYear model location
use App\Models\Settings\SchoolClass; // Assuming SchoolClass model location
use App\Models\Settings\Subject; // Assuming Subject model location

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff'; // Explicitly define table name

    protected $fillable = [
        'user_id',
        'staff_number',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'date_of_birth',
        'email',
        'phone_number',
        'address',
        'date_joined',
        'employment_type',
        'staff_type',
        'job_title',
        'department',
        'basic_salary',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_joined' => 'date',
        'is_active' => 'boolean',
        'basic_salary' => 'decimal:2',
    ];

    // --- Relationships ---

    /**
     * Get the user account associated with the staff member (optional).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The subjects taught by the staff member (for teachers).
     */
    public function subjects()
    {
        // Ensure pivot table name 'teacher_subjects' is correct
        return $this->belongsToMany(Subject::class, 'teacher_subjects')
                    ->withTimestamps();
    }

    /**
     * The classes assigned to the staff member (for teachers).
     */
    public function classes()
    {
         // Ensure pivot table name 'teacher_classes' and FK 'school_class_id' are correct
        return $this->belongsToMany(SchoolClass::class, 'teacher_classes')
                    ->withPivot('academic_year_id') // Include academic year from pivot
                    ->withTimestamps();
    }

    /**
     * Get the attendance records for the staff member.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the payroll elements assigned to the staff member.
     */
    public function payrollElements()
    {
        // Ensure pivot table name 'staff_payroll_elements' is correct
        return $this->belongsToMany(PayrollElement::class, 'staff_payroll_elements')
                    ->withPivot('amount_or_rate', 'start_date', 'end_date')
                    ->withTimestamps();
    }

    /**
     * Get the payslips generated for the staff member.
     */
    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    // --- Accessors ---

    /**
     * Get the staff member's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    // --- Scopes ---

    /**
     * Scope a query to only include active staff.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include teaching staff.
     */
    public function scopeTeaching($query)
    {
        return $query->where('staff_type', 'teaching');
    }

    /**
     * Scope a query to only include non-teaching staff.
     */
    public function scopeNonTeaching($query)
    {
        return $query->where('staff_type', 'non-teaching');
    }
}