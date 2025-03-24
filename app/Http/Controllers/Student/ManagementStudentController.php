<?php

namespace App\Http\Controllers\StudentProfile;

use App\Http\Controllers\Controller;
use App\Models\Student\StudentProfile; // Changed from Student to StudentProfile
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
        $students = StudentProfile::where('school_id', auth()->user()->school_id) // Changed Student to StudentProfile
                            ->with(['class', 'currentEnrollment'])
                            ->orderBy('last_name')
                            ->orderBy('first_name')
                            ->paginate(20);
        
        return view('student.studentprofile.index', compact('students'));
    }

    public function create()
    {
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)->get();
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)->get();
        
        return view('student.studentprofile.create', compact('classes', 'academicYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'admission_number' => 'required|string|max:255|unique:students',
            'class_id' => 'required|exists:timetable_school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'enrollment_date' => 'required|date',
            'photo' => 'nullable|image|max:2048',
            'is_boarder' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('student-photos', 'public');
            }
            
            // Create student
            $student = StudentProfile::create([ // Changed Student to StudentProfile
                'school_id' => auth()->user()->school_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'other_names' => $request->other_names,
                'admission_number' => $request->admission_number,
                'class_id' => $request->class_id,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'photo' => $photoPath,
                'status' => 'active',
                'enrollment_date' => $request->enrollment_date,
                'is_boarder' => $request->has('is_boarder'),
            ]);
            
            // Create enrollment record
            Enrollment::create([
                'student_id' => $student->id,
                'class_id' => $request->class_id,
                'academic_year_id' => $request->academic_year_id,
                'status' => 'active',
                'enrollment_date' => $request->enrollment_date,
                'notes' => 'Initial enrollment',
            ]);
            
            DB::commit();
            
            return redirect()->route('student.studentprofile.show', $student)
                ->with('success', 'Student created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded photo if exists
            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            
            return back()->withInput()
                ->with('error', 'Error creating student: ' . $e->getMessage());
        }
    }

    public function show(StudentProfile $student) // Changed Student to StudentProfile
    {
        $student->load([
            'guardians', 
            'enrollments.academicYear', 
            'enrollments.class', 
            'documents.documentType',
            'invoices',
            'payments'
        ]);
        
        return view('student.studentprofile.show', compact('student'));
    }

    public function edit(StudentProfile $student) // Changed Student to StudentProfile
    {
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)->get();
        
        return view('student.studentprofile.edit', compact('student', 'classes'));
    }

    public function update(Request $request, StudentProfile $student) // Changed Student to StudentProfile
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'admission_number' => 'required|string|max:255|unique:students,admission_number,'.$student->id,
            'class_id' => 'required|exists:timetable_school_classes,id',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'enrollment_date' => 'required|date',
            'photo' => 'nullable|image|max:2048',
            'is_boarder' => 'nullable|boolean',
            'status' => 'required|in:active,inactive,graduated,transferred,withdrawn',
        ]);

        DB::beginTransaction();
        try {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                    Storage::disk('public')->delete($student->photo);
                }
                
                $photoPath = $request->file('photo')->store('student-photos', 'public');
            }
            
            // Update student
            $student->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'other_names' => $request->other_names,
                'admission_number' => $request->admission_number,
                'class_id' => $request->class_id,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'photo' => $request->hasFile('photo') ? $photoPath : $student->photo,
                'status' => $request->status,
                'enrollment_date' => $request->enrollment_date,
                'graduation_date' => $request->graduation_date,
                'is_boarder' => $request->has('is_boarder'),
            ]);
            
            // Update current enrollment if class changed
            $currentEnrollment = $student->currentEnrollment;
            if ($currentEnrollment && $currentEnrollment->class_id != $request->class_id) {
                $currentEnrollment->update([
                    'class_id' => $request->class_id
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('student.studentprofile.show', $student)
                ->with('success', 'Student updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Error updating student: ' . $e->getMessage());
        }
    }

    public function destroy(StudentProfile $student) // Changed Student to StudentProfile
    {
        try {
            // Instead of deleting, mark as inactive
            $student->update(['status' => 'inactive']);
            
            return redirect()->route('student.studentprofile.index')
                ->with('success', 'Student marked as inactive.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error marking student as inactive: ' . $e->getMessage());
        }
    }
}