<?php

namespace App\Http\Controllers\Admin\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\StaffAccommodation;
use App\Models\Admin\AccommodationType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class StaffAccommodationController extends Controller
{
    /**
     * Display a listing of staff accommodations.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = StaffAccommodation::with(['accommodationType', 'currentOccupant']);
        
        // Apply filters
        if ($request->has('accommodation_type_id') && $request->accommodation_type_id) {
            $query->where('accommodation_type_id', $request->accommodation_type_id);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('occupant_id') && $request->occupant_id) {
            $query->where('current_occupant_id', $request->occupant_id);
        }
        
        $accommodations = $query->latest()->paginate(10);
        $accommodationTypes = AccommodationType::all();
        $staff = User::all(); // Assuming you want to filter by staff
        
        return view('admin.facility.staff-accommodation.index', compact('accommodations', 'accommodationTypes', 'staff'));
    }

    /**
     * Show the form for creating a new staff accommodation.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $accommodationTypes = AccommodationType::all();
        $availableStaff = User::whereDoesntHave('currentAccommodation')->get();
        return view('admin.facility.staff-accommodation.create', compact('accommodationTypes', 'availableStaff'));
    }

    /**
     * Store a newly created staff accommodation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'accommodation_type_id' => 'required|exists:accommodation_types,id',
            'room_number' => 'required|string|max:50|unique:staff_accommodations,room_number',
            'current_occupant_id' => 'nullable|exists:users,id',
            'status' => 'required|in:AVAILABLE,OCCUPIED,MAINTENANCE',
            'allocation_date' => 'nullable|date',
            'expected_vacate_date' => 'nullable|date|after_or_equal:allocation_date',
        ]);

        // Check accommodation type capacity
        if ($request->current_occupant_id) {
            $this->checkAccommodationCapacity($request->accommodation_type_id);
        }

        $accommodation = StaffAccommodation::create($request->all());

        return redirect()->route('admin.facility.staff-accommodation.index')
            ->with('success', 'Staff Accommodation created successfully.');
    }

    /**
     * Display the specified staff accommodation.
     *
     * @param  \App\Models\Admin\StaffAccommodation  $staffAccommodation
     * @return \Illuminate\View\View
     */
    public function show(StaffAccommodation $staffAccommodation)
    {
        $staffAccommodation->load(['accommodationType', 'currentOccupant']);
        return view('admin.facility.staff-accommodation.show', compact('staffAccommodation'));
    }

    /**
     * Show the form for editing the specified staff accommodation.
     *
     * @param  \App\Models\Admin\StaffAccommodation  $staffAccommodation
     * @return \Illuminate\View\View
     */
    public function edit(StaffAccommodation $staffAccommodation)
    {
        $accommodationTypes = AccommodationType::all();
        $staff = User::all();
        return view('admin.facility.staff-accommodation.edit', compact('staffAccommodation', 'accommodationTypes', 'staff'));
    }

    /**
     * Update the specified staff accommodation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\StaffAccommodation  $staffAccommodation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, StaffAccommodation $staffAccommodation)
    {
        $request->validate([
            'accommodation_type_id' => 'required|exists:accommodation_types,id',
            'room_number' => 'required|string|max:50|unique:staff_accommodations,room_number,' . $staffAccommodation->id,
            'current_occupant_id' => 'nullable|exists:users,id',
            'status' => 'required|in:AVAILABLE,OCCUPIED,MAINTENANCE',
            'allocation_date' => 'nullable|date',
            'expected_vacate_date' => 'nullable|date|after_or_equal:allocation_date',
        ]);

        // Check accommodation type capacity if occupant is being assigned
        if ($request->current_occupant_id && $request->current_occupant_id !== $staffAccommodation->current_occupant_id) {
            $this->checkAccommodationCapacity($request->accommodation_type_id);
        }

        $staffAccommodation->update($request->all());

        return redirect()->route('admin.facility.staff-accommodation.index')
            ->with('success', 'Staff Accommodation updated successfully.');
    }

    /**
     * Remove the specified staff accommodation from storage.
     *
     * @param  \App\Models\Admin\StaffAccommodation  $staffAccommodation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(StaffAccommodation $staffAccommodation)
    {
        // Prevent deletion if accommodation is occupied
        if ($staffAccommodation->status === 'OCCUPIED') {
            return back()->withErrors(['Cannot delete an occupied accommodation.']);
        }

        $staffAccommodation->delete();

        return redirect()->route('admin.facility.staff-accommodation.index')
            ->with('success', 'Staff Accommodation deleted successfully.');
    }

    /**
     * Allocate accommodation to a staff member.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function allocate(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:users,id',
            'accommodation_id' => 'required|exists:staff_accommodations,id',
        ]);

        try {
            $accommodation = StaffAccommodation::findOrFail($request->accommodation_id);
            
            // Check if staff already has an accommodation
            $existingAccommodation = StaffAccommodation::where('current_occupant_id', $request->staff_id)
                ->where('status', 'OCCUPIED')
                ->first();
            
            if ($existingAccommodation) {
                throw ValidationException::withMessages([
                    'staff_id' => 'Staff member already has an allocated accommodation.',
                ]);
            }

            // Check accommodation availability and capacity
            if ($accommodation->status !== 'AVAILABLE') {
                throw ValidationException::withMessages([
                    'accommodation_id' => 'Selected accommodation is not available.',
                ]);
            }

            $this->checkAccommodationCapacity($accommodation->accommodation_type_id);

            // Allocate accommodation
            $accommodation->update([
                'current_occupant_id' => $request->staff_id,
                'status' => 'OCCUPIED',
                'allocation_date' => now(),
            ]);

            return redirect()->route('admin.facility.staff-accommodation.index')
                ->with('success', 'Accommodation allocated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Vacate a staff accommodation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function vacate(Request $request)
    {
        $request->validate([
            'accommodation_id' => 'required|exists:staff_accommodations,id',
        ]);

        try {
            $accommodation = StaffAccommodation::findOrFail($request->accommodation_id);
            
            if ($accommodation->status !== 'OCCUPIED') {
                throw ValidationException::withMessages([
                    'accommodation_id' => 'Selected accommodation is not occupied.',
                ]);
            }

            $accommodation->update([
                'current_occupant_id' => null,
                'status' => 'AVAILABLE',
                'allocation_date' => null,
                'expected_vacate_date' => null,
            ]);

            return redirect()->route('admin.facility.staff-accommodation.index')
                ->with('success', 'Accommodation vacated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Check accommodation type capacity.
     *
     * @param int $accommodationTypeId
     * @throws \Illuminate\Validation\ValidationException
     */
    private function checkAccommodationCapacity($accommodationTypeId)
    {
        $accommodationType = AccommodationType::findOrFail($accommodationTypeId);
        
        $occupiedCount = StaffAccommodation::where('accommodation_type_id', $accommodationTypeId)
            ->where('status', 'OCCUPIED')
            ->count();
        
        if ($occupiedCount >= $accommodationType->capacity) {
            throw ValidationException::withMessages([
                'accommodation_type_id' => "Maximum capacity for {$accommodationType->name} reached.",
            ]);
        }
    }
}