<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\AcademicYear;
use App\Models\Student\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                                    ->orderByDesc('start_date')
                                    ->get();
        
        return view('student.academic-years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('student.academic-years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean',
            'terms' => 'nullable|array',
            'terms.*.name' => 'required|string|max:255',
            'terms.*.start_date' => 'required|date',
            'terms.*.end_date' => 'required|date|after:terms.*.start_date',
        ]);

        DB::beginTransaction();
        try {
            // If setting as current, unset other current academic years
            if ($request->has('is_current')) {
                AcademicYear::where('school_id', auth()->user()->school_id)
                          ->where('is_current', true)
                          ->update(['is_current' => false]);
            }
            
            // Create academic year
            $academicYear = AcademicYear::create([
                'school_id' => auth()->user()->school_id,
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_current' => $request->has('is_current'),
            ]);
            
            // Create terms if provided
            if ($request->has('terms') && is_array($request->terms)) {
                foreach ($request->terms as $termData) {
                    Term::create([
                        'academic_year_id' => $academicYear->id,
                        'name' => $termData['name'],
                        'start_date' => $termData['start_date'],
                        'end_date' => $termData['end_date'],
                        'is_current' => false, // Set to false initially
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('student.academic-years.show', $academicYear)
                ->with('success', 'Academic year created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Error creating academic year: ' . $e->getMessage());
        }
    }

    public function show(AcademicYear $academicYear)
    {
        $academicYear->load('terms');
        
        $enrollments = $academicYear->enrollments()
                                  ->with(['student', 'class'])
                                  ->get()
                                  ->groupBy('class_id');
        
        $applications = $academicYear->applications()
                                   ->get()
                                   ->groupBy('status');
        
        return view('student.academic-years.show', compact('academicYear', 'enrollments', 'applications'));
    }

    public function edit(AcademicYear $academicYear)
    {
        $academicYear->load('terms');
        
        return view('student.academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // If setting as current, unset other current academic years
            if ($request->has('is_current') && !$academicYear->is_current) {
                AcademicYear::where('school_id', auth()->user()->school_id)
                          ->where('is_current', true)
                          ->update(['is_current' => false]);
            }
            
            // Update academic year
            $academicYear->update([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_current' => $request->has('is_current'),
            ]);
            
            DB::commit();
            
            return redirect()->route('student.academic-years.show', $academicYear)
                ->with('success', 'Academic year updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Error updating academic year: ' . $e->getMessage());
        }
    }

    public function destroy(AcademicYear $academicYear)
    {
        // Don't allow deleting academic years with enrollments or applications
        if ($academicYear->enrollments()->count() > 0 || $academicYear->applications()->count() > 0) {
            return back()->with('error', 'Cannot delete academic year with associated enrollments or applications.');
        }
        
        try {
            // Delete terms
            $academicYear->terms()->delete();
            
            // Delete academic year
            $academicYear->delete();
            
            return redirect()->route('student.academic-years.index')
                ->with('success', 'Academic year deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting academic year: ' . $e->getMessage());
        }
    }
    
    public function setCurrentTerm(Request $request, Term $term)
    {
        DB::beginTransaction();
        try {
            // Unset current term for all terms in the academic year
            Term::where('academic_year_id', $term->academic_year_id)
               ->where('is_current', true)
               ->update(['is_current' => false]);
            
            // Set as current
            $term->update(['is_current' => true]);
            
            // Make sure academic year is set as current
            if (!$term->academicYear->is_current) {
                // Unset other current academic years
                AcademicYear::where('school_id', auth()->user()->school_id)
                          ->where('is_current', true)
                          ->update(['is_current' => false]);
                
                // Set this academic year as current
                $term->academicYear->update(['is_current' => true]);
            }
            
            DB::commit();
            
            return redirect()->route('student.academic-years.show', $term->academicYear)
                ->with('success', 'Current term updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error updating current term: ' . $e->getMessage());
        }
    }
}