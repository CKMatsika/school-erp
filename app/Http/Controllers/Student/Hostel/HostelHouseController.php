<?php
// File: app/Http/Controllers/Student/Hostel/HostelHouseController.php

namespace App\Http\Controllers\Student\Hostel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student\HostelHouse;
use App\Models\Student\HostelRoom;

class HostelHouseController extends Controller
{
    /**
     * Display a listing of hostel houses.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $houses = HostelHouse::withCount('rooms', 'beds')->get();
        
        return view('student.hostel.houses.index', compact('houses'));
    }
    
    /**
     * Show the form for creating a new hostel house.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('student.hostel.houses.create');
    }
    
    /**
     * Store a newly created hostel house in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:hostel_houses',
            'gender' => 'required|in:male,female,mixed',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance',
        ]);
        
        HostelHouse::create($validated);
        
        return redirect()->route('student.hostel.houses.index')
            ->with('success', 'Hostel house created successfully.');
    }
    
    /**
     * Display the specified hostel house.
     *
     * @param  \App\Models\Student\HostelHouse  $house
     * @return \Illuminate\Http\Response
     */
    public function show(HostelHouse $house)
    {
        $house->load('rooms.beds');
        
        return view('student.hostel.houses.show', compact('house'));
    }
    
    /**
     * Show the form for editing the specified hostel house.
     *
     * @param  \App\Models\Student\HostelHouse  $house
     * @return \Illuminate\Http\Response
     */
    public function edit(HostelHouse $house)
    {
        return view('student.hostel.houses.edit', compact('house'));
    }
    
    /**
     * Update the specified hostel house in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student\HostelHouse  $house
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HostelHouse $house)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:hostel_houses,code,' . $house->id,
            'gender' => 'required|in:male,female,mixed',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance',
        ]);
        
        $house->update($validated);
        
        return redirect()->route('student.hostel.houses.index')
            ->with('success', 'Hostel house updated successfully.');
    }
    
    /**
     * Remove the specified hostel house from storage.
     *
     * @param  \App\Models\Student\HostelHouse  $house
     * @return \Illuminate\Http\Response
     */
    public function destroy(HostelHouse $house)
    {
        // Check if house has rooms
        if ($house->rooms()->count() > 0) {
            return back()->with('error', 'Cannot delete house with rooms. Delete rooms first.');
        }
        
        $house->delete();
        
        return redirect()->route('student.hostel.houses.index')
            ->with('success', 'Hostel house deleted successfully.');
    }
}