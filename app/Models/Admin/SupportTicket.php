<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_number',
        'subject',
        'description',
        'category',
        'priority', // 'low', 'medium', 'high', 'urgent'
        'status', // 'open', 'in-progress', 'on-hold', 'resolved', 'closed'
        'reported_by',
        'assigned_to',
        'equipment_id',
        'equipment_type', // 'it', 'facility', etc.
        'due_date',
        'resolution',
        'resolution_date',
        'attachments',
        'is_private',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
        'resolution_date' => 'date',
        'attachments' => 'array',
        'is_private' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($ticket) {
            // Generate ticket number if not provided
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = 'TKT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the staff member who reported the ticket.
     */
    public function reportedBy()
    {
        return $this->belongsTo(Staff::class, 'reported_by');
    }

    /**
     * Get the staff member assigned to the ticket.
     */
    public function assignedTo()
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    /**
     * Get the equipment related to the ticket.
     */
    public function equipment()
    {
        if ($this->equipment_type === 'it') {
            return $this->belongsTo(ITEquipment::class, 'equipment_id');
        } else if ($this->equipment_type === 'facility') {
            return $this->belongsTo(Facility::class, 'equipment_id');
        }
        
        return null;
    }

    /**
     * Get the responses for the ticket.
     */
    public function responses()
    {
        return $this->hasMany(TicketResponse::class, 'ticket_id');
    }

    /**
     * Get the activity logs for the ticket.
     */
    public function activityLogs()
    {
        return $this->hasMany(TicketActivityLog::class, 'ticket_id');
    }

    /**
     * Scope a query to only include tickets with a given status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include tickets with a given priority.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $priority
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include tickets assigned to a specific staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $staffId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedTo($query, $staffId)
    {
        return $query->where('assigned_to', $staffId);
    }

    /**
     * Scope a query to only include tickets reported by a specific staff.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $staffId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReportedBy($query, $staffId)
    {
        return $query->where('reported_by', $staffId);
    }

    /**
     * Scope a query to only include tickets in a specific category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include overdue tickets.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
                    ->where('due_date', '<', now())
                    ->whereNotIn('status', ['resolved', 'closed']);
    }

    /**
     * Scope a query to only include open tickets.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in-progress', 'on-hold']);
    }

    /**
     * Check if ticket is overdue.
     *
     * @return bool
     */
    public function isOverdue()
    {
        return $this->due_date && 
               $this->due_date < now() && 
               !in_array($this->status, ['resolved', 'closed']);
    }

    /**
     * Assign ticket to staff.
     *
     * @param  int  $staffId
     * @param  string|null  $notes
     * @return bool
     */
    public function assignTo($staffId, $notes = null)
    {
        $oldAssignee = $this->assigned_to;
        $this->assigned_to = $staffId;
        
        if ($this->status === 'open') {
            $this->status = 'in-progress';
        }
        
        $saved = $this->save();
        
        // Log activity
        if ($saved) {
            $this->logActivity(
                'assigned', 
                "Ticket assigned to staff ID {$staffId}" . ($oldAssignee ? " from staff ID {$oldAssignee}" : ""),
                $notes
            );
        }
        
        return $saved;
    }

    /**
     * Update ticket status.
     *
     * @param  string  $status
     * @param  string|null  $notes
     * @return bool
     */
    public function updateStatus($status, $notes = null)
    {
        $oldStatus = $this->status;
        $this->status = $status;
        
        // If resolving or closing, set resolution date
        if (in_array($status, ['resolved', 'closed']) && !$this->resolution_date) {
            $this->resolution_date = now();
        }
        
        $saved = $this->save();
        
        // Log activity
        if ($saved) {
            $this->logActivity(
                'status_changed', 
                "Status changed from {$oldStatus} to {$status}",
                $notes
            );
        }
        
        return $saved;
    }

    /**
     * Add response to ticket.
     *
     * @param  array  $responseData
     * @return TicketResponse|null
     */
    public function addResponse($responseData)
    {
        $response = $this->responses()->create([
            'response' => $responseData['response'],
            'staff_id' => $responseData['staff_id'] ?? null,
            'is_private' => $responseData['is_private'] ?? false,
            'attachments' => $responseData['attachments'] ?? null,
        ]);
        
        // Update ticket status if needed
        if ($response && isset($responseData['update_status']) && $responseData['update_status']) {
            $this->updateStatus($responseData['status'], "Status updated via response");
        }
        
        // Log activity
        if ($response) {
            $this->logActivity(
                'response_added', 
                "Response added by staff ID " . ($responseData['staff_id'] ?? 'Unknown'),
                null
            );
        }
        
        return $response;
    }

    /**
     * Resolve the ticket.
     *
     * @param  string  $resolution
     * @param  string|null  $notes
     * @return bool
     */
    public function resolve($resolution, $notes = null)
    {
        $this->resolution = $resolution;
        $this->resolution_date = now();
        $this->status = 'resolved';
        
        $saved = $this->save();
        
        // Log activity
        if ($saved) {
            $this->logActivity(
                'resolved', 
                "Ticket resolved",
                $notes
            );
        }
        
        return $saved;
    }

    /**
     * Log activity for this ticket.
     *
     * @param  string  $action
     * @param  string  $description
     * @param  string|null  $notes
     * @return TicketActivityLog|null
     */
    public function logActivity($action, $description, $notes = null)
    {
        return $this->activityLogs()->create([
            'action' => $action,
            'description' => $description,
            'staff_id' => auth()->id() ?? null,
            'notes' => $notes,
        ]);
    }

    /**
     * Get time to resolution in hours.
     *
     * @return float|null
     */
    public function getTimeToResolutionAttribute()
    {
        if (!$this->resolution_date) {
            return null;
        }
        
        return $this->created_at->diffInHours($this->resolution_date);
    }

    /**
     * Get days since creation.
     *
     * @return int
     */
    public function getDaysSinceCreationAttribute()
    {
        return $this->created_at->diffInDays(now());
    }
}