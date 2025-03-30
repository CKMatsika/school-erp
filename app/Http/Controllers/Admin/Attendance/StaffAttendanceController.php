<?php

namespace App\Http\Controllers\Admin\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\StaffAttendance;
use App\Models\Admin\Staff;
use Carbon\Carbon;

class StaffAttendanceController extends Controller
{
    /**
     * Display a listing of staff attendance records.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $attendances = StaffAttendance::with('staff.user')
            ->whereDate('date', $date)
            ->latest()
            ->paginate(15);
            
        return view('admin.attendance.staff.index', compact('attendances', 'date'));
    }

    /**
     * Show the form for creating attendance records.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $staffMembers = Staff::with('user')->where('status', 'active')->get();
        $date = Carbon::today()->format('Y-m-d');
        
        return view('admin.attendance.staff.create', compact('staffMembers', 'date'));
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
            'staff_id' => 'required|array',
            'staff_id.*' => 'exists:staff,id',
            'status' => 'required|array',
            'status.*' => 'in:present,absent,late,half_day,leave',
            'in_time' => 'nullable|array',
            'out_time' => 'nullable|array',
            'remarks' => 'nullable|array',
        ]);

        $date = $request->date;
        
        // Delete existing records for the date to avoid duplicates
        StaffAttendance::whereDate('date', $date)->delete();
        
        foreach ($request->staff_id as $key => $staffId) {
            StaffAttendance::create([
                'staff_id' => $staffId,
                'date' => $date,
                'status' => $request->status[$key],
                'in_time' => $request->in_time[$key] ?? null,
                'out_time' => $request->out_time[$key] ?? null,
                'remarks' => $request->remarks[$key] ?? null,
            ]);
        }

        return redirect()->route('admin.attendance.staff.index', ['date' => $date])
            ->with('success', 'Staff attendance recorded successfully.');
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
        $staffMembers = Staff::with('user')->where('status', 'active')->get();
        $attendances = StaffAttendance::with('staff.user')
            ->whereDate('date', $date)
            ->get()
            ->keyBy('staff_id');
            
        return view('admin.attendance.staff.edit', compact('staffMembers', 'attendances', 'date'));
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
            'staff_id' => 'required|array',
            'staff_id.*' => 'exists:staff,id',
            'status' => 'required|array',
            'status.*' => 'in:present,absent,late,half_day,leave',
            'in_time' => 'nullable|array',
            'out_time' => 'nullable|array',
            'remarks' => 'nullable|array',
        ]);

        $date = Carbon::parse($date);
        
        // Delete existing records for the date
        StaffAttendance::whereDate('date', $date)->delete();
        
        foreach ($request->staff_id as $key => $staffId) {
            StaffAttendance::create([
                'staff_id' => $staffId,
                'date' => $date,
                'status' => $request->status[$key],
                'in_time' => $request->in_time[$key] ?? null,
                'out_time' => $request->out_time[$key] ?? null,
                'remarks' => $request->remarks[$key] ?? null,
            ]);
        }

        return redirect()->route('admin.attendance.staff.index', ['date' => $date->format('Y-m-d')])
            ->with('success', 'Staff attendance updated successfully.');
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
            'staff_id' => 'nullable|exists:staff,id',
            'department' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        $query = StaffAttendance::with('staff.user')
            ->whereBetween('date', [$startDate, $endDate]);
            
        if ($request->has('staff_id') && $request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }
        
        // If department filter is applied
        if ($request->has('department') && $request->department) {
            $query->whereHas('staff', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }
        
        $attendances = $query->get();
        $staffMembers = Staff::with('user')->where('status', 'active')->get();
        $departments = Staff::select('department')->distinct()->pluck('department');
        
        return view('admin.attendance.staff.report', compact('attendances', 'staffMembers', 'departments', 'startDate', 'endDate'));
    }
}