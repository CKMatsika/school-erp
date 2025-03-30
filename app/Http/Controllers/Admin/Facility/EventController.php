<?php

namespace App\Http\Controllers\Admin\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Event;
use App\Models\Admin\Room;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Event::with('room');
        
        // Apply filters
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('end_time', '<=', $request->end_date);
        }
        
        if ($request->has('room_id') && $request->room_id) {
            $query->where('room_id', $request->room_id);
        }
        
        if ($request->has('event_type') && $request->event_type) {
            $query->where('event_type', $request->event_type);
        }
        
        $events = $query->latest()->paginate(10);
        $rooms = Room::all();
        
        return view('admin.facility.event.index', compact('events', 'rooms'));
    }

    /**
     * Show the form for creating a new event.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $rooms = Room::where('status', 'available')->get();
        return view('admin.facility.event.create', compact('rooms'));
    }

    /**
     * Store a newly created event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'event_type' => 'required|string|max:50',
            'organizer' => 'required|string|max:255',
            'expected_attendees' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string',
            'status' => 'required|string|in:scheduled,ongoing,completed,cancelled',
        ]);

        // Check if room is available for the selected time slot
        $roomAvailable = $this->checkRoomAvailability(
            $request->room_id,
            $request->start_time,
            $request->end_time
        );

        if (!$roomAvailable) {
            return back()->withInput()->withErrors([
                'room_id' => 'The selected room is not available for the specified time slot.'
            ]);
        }

        Event::create($request->all());

        return redirect()->route('admin.facility.event.index')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified event.
     *
     * @param  \App\Models\Admin\Event  $event
     * @return \Illuminate\View\View
     */
    public function show(Event $event)
    {
        $event->load('room');
        return view('admin.facility.event.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     *
     * @param  \App\Models\Admin\Event  $event
     * @return \Illuminate\View\View
     */
    public function edit(Event $event)
    {
        $rooms = Room::all();
        return view('admin.facility.event.edit', compact('event', 'rooms'));
    }

    /**
     * Update the specified event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Event  $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'event_type' => 'required|string|max:50',
            'organizer' => 'required|string|max:255',
            'expected_attendees' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string',
            'status' => 'required|string|in:scheduled,ongoing,completed,cancelled',
        ]);

        // Check if room is available for the selected time slot (excluding current event)
        $roomAvailable = $this->checkRoomAvailability(
            $request->room_id,
            $request->start_time,
            $request->end_time,
            $event->id
        );

        if (!$roomAvailable) {
            return back()->withInput()->withErrors([
                'room_id' => 'The selected room is not available for the specified time slot.'
            ]);
        }

        $event->update($request->all());

        return redirect()->route('admin.facility.event.index')
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified event from storage.
     *
     * @param  \App\Models\Admin\Event  $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('admin.facility.event.index')
            ->with('success', 'Event deleted successfully.');
    }
    
    /**
     * Display the calendar view of events.
     *
     * @return \Illuminate\View\View
     */
    public function calendar()
    {
        $events = Event::with('room')->get();
        $formattedEvents = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_time,
                'end' => $event->end_time,
                'color' => $this->getEventColor($event->status),
                'url' => route('admin.facility.event.show', $event),
                'description' => $event->description,
                'location' => $event->room->name,
            ];
        });
        
        return view('admin.facility.event.calendar', compact('formattedEvents'));
    }
    
    /**
     * Get color code based on event status.
     *
     * @param string $status
     * @return string
     */
    private function getEventColor($status)
    {
        $colors = [
            'scheduled' => '#3788d8',
            'ongoing' => '#28a745',
            'completed' => '#6c757d',
            'cancelled' => '#dc3545',
        ];
        
        return $colors[$status] ?? '#3788d8';
    }
    
    /**
     * Check if room is available for the given time slot.
     *
     * @param int $roomId
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeEventId
     * @return bool
     */
    private function checkRoomAvailability($roomId, $startTime, $endTime, $excludeEventId = null)
    {
        $startTime = Carbon::parse($startTime);
        $endTime = Carbon::parse($endTime);
        
        $query = Event::where('room_id', $roomId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                // Check if there's any overlap with existing events
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($query) use ($startTime, $endTime) {
                      $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                  });
            });
            
        // Exclude current event when updating
        if ($excludeEventId) {
            $query->where('id', '!=', $excludeEventId);
        }
        
        $conflictingEvents = $query->count();
        
        return $conflictingEvents === 0;
    }
}