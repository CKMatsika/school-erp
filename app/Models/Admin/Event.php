<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'event_type', // 'academic', 'cultural', 'sports', 'holiday', 'examination', 'other'
        'status', // 'upcoming', 'ongoing', 'completed', 'cancelled'
        'all_day',
        'visibility', // 'public', 'staff', 'students', 'parents', 'specific'
        'organizer_id',
        'organizer_type', // 'staff', 'department', 'student_club'
        'budget',
        'max_participants',
        'registration_required',
        'registration_deadline',
        'attachment',
        'color',
        'reminder_days_before',
        'created_by',
        'approved_by',
        'approval_date',
        'notes',
        'room_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'all_day' => 'boolean',
        'registration_required' => 'boolean',
        'registration_deadline' => 'date',
        'budget' => 'decimal:2',
        'max_participants' => 'integer',
        'reminder_days_before' => 'integer',
        'approval_date' => 'date',
    ];

    /**
     * Get the organizer based on organizer_type.
     */
    public function organizer()
    {
        if ($this->organizer_type === 'staff') {
            return $this->belongsTo(Staff::class, 'organizer_id');
        } elseif ($this->organizer_type === 'department') {
            return $this->belongsTo(Department::class, 'organizer_id');
        } elseif ($this->organizer_type === 'student_club') {
            return $this->belongsTo(StudentClub::class, 'organizer_id');
        }
        
        return null;
    }

    /**
     * Get the staff member who created this event.
     */
    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the staff member who approved this event.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Get the room assigned to this event.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the participants for this event.
     */
    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }

    /**
     * Scope a query to only include upcoming events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now())
                    ->where('status', 'upcoming');
    }

    /**
     * Scope a query to only include ongoing events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->where('status', 'ongoing');
    }

    /**
     * Scope a query to only include completed events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('end_date', '<', now())
                    ->orWhere('status', 'completed');
    }

    /**
     * Scope a query to only include events for a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date);
    }

    /**
     * Scope a query to only include events for a specific date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($query) use ($startDate, $endDate) {
                      $query->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                  });
        });
    }

    /**
     * Scope a query to only include events for a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope a query to only include events visible to specific audience.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $audience
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisibleTo($query, $audience)
    {
        return $query->where(function ($query) use ($audience) {
            $query->where('visibility', 'public')
                  ->orWhere('visibility', $audience);
        });
    }

    /**
     * Get participants count.
     *
     * @return int
     */
    public function getParticipantsCountAttribute()
    {
        return $this->participants()->count();
    }

    /**
     * Check if registration is open.
     *
     * @return bool
     */
    public function isRegistrationOpen()
    {
        if (!$this->registration_required) {
            return false;
        }
        
        // If registration deadline has passed
        if ($this->registration_deadline && $this->registration_deadline < now()) {
            return false;
        }
        
        // If event has started
        if ($this->start_date < now()) {
            return false;
        }
        
        // If max participants reached
        if ($this->max_participants && $this->participants_count >= $this->max_participants) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if event is full.
     *
     * @return bool
     */
    public function isFull()
    {
        return $this->max_participants && $this->participants_count >= $this->max_participants;
    }

    /**
     * Approve this event.
     *
     * @param  int  $approvedBy  Staff ID
     * @param  string|null  $notes
     * @return bool
     */
    public function approve($approvedBy, $notes = null)
    {
        $this->approved_by = $approvedBy;
        $this->approval_date = now();
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Approval notes: {$notes}";
        }
        
        return $this->save();
    }

    /**
     * Cancel this event.
     *
     * @param  string  $reason
     * @return bool
     */
    public function cancel($reason)
    {
        $this->status = 'cancelled';
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Cancellation reason: {$reason}";
        
        return $this->save();
    }

    /**
     * Register a participant for this event.
     *
     * @param  int  $participantId
     * @param  string  $participantType  'staff', 'student', 'parent', etc.
     * @param  array  $additionalInfo
     * @return EventParticipant|bool
     */
    public function registerParticipant($participantId, $participantType, $additionalInfo = [])
    {
        if (!$this->isRegistrationOpen()) {
            return false;
        }
        
        // Check if already registered
        $existing = $this->participants()
                         ->where('participant_id', $participantId)
                         ->where('participant_type', $participantType)
                         ->first();
        
        if ($existing) {
            return false;
        }
        
        // Create new participant
        return $this->participants()->create([
            'participant_id' => $participantId,
            'participant_type' => $participantType,
            'registration_date' => now(),
            'status' => 'registered',
            'additional_info' => $additionalInfo,
        ]);
    }

    /**
     * Update event status based on current date.
     *
     * @return bool
     */
    public function updateStatus()
    {
        $today = now();
        
        if ($this->status === 'cancelled') {
            return false;
        }
        
        if ($this->end_date < $today) {
            $this->status = 'completed';
        } elseif ($this->start_date <= $today && $this->end_date >= $today) {
            $this->status = 'ongoing';
        } else {
            $this->status = 'upcoming';
        }
        
        return $this->save();
    }

    /**
     * Get the formatted event duration.
     *
     * @return string
     */
    public function getFormattedDurationAttribute()
    {
        if ($this->start_date->isSameDay($this->end_date)) {
            // Same day event
            return $this->start_date->format('M d, Y') . ' ' . 
                   ($this->all_day ? '(All day)' : $this->start_time . ' - ' . $this->end_time);
        } else {
            // Multi-day event
            return $this->start_date->format('M d, Y') . 
                   ($this->all_day ? '' : ' ' . $this->start_time) . 
                   ' - ' . 
                   $this->end_date->format('M d, Y') . 
                   ($this->all_day ? '' : ' ' . $this->end_time);
        }
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'upcoming':
                return 'badge-info';
            case 'ongoing':
                return 'badge-success';
            case 'completed':
                return 'badge-primary';
            case 'cancelled':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get the attachment URL.
     *
     * @return string|null
     */
    public function getAttachmentUrlAttribute()
    {
        return $this->attachment ? url('storage/' . $this->attachment) : null;
    }
}