<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'room_number',
        'floor',
        'building_id',
        'capacity',
        'room_type', // 'classroom', 'laboratory', 'office', 'meeting', 'library', 'auditorium', 'other'
        'facilities', // JSON array of available facilities
        'status', // 'available', 'occupied', 'under-maintenance', 'reserved'
        'description',
        'department_id',
        'photos', // JSON array of photo paths
        'floor_plan',
        'size',
        'location_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'capacity' => 'integer',
        'facilities' => 'array',
        'photos' => 'array',
        'size' => 'decimal:2',
    ];

    /**
     * Get the building that contains this room.
     */
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Get the department that owns this room.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the bookings for this room.
     */
    public function bookings()
    {
        return $this->hasMany(RoomBooking::class);
    }

    /**
     * Get the events in this room.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the timetable entries for this room.
     */
    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }

    /**
     * Scope a query to only include rooms with a specific status.
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
     * Scope a query to only include available rooms.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope a query to only include rooms of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('room_type', $type);
    }

    /**
     * Scope a query to only include rooms with minimum capacity.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $capacity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMinCapacity($query, $capacity)
    {
        return $query->where('capacity', '>=', $capacity);
    }

    /**
     * Scope a query to only include rooms with specific facilities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array|string  $facilities
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithFacilities($query, $facilities)
    {
        if (is_string($facilities)) {
            $facilities = [$facilities];
        }
        
        foreach ($facilities as $facility) {
            $query->whereRaw("JSON_CONTAINS(facilities, '\"$facility\"')");
        }
        
        return $query;
    }

    /**
     * Scope a query to only include rooms in a specific building.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $buildingId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInBuilding($query, $buildingId)
    {
        return $query->where('building_id', $buildingId);
    }

    /**
     * Check if the room is available for a specific time slot.
     *
     * @param  string  $date
     * @param  string  $startTime
     * @param  string  $endTime
     * @return bool
     */
    public function isAvailableFor($date, $startTime, $endTime)
    {
        if ($this->status !== 'available') {
            return false;
        }
        
        // Check room bookings
        $conflictingBookings = $this->bookings()
                                    ->whereDate('booking_date', $date)
                                    ->where(function ($query) use ($startTime, $endTime) {
                                        $query->where(function ($q) use ($startTime, $endTime) {
                                            $q->where('start_time', '<=', $startTime)
                                              ->where('end_time', '>=', $startTime);
                                        })->orWhere(function ($q) use ($startTime, $endTime) {
                                            $q->where('start_time', '<=', $endTime)
                                              ->where('end_time', '>=', $endTime);
                                        })->orWhere(function ($q) use ($startTime, $endTime) {
                                            $q->where('start_time', '>=', $startTime)
                                              ->where('end_time', '<=', $endTime);
                                        });
                                    })
                                    ->count();
        
        if ($conflictingBookings > 0) {
            return false;
        }
        
        // Check events
        $conflictingEvents = $this->events()
                                  ->whereDate('start_date', '<=', $date)
                                  ->whereDate('end_date', '>=', $date)
                                  ->where(function ($query) use ($startTime, $endTime) {
                                      $query->where(function ($q) use ($startTime, $endTime) {
                                          $q->where('start_time', '<=', $startTime)
                                            ->where('end_time', '>=', $startTime);
                                      })->orWhere(function ($q) use ($startTime, $endTime) {
                                          $q->where('start_time', '<=', $endTime)
                                            ->where('end_time', '>=', $endTime);
                                      })->orWhere(function ($q) use ($startTime, $endTime) {
                                          $q->where('start_time', '>=', $startTime)
                                            ->where('end_time', '<=', $endTime);
                                      })->orWhere('all_day', true);
                                  })
                                  ->count();
        
        if ($conflictingEvents > 0) {
            return false;
        }
        
        // Check timetable entries (for regular class schedules)
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        $conflictingTimetable = $this->timetableEntries()
                                     ->where('day_of_week', $dayOfWeek)
                                     ->where(function ($query) use ($startTime, $endTime) {
                                          $query->where(function ($q) use ($startTime, $endTime) {
                                              $q->where('start_time', '<=', $startTime)
                                                ->where('end_time', '>=', $startTime);
                                          })->orWhere(function ($q) use ($startTime, $endTime) {
                                              $q->where('start_time', '<=', $endTime)
                                                ->where('end_time', '>=', $endTime);
                                          })->orWhere(function ($q) use ($startTime, $endTime) {
                                              $q->where('start_time', '>=', $startTime)
                                                ->where('end_time', '<=', $endTime);
                                          });
                                      })
                                     ->count();
        
        if ($conflictingTimetable > 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Book this room.
     *
     * @param  array  $bookingData
     * @return RoomBooking|bool
     */
    public function book($bookingData)
    {
        // Check if room is available for the requested time
        if (!$this->isAvailableFor($bookingData['booking_date'], $bookingData['start_time'], $bookingData['end_time'])) {
            return false;
        }
        
        // Create booking
        return $this->bookings()->create([
            'booking_date' => $bookingData['booking_date'],
            'start_time' => $bookingData['start_time'],
            'end_time' => $bookingData['end_time'],
            'booked_by' => $bookingData['booked_by'],
            'purpose' => $bookingData['purpose'] ?? null,
            'attendees_count' => $bookingData['attendees_count'] ?? null,
            'status' => 'confirmed',
            'remarks' => $bookingData['remarks'] ?? null,
        ]);
    }

    /**
     * Set room status.
     *
     * @param  string  $status
     * @param  string|null  $remarks
     * @return bool
     */
    public function setStatus($status, $remarks = null)
    {
        $oldStatus = $this->status;
        $this->status = $status;
        
        if ($remarks) {
            $this->description = ($this->description ? $this->description . "\n" : '') . 
                                "Status changed from {$oldStatus} to {$status} on " . now() . ". {$remarks}";
        }
        
        return $this->save();
    }

    /**
     * Add a facility to the room.
     *
     * @param  string  $facility
     * @return bool
     */
    public function addFacility($facility)
    {
        $facilities = $this->facilities ?? [];
        
        if (!in_array($facility, $facilities)) {
            $facilities[] = $facility;
            $this->facilities = $facilities;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Remove a facility from the room.
     *
     * @param  string  $facility
     * @return bool
     */
    public function removeFacility($facility)
    {
        $facilities = $this->facilities ?? [];
        
        if (($key = array_search($facility, $facilities)) !== false) {
            unset($facilities[$key]);
            $this->facilities = array_values($facilities); // Re-index the array
            return $this->save();
        }
        
        return false;
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'available':
                return 'badge-success';
            case 'occupied':
                return 'badge-info';
            case 'under-maintenance':
                return 'badge-warning';
            case 'reserved':
                return 'badge-primary';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get the full location description.
     *
     * @return string
     */
    public function getFullLocationAttribute()
    {
        $parts = [];
        
        if ($this->building) {
            $parts[] = $this->building->name;
        }
        
        if ($this->floor) {
            $parts[] = "Floor {$this->floor}";
        }
        
        $parts[] = "Room {$this->room_number}";
        
        return implode(', ', $parts);
    }
}