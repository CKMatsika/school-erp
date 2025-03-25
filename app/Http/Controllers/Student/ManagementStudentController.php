<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\StudentProfile;  // Correct import path
use App\Models\Student\Guardian;
use App\Models\Student\Enrollment;
use App\Models\Student\AcademicYear;
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
        
        return view('student.studentprofile.index', compact('students'));
    }
 
    public function create()
    {
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)->get();
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                                ->orderByDesc('start_date')
                                ->get();
    
        return view('student.studentprofile.create', compact('classes', 'academicYears'));
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
            'is_boarder' => 'nullable|boolean',
        ]);
        
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
        
        return redirect()->route('student.students.show', $student)
            ->with('success', 'Student added successfully.');
    }
    
    public function show(StudentProfile $student)
    {
        $student->load(['class', 'currentEnrollment', 'enrollments.academicYear', 'guardians']);
        
        return view('student.studentprofile.show', compact('student'));
    }
    
    public function edit(StudentProfile $student)
    {
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)->get();
        
        return view('student.studentprofile.edit', compact('student', 'classes'));
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
        ]);
        
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
            'is_boarder' => $request->has('is_boarder'),
            'blood_group' => $request->blood_group,
            'medical_conditions' => $request->medical_conditions,
            'allergies' => $request->allergies,
            'additional_info' => $request->additional_info,
        ]);
        
        return redirect()->route('student.students.show', $student)
            ->with('success', 'Student updated successfully.');
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
            
            // Delete related records
            $student->guardians()->detach();  // Changed from delete() to detach() for many-to-many
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
}