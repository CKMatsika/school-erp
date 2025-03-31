<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_number',
        'title',
        'description',
        'maintenance_type', // 'preventive', 'corrective', 'emergency', 'inspection'
        'priority', // 'low', 'medium', 'high', 'urgent'
        'location_id',
        'room_id',
        'equipment_id',
        'equipment_type', // 'facility', 'it', 'vehicle', 'other'
        'requested_by',
        'assigned_to',
        'verified_by',
        'approved_by',
        'approval_date',
        'scheduled_date',
        'completion_date',
        'status', // 'pending', 'approved', 'in-progress', 'on-hold', 'completed', 'rejected', 'cancelled'
        'cost_estimate',
        'actual_cost',
        'work_order_number',
        'attachments',
        'remarks',
        'feedback',
        'feedback_rating',
        'vendor_id',
        'department_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'approval_date' => 'date',
        'scheduled_date' => 'date',
        'completion_date' => 'date',
        'cost_estimate' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'attachments' => 'array',
        'feedback_rating' => 'integer',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($request) {
            // Generate request number if not provided
            if (!$request->request_number) {
                $prefix = 'MR-';
                $date = date('Ymd');
                $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $request->request_number = $prefix . $date . '-' . $random;
            }
        });
    }

    /**
     * Get the location of the maintenance request.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the room for the maintenance request.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the equipment that needs maintenance.
     */
    public function equipment()
    {
        if ($this->equipment_type === 'facility') {
            return $this->belongsTo(Facility::class, 'equipment_id');
        } elseif ($this->equipment_type === 'it') {
            return $this->belongsTo(ITEquipment::class, 'equipment_id');
        } elseif ($this->equipment_type === 'vehicle') {
            return $this->belongsTo(Vehicle::class, 'equipment_id');
        }
        
        return null;
    }

    /**
     * Get the staff member who requested the maintenance.
     */
    public function requestedBy()
    {
        return $this->belongsTo(Staff::class, 'requested_by');
    }

    /**
     * Get the staff member assigned to the maintenance.
     */
    public function assignedTo()
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    /**
     * Get the staff member who verified the maintenance.
     */
    public function verifiedBy()
    {
        return $this->belongsTo(Staff::class, 'verified_by');
    }

    /**
     * Get the staff member who approved the maintenance.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Get the vendor for the maintenance.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the department for the maintenance.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the work logs for the maintenance request.
     */
    public function workLogs()
    {
        return $this->hasMany(MaintenanceWorkLog::class, 'maintenance_request_id');
    }

    /**
     * Get the parts used for the maintenance request.
     */
    public function partsUsed()
    {
        return $this->hasMany(MaintenancePart::class, 'maintenance_request_id');
    }

    /**
     * Scope a query to only include requests with a specific status.
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
     * Scope a query to only include pending requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include in-progress requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    /**
     * Scope a query to only include completed requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include requests with a specific priority.
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
     * Scope a query to only include requests for a specific equipment type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEquipmentType($query, $type)
    {
        return $query->where('equipment_type', $type);
    }

    /**
     * Scope a query to only include requests for a specific department.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to only include requests from a specific requester.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $requesterId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromRequester($query, $requesterId)
    {
        return $query->where('requested_by', $requesterId);
    }

    /**
     * Scope a query to only include requests assigned to a specific staff.
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
     * Approve the maintenance request.
     *
     * @param  int  $approvedBy  Staff ID
     * @param  \DateTime|string|null  $scheduledDate
     * @param  float|null  $costEstimate
     * @param  string|null  $remarks
     * @return bool
     */
    public function approve($approvedBy, $scheduledDate = null, $costEstimate = null, $remarks = null)
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        $this->status = 'approved';
        $this->approved_by = $approvedBy;
        $this->approval_date = now();
        
        if ($scheduledDate) {
            $this->scheduled_date = $scheduledDate;
        }
        
        if ($costEstimate !== null) {
            $this->cost_estimate = $costEstimate;
        }
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Approval remarks: {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Reject the maintenance request.
     *
     * @param  int  $rejectedBy  Staff ID
     * @param  string  $reason
     * @return bool
     */
    public function reject($rejectedBy, $reason)
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        $this->status = 'rejected';
        $this->approved_by = $rejectedBy;
        $this->approval_date = now();
        $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Rejection reason: {$reason}";
        
        return $this->save();
    }

    /**
     * Assign the maintenance request to a staff member.
     *
     * @param  int  $staffId
     * @param  string|null  $remarks
     * @return bool
     */
    public function assignTo($staffId, $remarks = null)
    {
        if (!in_array($this->status, ['approved', 'in-progress', 'on-hold'])) {
            return false;
        }
        
        $this->assigned_to = $staffId;
        
        if ($this->status === 'approved') {
            $this->status = 'in-progress';
        }
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Assignment remarks: {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Start work on the maintenance request.
     *
     * @param  string|null  $remarks
     * @return bool
     */
    public function startWork($remarks = null)
    {
        if ($this->status !== 'approved') {
            return false;
        }
        
        $this->status = 'in-progress';
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Work started remarks: {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Put the maintenance request on hold.
     *
     * @param  string  $reason
     * @return bool
     */
    public function putOnHold($reason)
    {
        if ($this->status !== 'in-progress') {
            return false;
        }
        
        $this->status = 'on-hold';
        $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "On-hold reason: {$reason}";
        
        return $this->save();
    }

    /**
     * Resume work on the maintenance request.
     *
     * @param  string|null  $remarks
     * @return bool
     */
    public function resumeWork($remarks = null)
    {
        if ($this->status !== 'on-hold') {
            return false;
        }
        
        $this->status = 'in-progress';
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Work resumed remarks: {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Complete the maintenance request.
     *
     * @param  array  $completionData
     * @return bool
     */
    public function complete($completionData)
    {
        if ($this->status !== 'in-progress') {
            return false;
        }
        
        $this->status = 'completed';
        $this->completion_date = $completionData['completion_date'] ?? now();
        $this->actual_cost = $completionData['actual_cost'] ?? $this->cost_estimate;
        
        if (isset($completionData['remarks'])) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Completion remarks: {$completionData['remarks']}";
        }
        
        return $this->save();
    }

    /**
     * Verify the completed maintenance request.
     *
     * @param  int  $verifiedBy  Staff ID
     * @param  string|null  $remarks
     * @return bool
     */
    public function verify($verifiedBy, $remarks = null)
    {
        if ($this->status !== 'completed') {
            return false;
        }
        
        $this->verified_by = $verifiedBy;
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Verification remarks: {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Add feedback to the maintenance request.
     *
     * @param  string  $feedback
     * @param  int  $rating  1-5
     * @return bool
     */
    public function addFeedback($feedback, $rating)
    {
        if ($this->status !== 'completed') {
            return false;
        }
        
        $this->feedback = $feedback;
        $this->feedback_rating = min(5, max(1, $rating));
        
        return $this->save();
    }

    /**
     * Add a work log to the maintenance request.
     *
     * @param  array  $logData
     * @return MaintenanceWorkLog
     */
    public function addWorkLog($logData)
    {
        return $this->workLogs()->create([
            'log_date' => $logData['log_date'] ?? now(),
            'staff_id' => $logData['staff_id'] ?? $this->assigned_to,
            'hours_worked' => $logData['hours_worked'],
            'description' => $logData['description'],
            'attachments' => $logData['attachments'] ?? null,
        ]);
    }

    /**
     * Add a part used to the maintenance request.
     *
     * @param  array  $partData
     * @return MaintenancePart
     */
    public function addPartUsed($partData)
    {
        return $this->partsUsed()->create([
            'part_name' => $partData['part_name'],
            'part_number' => $partData['part_number'] ?? null,
            'quantity' => $partData['quantity'],
            'cost' => $partData['cost'],
            'supplier' => $partData['supplier'] ?? null,
            'remarks' => $partData['remarks'] ?? null,
        ]);
    }

    /**
     * Calculate total hours worked on the maintenance request.
     *
     * @return float
     */
    public function getTotalHoursWorkedAttribute()
    {
        return $this->workLogs()->sum('hours_worked');
    }

    /**
     * Calculate total cost of parts used for the maintenance request.
     *
     * @return float
     */
    public function getTotalPartsUsedCostAttribute()
    {
        return $this->partsUsed()->sum(\DB::raw('cost * quantity'));
    }

    /**
     * Get the total cost of the maintenance request.
     *
     * @return float
     */
    public function getTotalCostAttribute()
    {
        // Labor cost (assuming a standard labor rate) + parts cost
        $laborRate = config('maintenance.labor_rate', 20.00); // Default labor rate per hour
        $laborCost = $this->total_hours_worked * $laborRate;
        
        return $laborCost + $this->total_parts_used_cost;
    }

    /**
     * Get the estimated completion time based on priority and type.
     *
     * @return int  Number of days
     */
    public function getEstimatedCompletionTimeAttribute()
    {
        // Default values based on priority
        $defaultDays = [
            'urgent' => 1,
            'high' => 3,
            'medium' => 7,
            'low' => 14,
        ];
        
        // Adjust based on maintenance type
        $typeMultiplier = [
            'emergency' => 0.5, // Faster for emergencies
            'corrective' => 1.0,
            'preventive' => 1.5, // Can take longer for preventive maintenance
            'inspection' => 0.8,
        ];
        
        $days = $defaultDays[$this->priority] ?? 7;
        $multiplier = $typeMultiplier[$this->maintenance_type] ?? 1.0;
        
        return round($days * $multiplier);
    }

    /**
     * Check if the maintenance request is overdue.
     *
     * @return bool
     */
    public function isOverdue()
    {
        if (in_array($this->status, ['completed', 'rejected', 'cancelled'])) {
            return false;
        }
        
        if ($this->scheduled_date) {
            return $this->scheduled_date < now();
        }
        
        // If no scheduled date, check if it's been outstanding longer than estimated time
        if ($this->approval_date) {
            $estimatedCompletion = $this->approval_date->addDays($this->estimated_completion_time);
            return $estimatedCompletion < now();
        }
        
        return false;
    }

    /**
     * Get the days overdue.
     *
     * @return int|null
     */
    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        $dueDate = $this->scheduled_date ?? 
                  ($this->approval_date ? $this->approval_date->addDays($this->estimated_completion_time) : null);
        
        if (!$dueDate) {
            return null;
        }
        
        return now()->diffInDays($dueDate);
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        $baseClass = '';
        
        switch ($this->status) {
            case 'pending':
                $baseClass = 'badge-warning';
                break;
            case 'approved':
                $baseClass = 'badge-info';
                break;
            case 'in-progress':
                $baseClass = 'badge-primary';
                break;
            case 'on-hold':
                $baseClass = 'badge-secondary';
                break;
            case 'completed':
                $baseClass = 'badge-success';
                break;
            case 'rejected':
                $baseClass = 'badge-danger';
                break;
            case 'cancelled':
                $baseClass = 'badge-dark';
                break;
            default:
                $baseClass = 'badge-light';
        }
        
        // Add overdue indicator if applicable
        if ($this->isOverdue()) {
            $baseClass .= ' overdue';
        }
        
        return $baseClass;
    }

    /**
     * Get the priority badge class.
     *
     * @return string
     */
    public function getPriorityBadgeClassAttribute()
    {
        switch ($this->priority) {
            case 'urgent':
                return 'badge-danger';
            case 'high':
                return 'badge-warning';
            case 'medium':
                return 'badge-info';
            case 'low':
                return 'badge-success';
            default:
                return 'badge-secondary';
        }
    }
}