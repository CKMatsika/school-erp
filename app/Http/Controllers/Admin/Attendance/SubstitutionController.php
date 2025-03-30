<?php

namespace App\Http\Controllers\Admin\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Substitution;
use App\Models\Teacher\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use Carbon\Carbon;

class SubstitutionController extends Controller
{
    /**
     * Display a listing of the substitutions.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Substitution::with(['absentTeacher.user', 'substituteTeacher.user', 'class', 'subject']);
        
        // Filters
        if ($request->has('date') && $request->date) {
            $query->whereDate('date', $request->date);
        }
        
        if ($request->has('absent_teacher_id') && $request->absent_teacher_id) {
            $query->where('absent_teacher_id', $request->absent_teacher_id);
        }
        
        if ($request->has('substitute_teacher_id') && $request->substitute_teacher_id) {
            $query->where('substitute_teacher_id', $request->substitute_teacher_id);
        }
        
        if ($request->has('class_id') && $request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        $substitutions = $query->latest()->paginate(15);
        $teachers = Teacher::with('user')->get();
        $classes = SchoolClass::all();
        
        return view('admin.attendance.substitution.index', compact('substitutions', 'teachers', 'classes'));
    }

    /**
     * Show the form for creating a new substitution.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $teachers = Teacher::with('user')->get();
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        
        return view('admin.attendance.substitution.create', compact('teachers', 'classes', 'subjects'));
    }

    /**
     * Store a newly created substitution in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'absent_teacher_id' => 'required|exists:teachers,id',
            'substitute_teacher_id' => 'required|exists:teachers,id|different:absent_teacher_id',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Substitution::create($request->all());

        return redirect()->route('admin.attendance.substitution.index')
            ->with('success', 'Substitution created successfully.');
    }

    /**
     * Display the specified substitution.
     *
     * @param  \App\Models\Admin\Substitution  $substitution
     * @return \Illuminate\View\View
     */
    public function show(Substitution $substitution)
    {
        $substitution->load(['absentTeacher.user', 'substituteTeacher.user', 'class', 'subject']);
        return view('admin.attendance.substitution.show', compact('substitution'));
    }

    /**
     * Show the form for editing the specified substitution.
     *
     * @param  \App\Models\Admin\Substitution  $substitution
     * @return \Illuminate\View\View
     */
    public function edit(Substitution $substitution)
    {
        $teachers = Teacher::with('user')->get();
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        
        return view('admin.attendance.substitution.edit', compact('substitution', 'teachers', 'classes', 'subjects'));
    }

    /**
     * Update the specified substitution in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Substitution  $substitution
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Substitution $substitution)
    {
        $request->validate([
            'absent_teacher_id' => 'required|exists:teachers,id',
            'substitute_teacher_id' => 'required|exists:teachers,id|different:absent_teacher_id',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $substitution->update($request->all());

        return redirect()->route('admin.attendance.substitution.index')
            ->with('success', 'Substitution updated successfully.');
    }

    /**
     * Remove the specified substitution from storage.
     *
     * @param  \App\Models\Admin\Substitution  $substitution
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Substitution $substitution)
    {
        $substitution->delete();

        return redirect()->route('admin.attendance.substitution.index')
            ->with('success', 'Substitution deleted successfully.');
    }
    
    /**
     * Generate substitution report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        $query = Substitution::with(['absentTeacher.user', 'substituteTeacher.user', 'class', 'subject'])
            ->whereBetween('date', [$startDate, $endDate]);
            
        if ($request->has('teacher_id') && $request->teacher_id) {
            $query->where(function($q) use ($request) {
                $q->where('absent_teacher_id', $request->teacher_id)
                  ->orWhere('substitute_teacher_id', $request->teacher_id);
            });
        }
        
        $substitutions = $query->get();
        $teachers = Teacher::with('user')->get();
        
        return view('admin.attendance.substitution.report', compact('substitutions', 'teachers', 'startDate', 'endDate'));
    }
}