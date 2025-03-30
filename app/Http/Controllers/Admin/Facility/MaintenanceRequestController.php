<?php

namespace App\Http\Controllers\Admin\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\MaintenanceRequest;
use App\Models\Admin\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MaintenanceRequestController extends Controller
{
    /**
     * Display a listing of the maintenance requests.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = MaintenanceRequest::with(['room', 'requestedBy', 'assignedTo']);
        
        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->has('room_id') && $request->room_id) {
            $query->where('room_id', $request->room_id);
        }
        
        if ($request->has('maintenance_type') && $request->maintenance_type) {
            $query->where('maintenance_type', $request->maintenance_type);
        }
        
        $maintenanceRequests = $query->latest()->paginate(10);
        $rooms = Room::all();
        
        return view('admin.facility.maintenance.index', compact('maintenanceRequests', 'rooms'));
    }

    /**
     * Show the form for creating a new maintenance request.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $rooms = Room::all();
        $maintenanceStaff = User::whereHas('roles', function ($query) {
            $query->where('name', 'maintenance');
        })->get();
        
        return view('admin.facility.maintenance.create', compact('rooms', 'maintenanceStaff'));
    }

    /**
     * Store a newly created maintenance request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'room_id' => 'required|exists:rooms,id',
            'maintenance_type' => 'required|string|max:50',
            'priority' => 'required|string|in:low,medium,high,critical',
            'requested_completion_date' => 'nullable|date|after_or_equal:today',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
        ]);

        $maintenanceRequest = new MaintenanceRequest($request->all());
        $maintenanceRequest->requested_by = Auth::id();
        $maintenanceRequest->request_date = now();
        $maintenanceRequest->save();

        // If room status needs to be updated to 'under_maintenance'
        if ($request->has('update_room_status') && $request->update_room_status) {
            $room = Room::find($request->room_id);
            $room->status = 'under_maintenance';
            $room->save();
        }

        return redirect()->route('admin.facility.maintenance.index')
            ->with('success', 'Maintenance request created successfully.');
    }

    /**
     * Display the specified maintenance request.
     *
     * @param  \App\Models\Admin\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\View\View
     */
    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['room', 'requestedBy', 'assignedTo']);
        return view('admin.facility.maintenance.show', compact('maintenanceRequest'));
    }

    /**
     * Show the form for editing the specified maintenance request.
     *
     * @param  \App\Models\Admin\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\View\View
     */
    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        $rooms = Room::all();
        $maintenanceStaff = User::whereHas('roles', function ($query) {
            $query->where('name', 'maintenance');
        })->get();
        
        return view('admin.facility.maintenance.edit', compact('maintenanceRequest', 'rooms', 'maintenanceStaff'));
    }

    /**
     * Update the specified maintenance request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'room_id' => 'required|exists:rooms,id',
            'maintenance_type' => 'required|string|max:50',
            'priority' => 'required|string|in:low,medium,high,critical',
            'requested_completion_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'completion_date' => 'nullable|date',
            'completion_notes' => 'nullable|string',
        ]);

        $oldStatus = $maintenanceRequest->status;
        $maintenanceRequest->update($request->all());

        // If status is changed to completed
        if ($oldStatus != 'completed' && $request->status == 'completed') {
            $maintenanceRequest->completion_date = now();
            $maintenanceRequest->save();
            
            // Update room status back to available if it was under maintenance
            $room = Room::find($request->room_id);
            if ($room->status == 'under_maintenance') {
                $room->status = 'available';
                $room->save();
            }
        }

        return redirect()->route('admin.facility.maintenance.index')
            ->with('success', 'Maintenance request updated successfully.');
    }

    /**
     * Remove the specified maintenance request from storage.
     *
     * @param  \App\Models\Admin\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->delete();

        return redirect()->route('admin.facility.maintenance.index')
            ->with('success', 'Maintenance request deleted successfully.');
    }
    
    /**
     * Update the status of a maintenance request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'completion_notes' => 'nullable|required_if:status,completed|string',
        ]);

        $oldStatus = $maintenanceRequest->status;
        $maintenanceRequest->status = $request->status;
        
        if ($request->status == 'completed' && $oldStatus != 'completed') {
            $maintenanceRequest->completion_date = now();
            $maintenanceRequest->completion_notes = $request->completion_notes;
            
            // Update room status if needed
            $room = $maintenanceRequest->room;
            if ($room && $room->status == 'under_maintenance') {
                $room->status = 'available';
                $room->save();
            }
        }
        
        $maintenanceRequest->save();

        return redirect()->route('admin.facility.maintenance.show', $maintenanceRequest)
            ->with('success', 'Maintenance request status updated successfully.');
    }
    
    /**
     * Assign a maintenance request to a staff member.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assign(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $maintenanceRequest->assigned_to = $request->assigned_to;
        $maintenanceRequest->assignment_date = now();
        
        // If status is pending, update to in_progress
        if ($maintenanceRequest->status == 'pending') {
            $maintenanceRequest->status = 'in_progress';
        }
        
        $maintenanceRequest->save();

        return redirect()->route('admin.facility.maintenance.show', $maintenanceRequest)
            ->with('success', 'Maintenance request assigned successfully.');
    }
}