<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryMember extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id',
        'member_type', // 'student', 'teacher', 'staff', 'external'
        'reference_id', // ID in respective table (student_id, teacher_id, etc.)
        'name',
        'email',
        'phone',
        'address',
        'gender',
        'date_of_birth',
        'membership_start_date',
        'membership_end_date',
        'class_section',
        'roll_number',
        'department',
        'designation',
        'organization',
        'id_proof',
        'photo',
        'max_books_allowed',
        'max_borrowing_days',
        'status', // 'active', 'inactive', 'suspended', 'expired'
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'membership_start_date' => 'date',
        'membership_end_date' => 'date',
        'max_books_allowed' => 'integer',
        'max_borrowing_days' => 'integer',
    ];

    /**
     * Get the circulations for the member.
     */
    public function circulations()
    {
        return $this->hasMany(BookCirculation::class, 'member_id');
    }

    /**
     * Get active circulations for the member.
     */
    public function activeCirculations()
    {
        return $this->circulations()->whereNull('return_date');
    }

    /**
     * Get overdue circulations for the member.
     */
    public function overdueCirculations()
    {
        return $this->circulations()
                    ->whereNull('return_date')
                    ->where('due_date', '<', now());
    }

    /**
     * Get unpaid fines for the member.
     */
    public function unpaidFines()
    {
        return $this->circulations()
                    ->where('fine_amount', '>', 0)
                    ->where('fine_paid', false);
    }

    /**
     * Scope a query to only include active members.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include expired memberships.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('membership_end_date', '<', now())
                    ->where('status', '!=', 'expired');
    }

    /**
     * Scope a query to only include members of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('member_type', $type);
    }

    /**
     * Check if membership is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->membership_end_date >= now();
    }

    /**
     * Check if member can borrow more books.
     *
     * @return bool
     */
    public function canBorrowMore()
    {
        if (!$this->isActive()) {
            return false;
        }
        
        $currentlyBorrowed = $this->activeCirculations()->count();
        return $currentlyBorrowed < $this->max_books_allowed;
    }

    /**
     * Get number of books the member can still borrow.
     *
     * @return int
     */
    public function getRemainingBooksAllowedAttribute()
    {
        if (!$this->isActive()) {
            return 0;
        }
        
        $currentlyBorrowed = $this->activeCirculations()->count();
        return max(0, $this->max_books_allowed - $currentlyBorrowed);
    }

    /**
     * Get total unpaid fine amount.
     *
     * @return float
     */
    public function getTotalUnpaidFineAttribute()
    {
        return $this->unpaidFines()->sum('fine_amount');
    }

    /**
     * Renew membership.
     *
     * @param  \DateTime|string  $endDate
     * @param  string  $remarks
     * @return bool
     */
    public function renewMembership($endDate, $remarks = null)
    {
        $this->membership_end_date = $endDate;
        
        if ($this->status === 'expired') {
            $this->status = 'active';
        }
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . 
                            "Membership renewed until {$endDate}. {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Update membership status based on end date.
     *
     * @return bool
     */
    public function updateMembershipStatus()
    {
        if ($this->status !== 'suspended' && $this->membership_end_date < now()) {
            $this->status = 'expired';
            return $this->save();
        }
        
        return false;
    }

    /**
     * Suspend membership.
     *
     * @param  string  $reason
     * @return bool
     */
    public function suspend($reason)
    {
        $this->status = 'suspended';
        $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . 
                        "Membership suspended on " . now() . ". Reason: {$reason}";
        
        return $this->save();
    }

    /**
     * Reactivate suspended membership.
     *
     * @param  string  $reason
     * @return bool
     */
    public function reactivate($reason)
    {
        // Only reactivate if it was suspended and membership hasn't expired
        if ($this->status === 'suspended' && $this->membership_end_date >= now()) {
            $this->status = 'active';
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . 
                            "Membership reactivated on " . now() . ". Reason: {$reason}";
            
            return $this->save();
        }
        
        return false;
    }

    /**
     * Generate member ID card.
     *
     * @return array
     */
    public function generateIdCard()
    {
        // Implementation would depend on the ID card generation system
        // This is a placeholder for the actual implementation
        
        return [
            'member_id' => $this->member_id,
            'name' => $this->name,
            'member_type' => $this->member_type,
            'valid_until' => $this->membership_end_date,
            'photo' => $this->photo,
            // Additional fields as needed
        ];
    }

    /**
     * Get related entity (student, teacher, staff).
     */
    public function relatedEntity()
    {
        if ($this->member_type === 'student') {
            return $this->belongsTo(Student::class, 'reference_id');
        } elseif ($this->member_type === 'teacher') {
            return $this->belongsTo(Staff::class, 'reference_id');
        } elseif ($this->member_type === 'staff') {
            return $this->belongsTo(Staff::class, 'reference_id');
        }
        
        return null;
    }
}