<?php
// File: app/Http/Controllers/Student/Hostel/HostelBedController.php

namespace App\Http\Controllers\Student\Hostel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student\HostelHouse;
use App\Models\Student\HostelRoom;
use App\Models\Student\HostelBed;

class HostelBedController extends Controller
{
    /**
     * Display a listing of hostel beds.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $beds = HostelBed::with(['room.house', 'currentAllocation.student'])
            ->paginate(20);
        
        return view('student.hostel.beds.index', compact('beds'));
    }
    
    /**
     * Show the form for creating a new hostel bed.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $rooms = HostelRoom::with('house')
            ->where('status', 'active')
            ->get()
            ->map(function($room) {
                return [
                    'id' => $room->id,
                    'name' => "House: {$room->house->name} - Room: {$room->room_number}"
                ];
            })
            ->pluck('name', 'id');
        
        return view('student.hostel.beds.create', compact('rooms'));
    }
    
    /**
     * Store a newly created hostel bed in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:hostel_rooms,id',
            'bed_number' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance',
        ]);
        
        // Check if bed number is unique for this room
        $exists = HostelBed::where('room_id', $validated['room_id'])
            ->where('bed_number', $validated['bed_number'])
            ->exists();
            
        if ($exists) {
            return back()->withInput()->withErrors([
                'bed_number' => 'Bed number already exists in this room.'
            ]);
        }
        
        HostelBed::create($validated);
        
        return redirect()->route('student.hostel.beds.index')
            ->with('success', 'Hostel bed created successfully.');
    }
    
    /**
     * Display the specified hostel bed.
     *
     * @param  \App\Models\Student\HostelBed  $bed
     * @return \Illuminate\Http\Response
     */
    public function show(HostelBed $bed)
    {
        $bed->load(['room.house', 'currentAllocation.student', 'allocations.student']);
        
        return view('student.hostel.beds.show', compact('bed'));
    }
    
    /**
     * Show the form for editing the specified hostel bed.
     *
     * @param  \App\Models\Student\HostelBed  $bed
     * @return \Illuminate\Http\Response
     */
    public function edit(HostelBed $bed)
    {
        $rooms = HostelRoom::with('house')
            ->where('status', 'active')
            ->get()
            ->map(function($room) {
                return [
                    'id' => $room->id,
                    'name' => "House: {$room->house->name} - Room: {$room->room_number}"
                ];
            })
            ->pluck('name', 'id');
        
        return view('student.hostel.beds.edit', compact('bed', 'rooms'));
    }
    
    /**
     * Update the specified hostel bed in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student\HostelBed  $bed
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HostelBed $bed)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:hostel_rooms,id',
            'bed_number' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance',
        ]);
        
        // Check if bed number is unique for this room
        $exists = HostelBed::where('room_id', $validated['room_id'])
            ->where('bed_number', $validated['bed_number'])
            ->where('id', '!=', $bed->id)
            ->exists();
            
        if ($exists) {
            return back()->withInput()->withErrors([
                'bed_number' => 'Bed number already exists in this room.'
            ]);
        }
        
        $bed->update($validated);
        
        return redirect()->route('student.hostel.beds.index')
            ->with('success', 'Hostel bed updated successfully.');
    }
    
    /**
     * Remove the specified hostel bed from storage.
     *
     * @param  \App\Models\Student\HostelBed  $bed
     * @return \Illuminate\Http\Response
     */
    public function destroy(HostelBed $bed)
    {
        // Check if bed has current allocation
        if ($bed->currentAllocation) {
            return back()->with('error', 'Cannot delete bed with active allocation.');
        }
        
        $bed->delete();
        
        return redirect()->route('student.hostel.beds.index')
            ->with('success', 'Hostel bed deleted successfully.');
    }
    
    /**
     * Generate multiple beds for a room.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generateBeds(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:hostel_rooms,id',
            'quantity' => 'required|integer|min:1|max:100',
            'prefix' => 'nullable|string|max:10',
        ]);
        
        $room = HostelRoom::findOrFail($validated['room_id']);
        $prefix = $validated['prefix'] ?? '';
        $quantity = $validated['quantity'];
        
        // Get existing bed numbers for this room
        $existingBeds = HostelBed::where('room_id', $room->id)
            ->pluck('bed_number')
            ->toArray();
        
        // Start transaction
        \DB::beginTransaction();
        
        try {
            for ($i = 1; $i <= $quantity; $i++) {
                $bedNumber = $prefix . $i;
                
                // Skip if bed number already exists
                if (in_array($bedNumber, $existingBeds)) {
                    continue;
                }
                
                HostelBed::create([
                    'room_id' => $room->id,
                    'bed_number' => $bedNumber,
                    'status' => 'available',
                ]);
            }
            
            \DB::commit();
            
            return redirect()->route('student.hostel.beds.index')
                ->with('success', 'Beds generated successfully.');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return back()->with('error', 'Failed to generate beds: ' . $e->getMessage());
        }
    }
}