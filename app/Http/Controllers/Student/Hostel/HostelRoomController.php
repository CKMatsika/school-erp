<?php
// File: app/Http/Controllers/Student/Hostel/HostelRoomController.php

namespace App\Http\Controllers\Student\Hostel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student\HostelHouse;
use App\Models\Student\HostelRoom;

class HostelRoomController extends Controller
{
    /**
     * Display a listing of hostel rooms.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rooms = HostelRoom::with('house')->withCount('beds')->get();
        
        return view('student.hostel.rooms.index', compact('rooms'));
    }
    
    /**
     * Show the form for creating a new hostel room.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $houses = HostelHouse::where('status', 'active')->pluck('name', 'id');
        
        return view('student.hostel.rooms.create', compact('houses'));
    }
    
    /**
     * Store a newly created hostel room in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:hostel_houses,id',
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance',
        ]);
        
        // Check if room number is unique for this house
        $exists = HostelRoom::where('house_id', $validated['house_id'])
            ->where('room_number', $validated['room_number'])
            ->exists();
            
        if ($exists) {
            return back()->withInput()->withErrors([
                'room_number' => 'Room number already exists in this house.'
            ]);
        }
        
        HostelRoom::create($validated);
        
        return redirect()->route('student.hostel.rooms.index')
            ->with('success', 'Hostel room created successfully.');
    }
    
    /**
     * Display the specified hostel room.
     *
     * @param  \App\Models\Student\HostelRoom  $room
     * @return \Illuminate\Http\Response
     */
    public function show(HostelRoom $room)
    {
        $room->load(['house', 'beds']);
        
        return view('student.hostel.rooms.show', compact('room'));
    }
    
    /**
     * Show the form for editing the specified hostel room.
     *
     * @param  \App\Models\Student\HostelRoom  $room
     * @return \Illuminate\Http\Response
     */
    public function edit(HostelRoom $room)
    {
        $houses = HostelHouse::where('status', 'active')->pluck('name', 'id');
        
        return view('student.hostel.rooms.edit', compact('room', 'houses'));
    }
    
    /**
     * Update the specified hostel room in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student\HostelRoom  $room
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HostelRoom $room)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:hostel_houses,id',
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance',
        ]);
        
        // Check if room number is unique for this house
        $exists = HostelRoom::where('house_id', $validated['house_id'])
            ->where('room_number', $validated['room_number'])
            ->where('id', '!=', $room->id)
            ->exists();
            
        if ($exists) {
            return back()->withInput()->withErrors([
                'room_number' => 'Room number already exists in this house.'
            ]);
        }
        
        $room->update($validated);
        
        return redirect()->route('student.hostel.rooms.index')
            ->with('success', 'Hostel room updated successfully.');
    }
    
    /**
     * Remove the specified hostel room from storage.
     *
     * @param  \App\Models\Student\HostelRoom  $room
     * @return \Illuminate\Http\Response
     */
    public function destroy(HostelRoom $room)
    {
        // Check if room has beds
        if ($room->beds()->count() > 0) {
            return back()->with('error', 'Cannot delete room with beds. Delete beds first.');
        }
        
        $room->delete();
        
        return redirect()->route('student.hostel.rooms.index')
            ->with('success', 'Hostel room deleted successfully.');
    }
}