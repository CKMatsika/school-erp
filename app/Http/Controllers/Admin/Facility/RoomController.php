<?php

namespace App\Http\Controllers\Admin\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Room;

class RoomController extends Controller
{
    /**
     * Display a listing of the facility rooms.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $rooms = Room::latest()->paginate(15);
        return view('admin.facility.room.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new room.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.facility.room.create');
    }

    /**
     * Store a newly created room in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'room_number' => 'required|string|max:20|unique:rooms',
            'floor' => 'required|string|max:20',
            'building' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'room_type' => 'required|string|max:50',
            'is_ac' => 'required|boolean',
            'has_projector' => 'required|boolean',
            'description' => 'nullable|string',
            'status' => 'required|string|in:available,occupied,under_maintenance,reserved',
        ]);

        Room::create($request->all());

        return redirect()->route('admin.facility.room.index')
            ->with('success', 'Room created successfully.');
    }

    /**
     * Display the specified room.
     *
     * @param  \App\Models\Admin\Room  $room
     * @return \Illuminate\View\View
     */
    public function show(Room $room)
    {
        return view('admin.facility.room.show', compact('room'));
    }

    /**
     * Show the form for editing the specified room.
     *
     * @param  \App\Models\Admin\Room  $room
     * @return \Illuminate\View\View
     */
    public function edit(Room $room)
    {
        return view('admin.facility.room.edit', compact('room'));
    }

    /**
     * Update the specified room in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Room  $room
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Room $room)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'room_number' => 'required|string|max:20|unique:rooms,room_number,'.$room->id,
            'floor' => 'required|string|max:20',
            'building' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'room_type' => 'required|string|max:50',
            'is_ac' => 'required|boolean',
            'has_projector' => 'required|boolean',
            'description' => 'nullable|string',
            'status' => 'required|string|in:available,occupied,under_maintenance,reserved',
        ]);

        $room->update($request->all());

        return redirect()->route('admin.facility.room.index')
            ->with('success', 'Room updated successfully.');
    }

    /**
     * Remove the specified room from storage.
     *
     * @param  \App\Models\Admin\Room  $room
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('admin.facility.room.index')
            ->with('success', 'Room deleted successfully.');
    }
}