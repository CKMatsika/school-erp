<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\Enrollment;
use App\Models\Student\Student;
use App\Models\Student\AcademicYear;
use App\Models\TimetableSchoolClass;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                                    ->orderByDesc('start_date')
                                    ->get();
        
        $currentYear = AcademicYear::where('school_id', auth()->user()->school_id)
                                   ->where('is_current', true)
                                   ->first();
        
        $enrollments = [];
        if ($currentYear) {
            $enrollments = Enrollment::where('academic_year_id', $currentYear->id)
                                    ->with(['student', 'class'])
                                    ->get();
        }
        
        return view('student.enrollments.index', compact('academicYears', 'currentYear', 'enrollments'));
    }

    public function showByYear(AcademicYear $academicYear)
    {
        $enrollments = Enrollment::where('academic_year_id', $academicYear->id)
                                ->with(['student', 'class'])
                                ->get();
        
        return view('student.enrollments.by_year', compact('academicYear', 'enrollments'));
    }

    public function create()
    {
        $students = Student::where('school_id', auth()->user()->school_id)
                          ->where('status', 'active')
                          ->orderBy('last_name')
                          ->orderBy('first_name')
                          ->get();
        
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)
                                     ->get();
        
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                                    ->orderByDesc('start_date')
                                    ->get();
        
        return view('student.enrollments.create', compact('students', 'classes', 'academicYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:timetable_school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,completed,withdrawn,transferred',
        ]);

        try {
            // Create enrollment
            $enrollment = Enrollment::create([
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
                'academic_year_id' => $request->academic_year_id,
                'enrollment_date' => $request->enrollment_date,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);
            
            // Update student class if enrollment is active
            if ($request->status === 'active') {
                Student::where('id', $request->student_id)
                      ->update(['class_id' => $request->class_id]);
            }
            
            return redirect()->route('student.enrollments.index')
                ->with('success', 'Enrollment created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating enrollment: ' . $e->getMessage());
        }
    }

    public function edit(Enrollment $enrollment)
    {
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)
                                     ->get();
        
        return view('student.enrollments.edit', compact('enrollment', 'classes'));
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'class_id' => 'required|exists:timetable_school_classes,id',
            'status' => 'required|in:active,completed,withdrawn,transferred',
        ]);

        try {
            // Update enrollment
            $enrollment->update([
                'class_id' => $request->class_id,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);
            
            // Update student class if enrollment is active
            if ($request->status === 'active') {
                Student::where('id', $enrollment->student_id)
                      ->update(['class_id' => $request->class_id]);
            }
            
            return redirect()->route('student.enrollments.index')
                ->with('success', 'Enrollment updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating enrollment: ' . $e->getMessage());
        }
    }

    public function destroy(Enrollment $enrollment)
    {
        // Don't allow deleting the enrollment record
        return back()->with('error', 'Enrollment records cannot be deleted. Mark as inactive instead.');
    }
}