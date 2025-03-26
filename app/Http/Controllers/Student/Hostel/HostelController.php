<?php

namespace App\Http\Controllers\Student\Hostel;

use App\Http\Controllers\Controller;
use App\Models\Student\HostelHouse;
use App\Models\Student\HostelRoom;
use App\Models\Student\HostelBed;
use App\Models\Student\HostelAllocation;
use App\Models\Student\StudentProfile;
use App\Models\Student\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HostelController extends Controller
{
    /**
     * Display a dashboard with hostel statistics.
     */
    public function dashboard()
    {
        $houses = HostelHouse::where('school_id', auth()->user()->school_id)
                             ->withCount('rooms')
                             ->get();
        
        $totalCapacity = $houses->sum('capacity');
        $totalOccupied = HostelBed::where('status', 'occupied')
                                 ->whereHas('room.house', function ($query) {
                                     $query->where('school_id', auth()->user()->school_id);
                                 })
                                 ->count();
        $totalVacant = $totalCapacity - $totalOccupied;
        
        // Calculate occupancy percentage
        $occupancyPercentage = ($totalCapacity > 0) 
                               ? round(($totalOccupied / $totalCapacity) * 100, 2)
                               : 0;
        
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('school_id', auth()->user()->school_id)
                                           ->where('is_current', true)
                                           ->first();
        
        // Get recent allocations
        $recentAllocations = HostelAllocation::with(['student', 'bed.room.house'])
                                             ->whereHas('bed.room.house', function ($query) {
                                                 $query->where('school_id', auth()->user()->school_id);
                                             })
                                             ->orderByDesc('created_at')
                                             ->limit(10)
                                             ->get();
        
        // Get current boarding students count
        $totalBoarders = StudentProfile::where('school_id', auth()->user()->school_id)
                                      ->where('is_boarder', true)
                                      ->count();
        
        // Get pending allocation students
        $pendingAllocations = StudentProfile::where('school_id', auth()->user()->school_id)
                                          ->where('is_boarder', true)
                                          ->where('boarding_status', 'pending')
                                          ->count();
        
        return view('student.hostel.dashboard', compact(
            'houses',
            'totalCapacity',
            'totalOccupied',
            'totalVacant',
            'occupancyPercentage',
            'recentAllocations',
            'currentAcademicYear',
            'totalBoarders',
            'pendingAllocations'
        ));
    }
    
    /**
     * List all hostel houses with occupancy stats.
     */
    public function houses()
    {
        $houses = HostelHouse::where('school_id', auth()->user()->school_id)
                             ->withCount(['rooms'])
                             ->orderBy('name')
                             ->paginate(10);
        
        return view('student.hostel.houses.index', compact('houses'));
    }
    
    /**
     * Show form to create a new hostel house.
     */
    public function createHouse()
    {
        return view('student.hostel.houses.create');
    }
    
    /**
     * Store a new hostel house.
     */
    public function storeHouse(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:hostel_houses,code',
            'gender' => 'required|in:male,female,mixed',
            'capacity' => 'required|integer|min:1',
            'floor_count' => 'required|integer|min:1',
            'warden_name' => 'nullable|string|max:255',
            'warden_contact' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
        
        $house = HostelHouse::create([
            'school_id' => auth()->user()->school_id,
            'name' => $request->name,
            'code' => $request->code,
            'gender' => $request->gender,
            'capacity' => $request->capacity,
            'floor_count' => $request->floor_count,
            'warden_name' => $request->warden_name,
            'warden_contact' => $request->warden_contact,
            'description' => $request->description,
            'location' => $request->location,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('student.hostel.houses.show', $house)
                         ->with('success', 'Hostel house created successfully.');
    }
    
    /**
     * View a specific hostel house with its rooms.
     */
    public function showHouse(HostelHouse $house)
    {
        $house->load('rooms.beds');
        
        $roomsCount = $house->rooms->count();
        $bedsCount = $house->rooms->sum(function($room) {
            return $room->beds->count();
        });
        
        $occupiedBeds = $house->rooms->sum(function($room) {
            return $room->beds->where('status', 'occupied')->count();
        });
        
        $availableBeds = $bedsCount - $occupiedBeds;
        
        $occupancyRate = $bedsCount > 0 ? round(($occupiedBeds / $bedsCount) * 100, 2) : 0;
        
        $rooms = $house->rooms()->paginate(10);
        
        return view('student.hostel.houses.show', compact(
            'house',
            'rooms',
            'roomsCount',
            'bedsCount',
            'occupiedBeds',
            'availableBeds',
            'occupancyRate'
        ));
    }
    
    /**
     * Edit a hostel house.
     */
    public function editHouse(HostelHouse $house)
    {
        return view('student.hostel.houses.edit', compact('house'));
    }
    
    /**
     * Update a hostel house.
     */
    public function updateHouse(Request $request, HostelHouse $house)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:hostel_houses,code,' . $house->id,
            'gender' => 'required|in:male,female,mixed',
            'capacity' => 'required|integer|min:1',
            'floor_count' => 'required|integer|min:1',
            'warden_name' => 'nullable|string|max:255',
            'warden_contact' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
        
        $house->update([
            'name' => $request->name,
            'code' => $request->code,
            'gender' => $request->gender,
            'capacity' => $request->capacity,
            'floor_count' => $request->floor_count,
            'warden_name' => $request->warden_name,
            'warden_contact' => $request->warden_contact,
            'description' => $request->description,
            'location' => $request->location,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('student.hostel.houses.show', $house)
                         ->with('success', 'Hostel house updated successfully.');
    }
    
    /**
     * Delete a hostel house.
     */
    public function destroyHouse(HostelHouse $house)
    {
        // Check if there are any active allocations
        $hasActiveAllocations = $house->rooms()
            ->whereHas('beds', function ($query) {
                $query->where('status', 'occupied');
            })
            ->exists();
        
        if ($hasActiveAllocations) {
            return back()->with('error', 'Cannot delete house with active allocations.');
        }
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Delete beds first
            foreach ($house->rooms as $room) {
                $room->beds()->delete();
            }
            
            // Delete rooms
            $house->rooms()->delete();
            
            // Delete house
            $house->delete();
            
            DB::commit();
            
            return redirect()->route('student.hostel.houses.index')
                             ->with('success', 'Hostel house and associated rooms/beds deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error deleting hostel house: ' . $e->getMessage());
        }
    }
    
    /**
     * List all rooms in a house.
     */
    public function rooms(HostelHouse $house)
    {
        $rooms = $house->rooms()
                      ->withCount(['beds', 'beds as occupied_count' => function ($query) {
                          $query->where('status', 'occupied');
                      }])
                      ->orderBy('room_number')
                      ->paginate(20);
        
        return view('student.hostel.rooms.index', compact('house', 'rooms'));
    }
    
    /**
     * Show form to create a new room.
     */
    public function createRoom(HostelHouse $house)
    {
        return view('student.hostel.rooms.create', compact('house'));
    }
    
    /**
     * Store a new room.
     */
    public function storeRoom(Request $request, HostelHouse $house)
    {
        $request->validate([
            'room_number' => 'required|string|max:50',
            'floor' => 'nullable|string|max:50',
            'room_type' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
            'auto_create_beds' => 'nullable|boolean',
        ]);
        
        // Check if room number is unique within this house
        $exists = $house->rooms()->where('room_number', $request->room_number)->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['room_number' => 'Room number must be unique within this house.']);
        }
        
        DB::beginTransaction();
        
        try {
            // Create the room
            $room = $house->rooms()->create([
                'room_number' => $request->room_number,
                'floor' => $request->floor,
                'room_type' => $request->room_type,
                'capacity' => $request->capacity,
                'is_active' => $request->has('is_active'),
                'description' => $request->description,
            ]);
            
            // Auto-create beds if requested
            if ($request->has('auto_create_beds')) {
                for ($i = 1; $i <= $request->capacity; $i++) {
                    $room->beds()->create([
                        'bed_number' => $i,
                        'status' => 'available',
                        'is_active' => true,
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('student.hostel.rooms.show', $room)
                             ->with('success', 'Room created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                         ->with('error', 'Error creating room: ' . $e->getMessage());
        }
    }
    
    /**
     * Show a specific room with its beds.
     */
    public function showRoom(HostelRoom $room)
    {
        $room->load(['house', 'beds']);
        
        $totalBeds = $room->beds->count();
        $occupiedBeds = $room->beds->where('status', 'occupied')->count();
        $availableBeds = $totalBeds - $occupiedBeds;
        
        $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 2) : 0;
        
        // Get students in this room
        $students = StudentProfile::whereHas('currentBed', function ($query) use ($room) {
                                      $query->where('hostel_room_id', $room->id);
                                  })
                                  ->get();
        
        return view('student.hostel.rooms.show', compact(
            'room',
            'totalBeds',
            'occupiedBeds',
            'availableBeds',
            'occupancyRate',
            'students'
        ));
    }
    
    /**
     * Edit a room.
     */
    public function editRoom(HostelRoom $room)
    {
        $room->load('house');
        
        return view('student.hostel.rooms.edit', compact('room'));
    }
    
    /**
     * Update a room.
     */
    public function updateRoom(Request $request, HostelRoom $room)
    {
        $request->validate([
            'room_number' => 'required|string|max:50',
            'floor' => 'nullable|string|max:50',
            'room_type' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);
        
        // Check if room number is unique within this house
        $exists = HostelRoom::where('hostel_house_id', $room->hostel_house_id)
                           ->where('room_number', $request->room_number)
                           ->where('id', '!=', $room->id)
                           ->exists();
                           
        if ($exists) {
            return back()->withInput()->withErrors(['room_number' => 'Room number must be unique within this house.']);
        }
        
        $room->update([
            'room_number' => $request->room_number,
            'floor' => $request->floor,
            'room_type' => $request->room_type,
            'capacity' => $request->capacity,
            'is_active' => $request->has('is_active'),
            'description' => $request->description,
        ]);
        
        return redirect()->route('student.hostel.rooms.show', $room)
                         ->with('success', 'Room updated successfully.');
    }
    
    /**
     * Delete a room.
     */
    public function destroyRoom(HostelRoom $room)
    {
        // Check for active allocations
        $hasActiveAllocations = $room->beds()
                                    ->where('status', 'occupied')
                                    ->exists();
        
        if ($hasActiveAllocations) {
            return back()->with('error', 'Cannot delete room with active allocations.');
        }
        
        DB::beginTransaction();
        
        try {
            // Delete beds first
            $room->beds()->delete();
            
            // Delete room
            $room->delete();
            
            DB::commit();
            
            return redirect()->route('student.hostel.rooms.index', $room->hostel_house_id)
                             ->with('success', 'Room and associated beds deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error deleting room: ' . $e->getMessage());
        }
    }
    
    /**
     * List all beds in a room.
     */
    public function beds(HostelRoom $room)
    {
        $room->load('house');
        
        $beds = $room->beds()
                    ->orderBy('bed_number')
                    ->paginate(50);
        
        return view('student.hostel.beds.index', compact('room', 'beds'));
    }
    
    /**
     * Show form to create a new bed.
     */
    public function createBed(HostelRoom $room)
    {
        $room->load('house');
        
        return view('student.hostel.beds.create', compact('room'));
    }
    
    /**
     * Store a new bed.
     */
    public function storeBed(Request $request, HostelRoom $room)
    {
        $request->validate([
            'bed_number' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        
        // Check if bed number is unique within this room
        $exists = $room->beds()->where('bed_number', $request->bed_number)->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['bed_number' => 'Bed number must be unique within this room.']);
        }
        
        $bed = $room->beds()->create([
            'bed_number' => $request->bed_number,
            'status' => 'available',
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('student.hostel.beds.index', $room)
                         ->with('success', 'Bed created successfully.');
    }
    
    /**
     * Edit a bed.
     */
    public function editBed(HostelBed $bed)
    {
        $bed->load('room.house');
        
        return view('student.hostel.beds.edit', compact('bed'));
    }
    
    /**
     * Update a bed.
     */
    public function updateBed(Request $request, HostelBed $bed)
    {
        $request->validate([
            'bed_number' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        
        // Check if bed number is unique within this room
        $exists = HostelBed::where('hostel_room_id', $bed->hostel_room_id)
                          ->where('bed_number', $request->bed_number)
                          ->where('id', '!=', $bed->id)
                          ->exists();
                          
        if ($exists) {
            return back()->withInput()->withErrors(['bed_number' => 'Bed number must be unique within this room.']);
        }
        
        $bed->update([
            'bed_number' => $request->bed_number,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('student.hostel.beds.index', $bed->hostel_room_id)
                         ->with('success', 'Bed updated successfully.');
    }
    
    /**
     * Delete a bed.
     */
    public function destroyBed(HostelBed $bed)
    {
        // Check for active allocation
        if ($bed->status === 'occupied') {
            return back()->with('error', 'Cannot delete bed that is currently occupied.');
        }
        
        $bed->delete();
        
        return redirect()->route('student.hostel.beds.index', $bed->hostel_room_id)
                         ->with('success', 'Bed deleted successfully.');
    }
    
    /**
     * List all students who need bed allocation.
     */
    public function allocations()
    {
        $pendingStudents = StudentProfile::where('school_id', auth()->user()->school_id)
                                        ->where('is_boarder', true)
                                        ->where(function($query) {
                                            $query->where('boarding_status', 'pending')
                                                 ->orWhereNull('boarding_status');
                                        })
                                        ->orderBy('last_name')
                                        ->orderBy('first_name')
                                        ->paginate(20);
        
        $currentAllocations = HostelAllocation::with(['student', 'bed.room.house'])
                                            ->whereHas('bed.room.house', function ($query) {
                                                $query->where('school_id', auth()->user()->school_id);
                                            })
                                            ->where('is_current', true)
                                            ->orderBy('allocation_date', 'desc')
                                            ->paginate(20);
        
        return view('student.hostel.allocations.index', compact('pendingStudents', 'currentAllocations'));
    }
    
    /**
     * Show form to allocate a bed to a student.
     */
    public function allocateBed(StudentProfile $student)
    {
        // Get available beds grouped by house and room
        $houses = HostelHouse::where('school_id', auth()->user()->school_id)
                            ->where('is_active', true)
                            ->where(function($query) use ($student) {
                                // Gender match
                                $query->where('gender', $student->gender)
                                     ->orWhere('gender', 'mixed');
                            })
                            ->with(['rooms' => function($query) {
                                $query->where('is_active', true)
                                     ->withCount(['beds' => function($query) {
                                         $query->where('status', 'available')
                                              ->where('is_active', true);
                                     }]);
                            }])
                            ->get();
        
        // Filter out houses/rooms with no available beds
        $houses = $houses->filter(function($house) {
            $house->rooms = $house->rooms->filter(function($room) {
                return $room->beds_count > 0;
            });
            
            return $house->rooms->count() > 0;
        });
        
        // Get current academic year for default selection
        $currentAcademicYear = AcademicYear::where('school_id', auth()->user()->school_id)
                                          ->where('is_current', true)
                                          ->first();
        
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                                    ->orderByDesc('start_date')
                                    ->get();
        
        return view('student.hostel.allocations.create', compact(
            'student',
            'houses',
            'currentAcademicYear',
            'academicYears'
        ));
    }
    
    /**
     * Store a new bed allocation.
     */
    public function storeAllocation(Request $request, StudentProfile $student)
    {
        $request->validate([
            'hostel_bed_id' => 'required|exists:hostel_beds,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'notes' => 'nullable|string',
        ]);
        
        $bed = HostelBed::findOrFail($request->hostel_bed_id);
        
        // Check if bed is available
        if ($bed->status !== 'available' || !$bed->is_active) {
            return back()->with('error', 'The selected bed is not available for allocation.');
        }
        
        // Check gender restrictions
        $house = $bed->room->house;
        if ($house->gender !== 'mixed' && $house->gender !== $student->gender) {
            return back()->with('error', 'Gender mismatch: This house is for ' . $house->gender . ' students only.');
        }
        
        // Attempt to allocate the bed
        $allocation = $student->allocateBed($bed, $request->academic_year_id, $request->notes);
        
        if (!$allocation) {
            return back()->with('error', 'Failed to allocate bed. Please try again.');
        }
        
        return redirect()->route('student.hostel.allocations.index')
                         ->with('success', 'Bed allocated successfully to ' . $student->full_name);
    }
    
    /**
     * Show form to deallocate a bed.
     */
    public function endAllocation(StudentProfile $student)
    {
        if (!$student->hasActiveHostelAllocation()) {
            return redirect()->route('student.hostel.allocations.index')
                             ->with('error', 'Student does not have an active hostel allocation.');
        }
        
        return view('student.hostel.allocations.end', compact('student'));
    }
    
    /**
     * Process bed deallocation.
     */
    public function processEndAllocation(Request $request, StudentProfile $student)
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);
        
        $success = $student->endCurrentAllocation();
        
        if (!$success) {
            return back()->with('error', 'Failed to end allocation. Please try again.');
        }
        
        return redirect()->route('student.hostel.allocations.index')
                         ->with('success', 'Hostel allocation ended successfully for ' . $student->full_name);
    }
    
    /**
     * View allocation history for a student.
     */
    public function allocationHistory(StudentProfile $student)
    {
        $allocations = $student->hostelAllocations()
                              ->with(['bed.room.house', 'academicYear'])
                              ->orderByDesc('allocation_date')
                              ->paginate(20);
        
        return view('student.hostel.allocations.history', compact('student', 'allocations'));
    }
    
    /**
     * View occupancy report.
     */
    public function occupancyReport(Request $request)
    {
        $houses = HostelHouse::where('school_id', auth()->user()->school_id)
                             ->withCount('rooms')
                             ->get();
        
        foreach ($houses as $house) {
            $house->total_beds = $house->rooms()
                                     ->withCount('beds')
                                     ->get()
                                     ->sum('beds_count');
                                     
            $house->occupied_beds = HostelBed::whereHas('room', function($query) use ($house) {
                                           $query->where('hostel_house_id', $house->id);
                                       })
                                       ->where('status', 'occupied')
                                       ->count();
                                       
            $house->occupancy_rate = $house->total_beds > 0 
                                   ? round(($house->occupied_beds / $house->total_beds) * 100, 2)
                                   : 0;
        }
        
        // Filter by gender
        $genderFilter = $request->gender ?? 'all';
        if ($genderFilter !== 'all') {
            $houses = $houses->filter(function($house) use ($genderFilter) {
                return $house->gender === $genderFilter || $house->gender === 'mixed';
            });
        }
        
        // Calculate totals
        $totalBeds = $houses->sum('total_beds');
        $totalOccupied = $houses->sum('occupied_beds');
        $totalRate = $totalBeds > 0 ? round(($totalOccupied / $totalBeds) * 100, 2) : 0;
        
        return view('student.hostel.reports.occupancy', compact(
            'houses',
            'totalBeds',
            'totalOccupied',
            'totalRate',
            'genderFilter'
        ));
    }
    
    /**
     * View allocation report.
     */
    public function allocationReport(Request $request)
    {
        $academicYearId = $request->academic_year_id;
        $houseId = $request->hostel_house_id;
        $gender = $request->gender;
        
        $query = StudentProfile::where('school_id', auth()->user()->school_id)
                               ->where('is_boarder', true)
                               ->with(['currentHostelAllocation.bed.room.house']);
        
        // Apply filters
        if ($academicYearId) {
            $query->whereHas('currentHostelAllocation', function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }
        
        if ($houseId) {
            $query->whereHas('currentHostelAllocation.bed.room.house', function($q) use ($houseId) {
                $q->where('id', $houseId);
            });
        }
        
        if ($gender) {
            $query->where('gender', $gender);
        }
        
        $students = $query->orderBy('last_name')
                         ->orderBy('first_name')
                         ->paginate(50);
        
        // Get filter data
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                                    ->orderByDesc('start_date')
                                    ->get();
                                    
        $houses = HostelHouse::where('school_id', auth()->user()->school_id)
                            ->orderBy('name')
                            ->get();
        
        return view('student.hostel.reports.allocations', compact(
            'students',
            'academicYears',
            'houses',
            'academicYearId',
            'houseId',
            'gender'
        ));
    }
}