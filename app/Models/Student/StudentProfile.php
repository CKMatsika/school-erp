<?php

namespace App\Models\Student;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentPlan;
use App\Models\Accounting\StudentDebt;
use App\Models\Traits\HasStatus;
use App\Models\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentProfile extends Model
{
    use HasFactory, SoftDeletes, HasStatus, LogsActivity;

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
        'boarding_status',
        'current_hostel_allocation_id',
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
     * Get the current hostel allocation for this student.
     */
    public function currentHostelAllocation()
    {
        return $this->hasOne(HostelAllocation::class, 'id', 'current_hostel_allocation_id');
    }

    /**
     * Get all hostel allocations for this student.
     */
    public function hostelAllocations()
    {
        return $this->hasMany(HostelAllocation::class, 'student_profile_id');
    }

    /**
     * Get the current bed allocation.
     */
    public function currentBed()
    {
        return $this->currentHostelAllocation ? $this->currentHostelAllocation->bed : null;
    }

    /**
     * Get the current room allocation.
     */
    public function currentRoom()
    {
        return $this->currentBed ? $this->currentBed->room : null;
    }

    /**
     * Get the current house allocation.
     */
    public function currentHouse()
    {
        return $this->currentRoom ? $this->currentRoom->house : null;
    }

    /**
     * Allocate a bed to this student.
     */
    public function allocateBed(HostelBed $bed, $academicYearId = null, $notes = null)
    {
        // Check if the bed is available
        if ($bed->status !== 'available' || !$bed->is_active) {
            return false;
        }
        
        // Check if bed's house gender matches student's gender
        $house = $bed->room->house;
        if ($house->gender !== 'mixed' && $house->gender !== $this->gender) {
            return false;
        }
        
        // Start a transaction
        \DB::beginTransaction();
        
        try {
            // End any existing allocations
            $this->endCurrentAllocation();
            
            // Create a new allocation
            $allocation = HostelAllocation::create([
                'student_profile_id' => $this->id,
                'hostel_bed_id' => $bed->id,
                'academic_year_id' => $academicYearId,
                'allocation_date' => now(),
                'is_current' => true,
                'status' => 'active',
                'notes' => $notes
            ]);
            
            // Update the bed status
            $bed->update([
                'status' => 'occupied'
            ]);
            
            // Update the student's boarding status
            $this->update([
                'is_boarder' => true,
                'boarding_status' => 'allocated',
                'current_hostel_allocation_id' => $allocation->id
            ]);
            
            \DB::commit();
            return $allocation;
        } catch (\Exception $e) {
            \DB::rollBack();
            return false;
        }
    }

    /**
     * End the current bed allocation.
     */
    public function endCurrentAllocation()
    {
        if (!$this->currentHostelAllocation) {
            return true;
        }

        // Start a transaction
        \DB::beginTransaction();
        
        try {
            $currentBed = $this->currentBed();
            
            // Update the allocation
            $this->currentHostelAllocation->update([
                'is_current' => false,
                'status' => 'ended',
                'expiry_date' => now()
            ]);
            
            // Update the bed status
            if ($currentBed) {
                $currentBed->update(['status' => 'available']);
            }
            
            // Update student status
            $this->update([
                'boarding_status' => 'day_scholar',
                'current_hostel_allocation_id' => null
            ]);
            
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            return false;
        }
    }

    /**
     * Check if this student has an active hostel allocation.
     */
    public function hasActiveHostelAllocation()
    {
        return $this->currentHostelAllocation !== null;
    }

    /**
     * Get the current hostel allocation details.
     */
    public function getCurrentHostelDetailsAttribute()
    {
        if (!$this->currentHostelAllocation) {
            return 'Not allocated';
        }
        
        $bed = $this->currentBed();
        $room = $this->currentRoom();
        $house = $this->currentHouse();
        
        if (!$bed || !$room || !$house) {
            return 'Allocation data incomplete';
        }
        
        return "House: {$house->name}, Room: {$room->room_number}, Bed: {$bed->bed_number}";
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
     * Filter students by boarding status.
     */
    public function scopeBoarders($query, $isBoarder = true)
    {
        return $query->where('is_boarder', $isBoarder);
    }

    /**
     * Filter students by boarding status.
     */
    public function scopeBoardingStatus($query, $status)
    {
        if ($status) {
            return $query->where('boarding_status', $status);
        }
        
        return $query;
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

        $query->when($filters['boarding_status'] ?? null, function ($query, $boardingStatus) {
            $query->where('boarding_status', $boardingStatus);
        });

        $query->when($filters['hostel_house_id'] ?? null, function ($query, $houseId) {
            $query->whereHas('currentBed.room.house', function ($query) use ($houseId) {
                $query->where('id', $houseId);
            });
        });

        $query->when($filters['hostel_room_id'] ?? null, function ($query, $roomId) {
            $query->whereHas('currentBed.room', function ($query) use ($roomId) {
                $query->where('id', $roomId);
            });
        });

        return $query;
    }
}