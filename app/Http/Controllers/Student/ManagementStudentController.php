<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\StudentProfile;
use App\Models\Student\Guardian;
use App\Models\Student\Enrollment;
use App\Models\Student\AcademicYear;
use App\Models\Student\HostelHouse;
use App\Models\Student\HostelRoom;
use App\Models\Student\HostelBed;
use App\Models\TimetableSchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ManagementStudentController extends Controller
{
    public function index()
    {
        $students = StudentProfile::where('school_id', auth()->user()->school_id)
                            ->with(['class', 'currentEnrollment'])
                            ->orderBy('last_name')
                            ->orderBy('first_name')
                            ->paginate(20);
        
        // Get classes for filtering
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)
                                     ->orderBy('name')
                                     ->get();
        
        return view('student.studentprofile.index', compact('students', 'classes'));
    }
 
    public function create()
    {
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)->get();
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                                ->orderByDesc('start_date')
                                ->get();
    
        // Get hostel houses to display available capacity
        $houses = HostelHouse::where('school_id', auth()->user()->school_id)
                            ->where('is_active', true)
                            ->get();
                            
        foreach ($houses as $house) {
            $totalBeds = HostelBed::whereHas('room', function($query) use ($house) {
                                $query->where('hostel_house_id', $house->id);
                            })
                            ->where('is_active', true)
                            ->count();
                            
            $occupiedBeds = HostelBed::whereHas('room', function($query) use ($house) {
                                $query->where('hostel_house_id', $house->id);
                            })
                            ->where('status', 'occupied')
                            ->count();
                            
            $house->available_beds = $totalBeds - $occupiedBeds;
        }
    
        return view('student.studentprofile.create', compact('classes', 'academicYears', 'houses'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'admission_number' => 'required|string|max:50|unique:student_profiles',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'class_id' => 'required|exists:timetable_school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'is_boarder' => 'nullable|boolean',
            'boarding_preferences' => 'nullable|string',
        ]);
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Create student profile
            $student = StudentProfile::create([
                'school_id' => auth()->user()->school_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'other_names' => $request->other_names,
                'admission_number' => $request->admission_number,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'is_boarder' => $request->has('is_boarder'),
                'blood_group' => $request->blood_group,
                'medical_conditions' => $request->medical_conditions,
                'allergies' => $request->allergies,
                'additional_info' => $request->additional_info,
            ]);
            
            // Create enrollment
            $student->enrollments()->create([
                'school_id' => auth()->user()->school_id,
                'class_id' => $request->class_id,
                'academic_year_id' => $request->academic_year_id,
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
            
            // Handle boarding status
            if ($request->has('is_boarder') && $request->is_boarder) {
                // Check if there's capacity in the hostels
                $totalCapacity = HostelHouse::where('school_id', auth()->user()->school_id)
                                          ->where('is_active', true)
                                          ->where(function($query) use ($request) {
                                              $query->where('gender', $request->gender)
                                                   ->orWhere('gender', 'mixed');
                                          })
                                          ->sum('capacity');
                                          
                $totalOccupied = HostelBed::whereHas('room.house', function($query) use ($request) {
                                         $query->where('school_id', auth()->user()->school_id)
                                              ->where(function($q) use ($request) {
                                                  $q->where('gender', $request->gender)
                                                   ->orWhere('gender', 'mixed');
                                              });
                                     })
                                     ->where('status', 'occupied')
                                     ->count();
                
                $availableSpaces = $totalCapacity - $totalOccupied;
                
                if ($availableSpaces <= 0) {
                    // No capacity, can't be a boarder
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Cannot accept boarding student. All hostels for ' . $request->gender . ' students are at full capacity.');
                }
                
                // Set as pending allocation
                $student->update([
                    'is_boarder' => true,
                    'boarding_status' => 'pending',
                    'boarding_preferences' => $request->boarding_preferences
                ]);
            }
            
            DB::commit();
            
            if ($request->has('is_boarder') && $request->is_boarder) {
                return redirect()->route('student.students.show', $student)
                    ->with('success', 'Student added successfully. Please allocate a bed from the hostel management section.');
            } else {
                return redirect()->route('student.students.show', $student)
                    ->with('success', 'Student added successfully.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error adding student: ' . $e->getMessage());
        }
    }
    
    public function show(StudentProfile $student)
    {
        $student->load(['class', 'currentEnrollment', 'enrollments.academicYear', 'guardians']);
        
        // Load boarding information if student is a boarder
        if ($student->is_boarder) {
            $student->load(['currentHostelAllocation.bed.room.house']);
        }
        
        return view('student.studentprofile.show', compact('student'));
    }
    
    public function edit(StudentProfile $student)
    {
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)->get();
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                                ->orderByDesc('start_date')
                                ->get();
        
        // Get hostel houses to display available capacity
        $houses = HostelHouse::where('school_id', auth()->user()->school_id)
                            ->where('is_active', true)
                            ->get();
                            
        foreach ($houses as $house) {
            $totalBeds = HostelBed::whereHas('room', function($query) use ($house) {
                                $query->where('hostel_house_id', $house->id);
                            })
                            ->where('is_active', true)
                            ->count();
                            
            $occupiedBeds = HostelBed::whereHas('room', function($query) use ($house) {
                                $query->where('hostel_house_id', $house->id);
                            })
                            ->where('status', 'occupied')
                            ->count();
                            
            $house->available_beds = $totalBeds - $occupiedBeds;
        }
        
        return view('student.studentprofile.edit', compact('student', 'classes', 'academicYears', 'houses'));
    }
    
    public function update(Request $request, StudentProfile $student)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'admission_number' => 'required|string|max:50|unique:student_profiles,admission_number,' . $student->id,
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'is_boarder' => 'nullable|boolean',
            'boarding_preferences' => 'nullable|string',
        ]);
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Check if boarding status has changed
            $wasBoarder = $student->is_boarder;
            $isBoarder = $request->has('is_boarder');
            
            // Update student profile
            $student->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'other_names' => $request->other_names,
                'admission_number' => $request->admission_number,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'blood_group' => $request->blood_group,
                'medical_conditions' => $request->medical_conditions,
                'allergies' => $request->allergies,
                'additional_info' => $request->additional_info,
                'boarding_preferences' => $request->boarding_preferences
            ]);
            
            // Handle boarding status changes
            if ($wasBoarder != $isBoarder) {
                if ($isBoarder) {
                    // Checking to boarder status
                    // Check if there's capacity in the hostels
                    $totalCapacity = HostelHouse::where('school_id', auth()->user()->school_id)
                                          ->where('is_active', true)
                                          ->where(function($query) use ($request) {
                                              $query->where('gender', $request->gender)
                                                   ->orWhere('gender', 'mixed');
                                          })
                                          ->sum('capacity');
                                          
                    $totalOccupied = HostelBed::whereHas('room.house', function($query) use ($request) {
                                             $query->where('school_id', auth()->user()->school_id)
                                                  ->where(function($q) use ($request) {
                                                      $q->where('gender', $request->gender)
                                                       ->orWhere('gender', 'mixed');
                                                  });
                                         })
                                         ->where('status', 'occupied')
                                         ->count();
                    
                    $availableSpaces = $totalCapacity - $totalOccupied;
                    
                    if ($availableSpaces <= 0) {
                        // No capacity, can't be a boarder
                        DB::rollBack();
                        return back()->withInput()->with('error', 'Cannot convert to boarding student. All hostels for ' . $request->gender . ' students are at full capacity.');
                    }
                    
                    // Set as pending allocation
                    $student->update([
                        'is_boarder' => true,
                        'boarding_status' => 'pending'
                    ]);
                } else {
                    // Changing from boarder to day scholar
                    // End any current allocation
                    if ($student->hasActiveHostelAllocation()) {
                        $student->endCurrentAllocation();
                    }
                    
                    $student->update([
                        'is_boarder' => false,
                        'boarding_status' => 'day_scholar'
                    ]);
                }
            } else {
                // No change in boarding status, just update is_boarder field
                $student->update([
                    'is_boarder' => $isBoarder
                ]);
            }
            
            DB::commit();
            
            if (!$wasBoarder && $isBoarder) {
                return redirect()->route('student.students.show', $student)
                    ->with('success', 'Student updated successfully. Please allocate a bed from the hostel management section.');
            } else {
                return redirect()->route('student.students.show', $student)
                    ->with('success', 'Student updated successfully.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating student: ' . $e->getMessage());
        }
    }
    
    public function destroy(StudentProfile $student)
    {
        // Check if student can be deleted (e.g., no active enrollments)
        if ($student->enrollments()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete student with active enrollment.');
        }
        
        try {
            // Begin transaction
            DB::beginTransaction();
            
            // End any hostel allocations
            if ($student->is_boarder && $student->hasActiveHostelAllocation()) {
                $student->endCurrentAllocation();
            }
            
            // Delete all hostel allocation history
            if ($student->hostelAllocations) {
                foreach ($student->hostelAllocations as $allocation) {
                    $allocation->delete();
                }
            }
            
            // Delete related records
            $student->guardians()->detach();
            $student->enrollments()->delete();
            
            // Delete student
            $student->delete();
            
            DB::commit();
            
            return redirect()->route('student.students.index')
                ->with('success', 'Student deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error deleting student: ' . $e->getMessage());
        }
    }

    /**
     * Filter students by various criteria including boarding status.
     */
    public function filter(Request $request)
    {
        $query = StudentProfile::where('school_id', auth()->user()->school_id);
        
        // Apply filters
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('admission_number', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->gender) {
            $query->where('gender', $request->gender);
        }
        
        if ($request->has('is_boarder')) {
            $query->where('is_boarder', true);
            
            if ($request->boarding_status) {
                $query->where('boarding_status', $request->boarding_status);
            }
            
            if ($request->hostel_house_id) {
                $query->whereHas('currentHostelAllocation.bed.room', function($q) use ($request) {
                    $q->where('hostel_house_id', $request->hostel_house_id);
                });
            }
        }
        
        $students = $query->with(['class', 'currentEnrollment'])
                        ->orderBy('last_name')
                        ->orderBy('first_name')
                        ->paginate(20);
        
        // Get data for filters
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)
                                     ->orderBy('name')
                                     ->get();
                                     
        $houses = HostelHouse::where('school_id', auth()->user()->school_id)
                            ->orderBy('name')
                            ->get();
        
        return view('student.studentprofile.index', compact('students', 'classes', 'houses', 'request'));
    }
    
    /**
     * Bulk allocate beds to multiple students.
     */
    public function bulkAllocate()
    {
        $pendingStudents = StudentProfile::where('school_id', auth()->user()->school_id)
                                        ->where('is_boarder', true)
                                        ->where(function($query) {
                                            $query->where('boarding_status', 'pending')
                                                 ->orWhereNull('boarding_status');
                                        })
                                        ->orderBy('gender')
                                        ->orderBy('last_name')
                                        ->orderBy('first_name')
                                        ->get();
        
        // Group by gender
        $maleStudents = $pendingStudents->where('gender', 'male');
        $femaleStudents = $pendingStudents->where('gender', 'female');
        $otherStudents = $pendingStudents->where('gender', 'other');
        
        // Get houses with available beds
        $houses = HostelHouse::where('school_id', auth()->user()->school_id)
                            ->where('is_active', true)
                            ->with(['rooms' => function($query) {
                                $query->where('is_active', true)
                                     ->with(['beds' => function($query) {
                                         $query->where('status', 'available')
                                              ->where('is_active', true);
                                     }]);
                            }])
                            ->get();
        
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('school_id', auth()->user()->school_id)
                                          ->where('is_current', true)
                                          ->first();
        
        return view('student.studentprofile.bulk-allocate', compact(
            'maleStudents',
            'femaleStudents',
            'otherStudents',
            'houses',
            'currentAcademicYear'
        ));
    }
    
    /**
     * Process bulk allocation of beds.
     */
    public function processBulkAllocate(Request $request)
    {
        $request->validate([
            'allocations' => 'required|array',
            'allocations.*.student_id' => 'required|exists:student_profiles,id',
            'allocations.*.bed_id' => 'required|exists:hostel_beds,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        
        $successCount = 0;
        $failureCount = 0;
        
        foreach ($request->allocations as $allocation) {
            $student = StudentProfile::find($allocation['student_id']);
            $bed = HostelBed::find($allocation['bed_id']);
            
            if (!$student || !$bed) {
                $failureCount++;
                continue;
            }
            
            // Try to allocate the bed
            $result = $student->allocateBed($bed, $request->academic_year_id);
            
            if ($result) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }
        
        $message = "Allocation completed. Successfully allocated $successCount beds.";
        if ($failureCount > 0) {
            $message .= " Failed to allocate $failureCount beds.";
        }
        
        return redirect()->route('student.hostel.allocations.index')
                        ->with('success', $message);
    }
}