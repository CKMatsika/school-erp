<?php

namespace App\Http\Controllers\Admin\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\TeacherAttendance;
use App\Models\Teacher\Teacher;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    /**
     * Display a listing of teacher attendance records.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $attendances = TeacherAttendance::with('teacher.user')
            ->whereDate('date', $date)
            ->latest()
            ->paginate(15);
            
        return view('admin.attendance.teacher.index', compact('attendances', 'date'));
    }

    /**
     * Show the form for creating attendance records.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $teachers = Teacher::with('user')->get();
        $date = Carbon::today()->format('Y-m-d');
        
        return view('admin.attendance.teacher.create', compact('teachers', 'date'));
    }

    /**
     * Store attendance records in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'teacher_id' => 'required|array',
            'teacher_id.*' => 'exists:teachers,id',
            'status' => 'required|array',
            'status.*' => 'in:present,absent,late,half_day,leave',
            'in_time' => 'nullable|array',
            'out_time' => 'nullable|array',
            'remarks' => 'nullable|array',
        ]);

        $date = $request->date;
        
        // Delete existing records for the date to avoid duplicates
        TeacherAttendance::whereDate('date', $date)->delete();
        
        foreach ($request->teacher_id as $key => $teacherId) {
            TeacherAttendance::create([
                'teacher_id' => $teacherId,
                'date' => $date,
                'status' => $request->status[$key],
                'in_time' => $request->in_time[$key] ?? null,
                'out_time' => $request->out_time[$key] ?? null,
                'remarks' => $request->remarks[$key] ?? null,
            ]);
        }

        return redirect()->route('admin.attendance.teacher.index', ['date' => $date])
            ->with('success', 'Teacher attendance recorded successfully.');
    }

    /**
     * Show the form for editing attendance records.
     *
     * @param  string  $date
     * @return \Illuminate\View\View
     */
    public function edit($date)
    {
        $date = Carbon::parse($date);
        $teachers = Teacher::with('user')->get();
        $attendances = TeacherAttendance::with('teacher.user')
            ->whereDate('date', $date)
            ->get()
            ->keyBy('teacher_id');
            
        return view('admin.attendance.teacher.edit', compact('teachers', 'attendances', 'date'));
    }

    /**
     * Update attendance records in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $date
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $date)
    {
        $request->validate([
            'teacher_id' => 'required|array',
            'teacher_id.*' => 'exists:teachers,id',
            'status' => 'required|array',
            'status.*' => 'in:present,absent,late,half_day,leave',
            'in_time' => 'nullable|array',
            'out_time' => 'nullable|array',
            'remarks' => 'nullable|array',
        ]);

        $date = Carbon::parse($date);
        
        // Delete existing records for the date
        TeacherAttendance::whereDate('date', $date)->delete();
        
        foreach ($request->teacher_id as $key => $teacherId) {
            TeacherAttendance::create([
                'teacher_id' => $teacherId,
                'date' => $date,
                'status' => $request->status[$key],
                'in_time' => $request->in_time[$key] ?? null,
                'out_time' => $request->out_time[$key] ?? null,
                'remarks' => $request->remarks[$key] ?? null,
            ]);
        }

        return redirect()->route('admin.attendance.teacher.index', ['date' => $date->format('Y-m-d')])
            ->with('success', 'Teacher attendance updated successfully.');
    }

    /**
     * Generate attendance report.
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
        
        $query = TeacherAttendance::with('teacher.user')
            ->whereBetween('date', [$startDate, $endDate]);
            
        if ($request->has('teacher_id') && $request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }
        
        $attendances = $query->get();
        $teachers = Teacher::with('user')->get();
        
        return view('admin.attendance.teacher.report', compact('attendances', 'teachers', 'startDate', 'endDate'));
    }
}