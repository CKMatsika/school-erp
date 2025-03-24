<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\Student;
use App\Models\Student\Enrollment;
use App\Models\Student\AcademicYear;
use App\Models\TimetableSchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassPromotionController extends Controller
{
    public function index()
    {
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)->get();
        
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                                    ->orderByDesc('start_date')
                                    ->get();
        
        return view('student.promotions.index', compact('classes', 'academicYears'));
    }
    
    public function preview(Request $request)
    {
        $request->validate([
            'from_class_id' => 'required|exists:timetable_school_classes,id',
            'to_class_id' => 'required|exists:timetable_school_classes,id',
            'current_academic_year_id' => 'required|exists:academic_years,id',
            'new_academic_year_id' => 'required|exists:academic_years,id',
        ]);
        
        $fromClass = TimetableSchoolClass::findOrFail($request->from_class_id);
        $toClass = TimetableSchoolClass::findOrFail($request->to_class_id);
        $currentAcademicYear = AcademicYear::findOrFail($request->current_academic_year_id);
        $newAcademicYear = AcademicYear::findOrFail($request->new_academic_year_id);
        
        // Get students in the from class with active enrollments in the current academic year
        $students = Student::where('class_id', $request->from_class_id)
                          ->where('status', 'active')
                          ->whereHas('enrollments', function ($query) use ($request) {
                              $query->where('academic_year_id', $request->current_academic_year_id)
                                    ->where('status', 'active');
                          })
                          ->get();
        
        return view('student.promotions.preview', compact('students', 'fromClass', 'toClass', 'currentAcademicYear', 'newAcademicYear'));
    }
    
    public function promote(Request $request)
    {
        $request->validate([
            'from_class_id' => 'required|exists:timetable_school_classes,id',
            'to_class_id' => 'required|exists:timetable_school_classes,id',
            'current_academic_year_id' => 'required|exists:academic_years,id',
            'new_academic_year_id' => 'required|exists:academic_years,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'promotion_date' => 'required|date',
        ]);
        
        $fromClass = TimetableSchoolClass::findOrFail($request->from_class_id);
        $toClass = TimetableSchoolClass::findOrFail($request->to_class_id);
        $currentAcademicYear = AcademicYear::findOrFail($request->current_academic_year_id);
        $newAcademicYear = AcademicYear::findOrFail($request->new_academic_year_id);
        
        DB::beginTransaction();
        try {
            $promotedCount = 0;
            
            foreach ($request->student_ids as $studentId) {
                $student = Student::findOrFail($studentId);
                
                // Update current enrollment to completed
                Enrollment::where('student_id', $studentId)
                        ->where('academic_year_id', $currentAcademicYear->id)
                        ->where('status', 'active')
                        ->update(['status' => 'completed']);
                
                // Create new enrollment
                Enrollment::create([
                    'student_id' => $studentId,
                    'class_id' => $toClass->id,
                    'academic_year_id' => $newAcademicYear->id,
                    'status' => 'active',
                    'enrollment_date' => $request->promotion_date,
                    'notes' => "Promoted from {$fromClass->name} to {$toClass->name}",
                ]);
                
                // Update student class
                $student->update(['class_id' => $toClass->id]);
                
                // Check if this is a graduation class
                if ($request->has('is_graduation') && $request->is_graduation) {
                    $student->update([
                        'status' => 'graduated',
                        'graduation_date' => $request->promotion_date,
                    ]);
                }
                
                $promotedCount++;
            }
            
            DB::commit();
            
            return redirect()->route('student.promotions.index')
                ->with('success', "{$promotedCount} students promoted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error promoting students: ' . $e->getMessage());
        }
    }
    
    public function graduate()
    {
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)->get();
        
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                                    ->orderByDesc('start_date')
                                    ->get();
        
        return view('student.promotions.graduate', compact('classes', 'academicYears'));
    }
    
    public function previewGraduation(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:timetable_school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        
        $class = TimetableSchoolClass::findOrFail($request->class_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        
        // Get students in the class with active enrollments in the academic year
        $students = Student::where('class_id', $request->class_id)
                          ->where('status', 'active')
                          ->whereHas('enrollments', function ($query) use ($request) {
                              $query->where('academic_year_id', $request->academic_year_id)
                                    ->where('status', 'active');
                          })
                          ->get();
        
        return view('student.promotions.preview-graduation', compact('students', 'class', 'academicYear'));
    }
    
    public function processGraduation(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:timetable_school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'graduation_date' => 'required|date',
        ]);
        
        $class = TimetableSchoolClass::findOrFail($request->class_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        
        DB::beginTransaction();
        try {
            $graduatedCount = 0;
            
            foreach ($request->student_ids as $studentId) {
                $student = Student::findOrFail($studentId);
                
                // Update current enrollment to completed
                Enrollment::where('student_id', $studentId)
                        ->where('academic_year_id', $academicYear->id)
                        ->where('status', 'active')
                        ->update(['status' => 'completed']);
                
                // Update student status
                $student->update([
                    'status' => 'graduated',
                    'graduation_date' => $request->graduation_date,
                ]);
                
                $graduatedCount++;
            }
            
            DB::commit();
            
            return redirect()->route('student.promotions.graduate')
                ->with('success', "{$graduatedCount} students graduated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error processing graduation: ' . $e->getMessage());
        }
    }
}