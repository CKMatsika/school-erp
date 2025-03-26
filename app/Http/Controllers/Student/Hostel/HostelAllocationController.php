<?php
// File: app/Http/Controllers/Student/Hostel/HostelAllocationController.php

namespace App\Http\Controllers\Student\Hostel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student\HostelHouse;
use App\Models\Student\HostelRoom;
use App\Models\Student\HostelBed;
use App\Models\Student\HostelAllocation;
use App\Models\Student\StudentProfile;
use App\Models\AcademicYear;

class HostelAllocationController extends Controller
{
    /**
     * Display a listing of hostel allocations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $house = $request->input('house');
        $search = $request->input('search');
        
        $query = HostelAllocation::with(['student', 'bed.room.house']);
        
        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }
        
        // Filter by house
        if ($house) {
            $query->whereHas('bed.room', function($q) use ($house) {
                $q->where('house_id', $house);
            });
        }
        
        // Search by student name or admission number
        if ($search) {
            $query->whereHas('student', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }
        
        $allocations = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get houses for filter dropdown
        $houses = HostelHouse::pluck('name', 'id');
        
        return view('student.hostel.allocations.index', compact(
            'allocations', 
            'houses', 
            'status', 
            'house', 
            'search'
        ));
    }
    
    /**
     * Show the form for creating a new hostel allocation.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get available beds
        $beds = HostelBed::where('status', 'available')
            ->with('room.house')
            ->get()
            ->map(function($bed) {
                return [
                    'id' => $bed->id,
                    'name' => "House: {$bed->room->house->name} - Room: {$bed->room->room_number} - Bed: {$bed->bed_number}"
                ];
            })
            ->pluck('name', 'id');
            
        // Get students without current allocations
        $students = StudentProfile::whereDoesntHave('currentHostelAllocation')
            ->orderBy('name')
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => "{$student->name} ({$student->admission_number})"
                ];
            })
            ->pluck('name', 'id');
            
        // Get academic years
        $academicYears = AcademicYear::orderBy('start_date', 'desc')
            ->pluck('name', 'id');
            
        return view('student.hostel.allocations.create', compact(
            'beds',
            'students',
            'academicYears'
        ));
    }
    
    /**
     * Store a newly created hostel allocation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'bed_id' => 'required|exists:hostel_beds,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'allocation_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:allocation_date',
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);
        
        // Check if student already has active allocation
        $existingAllocation = HostelAllocation::where('student_id', $validated['student_id'])
            ->where('is_current', true)
            ->first();
            
        if ($existingAllocation) {
            return back()->withInput()->withErrors([
                'student_id' => 'Student already has an active hostel allocation.'
            ]);
        }
        
        // Check if bed is available
        $bed = HostelBed::findOrFail($validated['bed_id']);
        if ($bed->status !== 'available') {
            return back()->withInput()->withErrors([
                'bed_id' => 'Selected bed is not available.'
            ]);
        }
        
        // Start transaction
        \DB::beginTransaction();
        
        try {
            // Create allocation
            $allocation = HostelAllocation::create([
                'student_id' => $validated['student_id'],
                'bed_id' => $validated['bed_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'allocation_date' => $validated['allocation_date'],
                'expiry_date' => $validated['expiry_date'],
                'is_current' => true,
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);
            
            // Update bed status if allocation is approved
            if ($validated['status'] === 'approved') {
                $bed->update(['status' => 'occupied']);
                
                // Update student's boarding status
                $student = StudentProfile::findOrFail($validated['student_id']);
                $student->update([
                    'boarding_status' => 'boarder',
                    'current_hostel_allocation_id' => $allocation->id
                ]);
            }
            
            \DB::commit();
            
            return redirect()->route('student.hostel.allocations.index')
                ->with('success', 'Hostel allocation created successfully.');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return back()->with('error', 'Failed to create allocation: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified hostel allocation.
     *
     * @param  \App\Models\Student\HostelAllocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function show(HostelAllocation $allocation)
    {
        $allocation->load(['student', 'bed.room.house', 'academicYear']);
        
        return view('student.hostel.allocations.show', compact('allocation'));
    }
    
    /**
     * Show the form for editing the specified hostel allocation.
     *
     * @param  \App\Models\Student\HostelAllocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function edit(HostelAllocation $allocation)
    {
        $allocation->load(['student', 'bed.room.house']);
        
        // Get available beds plus current bed
        $beds = HostelBed::where(function($query) use ($allocation) {
                $query->where('status', 'available')
                      ->orWhere('id', $allocation->bed_id);
            })
            ->with('room.house')
            ->get()
            ->map(function($bed) {
                return [
                    'id' => $bed->id,
                    'name' => "House: {$bed->room->house->name} - Room: {$bed->room->room_number} - Bed: {$bed->bed_number}"
                ];
            })
            ->pluck('name', 'id');
            
        // Get academic years
        $academicYears = AcademicYear::orderBy('start_date', 'desc')
            ->pluck('name', 'id');
            
        return view('student.hostel.allocations.edit', compact(
            'allocation',
            'beds',
            'academicYears'
        ));
    }
    
    /**
     * Update the specified hostel allocation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student\HostelAllocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HostelAllocation $allocation)
    {
        $validated = $request->validate([
            'bed_id' => 'required|exists:hostel_beds,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'allocation_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:allocation_date',
            'status' => 'required|in:pending,approved,rejected,ended',
            'notes' => 'nullable|string',
        ]);
        
        // Check if bed is available if it's different from current
        if ($validated['bed_id'] != $allocation->bed_id) {
            $bed = HostelBed::findOrFail($validated['bed_id']);
            if ($bed->status !== 'available') {
                return back()->withInput()->withErrors([
                    'bed_id' => 'Selected bed is not available.'
                ]);
            }
        }
        
        // Start transaction
        \DB::beginTransaction();
        
        try {
            // Handle bed status changes
            $oldBed = HostelBed::findOrFail($allocation->bed_id);
            $newBed = HostelBed::findOrFail($validated['bed_id']);
            $statusChanged = $validated['status'] !== $allocation->status;
            $bedChanged = $validated['bed_id'] != $allocation->bed_id;
            
            // Update allocation
            $validated['is_current'] = in_array($validated['status'], ['pending', 'approved']);
            $allocation->update($validated);
            
            // Handle bed changes
            if ($bedChanged) {
                $oldBed->update(['status' => 'available']);
                
                if ($validated['status'] === 'approved') {
                    $newBed->update(['status' => 'occupied']);
                }
            } else if ($statusChanged) {
                // Status changed but bed is the same
                if ($validated['status'] === 'approved') {
                    $newBed->update(['status' => 'occupied']);
                } else {
                    $newBed->update(['status' => 'available']);
                }
            }
            
            // Update student's boarding status
            $student = $allocation->student;
            if ($validated['status'] === 'approved') {
                $student->update([
                    'boarding_status' => 'boarder',
                    'current_hostel_allocation_id' => $allocation->id
                ]);
            } else if (in_array($validated['status'], ['rejected', 'ended'])) {
                // Check if student has other active allocations
                $otherActiveAllocations = HostelAllocation::where('student_id', $student->id)
                    ->where('id', '!=', $allocation->id)
                    ->where('is_current', true)
                    ->first();
                    
                if (!$otherActiveAllocations) {
                    $student->update([
                        'boarding_status' => 'day_scholar',
                        'current_hostel_allocation_id' => null
                    ]);
                }
            }
            
            \DB::commit();
            
            return redirect()->route('student.hostel.allocations.index')
                ->with('success', 'Hostel allocation updated successfully.');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return back()->with('error', 'Failed to update allocation: ' . $e->getMessage());
        }
    }
    
    /**
     * End the specified hostel allocation.
     *
     * @param  \App\Models\Student\HostelAllocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function endAllocation(HostelAllocation $allocation)
    {
        if ($allocation->endAllocation()) {
            return redirect()->route('student.hostel.allocations.index')
                ->with('success', 'Hostel allocation ended successfully.');
        }
        
        return back()->with('error', 'Failed to end allocation.');
    }
    
    /**
     * Remove the specified hostel allocation from storage.
     *
     * @param  \App\Models\Student\HostelAllocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(HostelAllocation $allocation)
    {
        // Only allow deletion of pending or rejected allocations
        if (!in_array($allocation->status, ['pending', 'rejected'])) {
            return back()->with('error', 'Cannot delete active or ended allocations.');
        }
        
        $allocation->delete();
        
        return redirect()->route('student.hostel.allocations.index')
            ->with('success', 'Hostel allocation deleted successfully.');
    }
}\Response
     */
    public function create()
    {
        // Get available beds
        $beds = HostelBed::where('status', 'available')
            ->with('room.house')
            ->get()
            ->map(function($bed) {
                return [
                    'id' => $bed->id,
                    'name' => "House: {$bed->room->house->name} - Room: {$bed->room->room_number} - Bed: {$bed->bed_number}"
                ];
            })
            ->pluck('name', 'id');
            
        // Get students without current allocations
        $students = StudentProfile::whereDoesntHave('currentHostelAllocation')
            ->orderBy('name')
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => "{$student->name} ({$student->admission_number})"
                ];
            })
            ->pluck('name', 'id');
            
        // Get academic years
        $academicYears = AcademicYear::orderBy('start_date', 'desc')
            ->pluck('name', 'id');
            
        return view('student.hostel.allocations.create', compact(
            'beds',
            'students',
            'academicYears'
        ));
    }
    
    /**
     * Store a newly created hostel allocation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'bed_id' => 'required|exists:hostel_beds,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'allocation_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:allocation_date',
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);
        
        // Check if student already has active allocation
        $existingAllocation = HostelAllocation::where('student_id', $validated['student_id'])
            ->where('is_current', true)
            ->first();
            
        if ($existingAllocation) {
            return back()->withInput()->withErrors([
                'student_id' => 'Student already has an active hostel allocation.'
            ]);
        }
        
        // Check if bed is available
        $bed = HostelBed::findOrFail($validated['bed_id']);
        if ($bed->status !== 'available') {
            return back()->withInput()->withErrors([
                'bed_id' => 'Selected bed is not available.'
            ]);
        }
        
        // Start transaction
        \DB::beginTransaction();
        
        try {
            // Create allocation
            $allocation = HostelAllocation::create([
                'student_id' => $validated['student_id'],
                'bed_id' => $validated['bed_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'allocation_date' => $validated['allocation_date'],
                'expiry_date' => $validated['expiry_date'],
                'is_current' => true,
                'status' => $validated['status'],
                'notes' => $validated['notes