<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use App\Models\HumanResources\AttendanceRecord;
use App\Models\HumanResources\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // For date handling

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendance records.
     */
    public function index(Request $request)
    {
        // Gate::authorize('viewAny', AttendanceRecord::class);

        $query = AttendanceRecord::with('staff:id,first_name,last_name', 'recorder:id,name'); // Eager load

        // Filters
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }
        if ($request->filled('date_from')) {
            $query->where('attendance_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('attendance_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendanceRecords = $query->orderBy('attendance_date', 'desc')->orderBy('staff_id')->paginate(50)->withQueryString();
        $staffList = Staff::active()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']); // For filter dropdown

        return view('hr.attendance.index', compact('attendanceRecords', 'staffList'));
    }

    /**
     * Show the form for creating a new manual attendance record.
     */
    public function create()
    {
        // Gate::authorize('create', AttendanceRecord::class);
        $staffList = Staff::active()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        return view('hr.attendance.create', compact('staffList'));
    }

    /**
     * Store a newly created manual attendance record in storage.
     */
    public function store(Request $request)
    {
        // Gate::authorize('create', AttendanceRecord::class);

        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'attendance_date' => 'required|date',
            'status' => 'required|string|in:present,absent,late,early_departure,on_leave,holiday', // Match enum
            'clock_in_time' => 'nullable|required_if:status,present,late|date_format:H:i', // Use H:i for input
            'clock_out_time' => 'nullable|required_if:status,present,early_departure|date_format:H:i|after_or_equal:clock_in_time', // Use H:i for input
            'notes' => 'nullable|required_if:status,absent,late,early_departure,on_leave|string|max:500',
        ]);

        // Prevent duplicate entry for same staff on same day
        $existing = AttendanceRecord::where('staff_id', $validated['staff_id'])
                                    ->where('attendance_date', $validated['attendance_date'])
                                    ->first();
        if ($existing) {
            return back()->withInput()->with('error', 'An attendance record already exists for this staff member on this date.');
        }

        // Calculate duration if applicable
        $workDuration = null;
        if (!empty($validated['clock_in_time']) && !empty($validated['clock_out_time'])) {
            try {
                 // Combine date and time for calculation
                 $clockIn = Carbon::parse($validated['attendance_date'] . ' ' . $validated['clock_in_time']);
                 $clockOut = Carbon::parse($validated['attendance_date'] . ' ' . $validated['clock_out_time']);
                 // Handle overnight shifts if necessary (out time < in time)
                 if ($clockOut->lt($clockIn)) {
                     $clockOut->addDay(); // Add a day if clock out is on the next day
                 }
                 $workDuration = round($clockOut->diffInMinutes($clockIn) / 60, 2); // Duration in hours
            } catch (\Exception $e) {
                // Handle parsing error if time format is wrong, though validation should catch it
                Log::warning("Could not calculate work duration: " . $e->getMessage());
            }
        }

        // Add system fields
        $validated['source'] = 'manual';
        $validated['recorded_by'] = Auth::id();
        $validated['work_duration'] = $workDuration;
        // Format time for DB storage (assuming TIME column type)
        $validated['clock_in_time'] = $validated['clock_in_time'] ? $validated['clock_in_time'] . ':00' : null;
        $validated['clock_out_time'] = $validated['clock_out_time'] ? $validated['clock_out_time'] . ':00' : null;


        try {
            AttendanceRecord::create($validated);
            return redirect()->route('hr.attendance.index')->with('success', 'Attendance recorded successfully.');
        } catch (\Exception $e) {
            Log::error("Manual Attendance Store failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to record attendance.');
        }
    }

     /**
     * Show the form for generating attendance reports.
     */
    public function reports()
    {
        // Gate::authorize('viewReports', AttendanceRecord::class); // Custom permission maybe
         $staffList = Staff::active()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
         $departments = Staff::distinct()->whereNotNull('department')->pluck('department');
        return view('hr.attendance.reports', compact('staffList', 'departments'));
    }

    /**
     * Generate and potentially download an attendance report.
     */
    public function generateReport(Request $request)
    {
         // Gate::authorize('viewReports', AttendanceRecord::class);

         $validated = $request->validate([
             'report_type' => 'required|in:daily_summary,monthly_summary,exception_report',
             'date_from' => 'required|date',
             'date_to' => 'required|date|after_or_equal:date_from',
             'staff_id' => 'nullable|exists:staff,id',
             'department' => 'nullable|string',
             'status' => 'nullable|string', // For exception report
         ]);

         // TODO: Implement complex report generation logic based on $validated['report_type']
         // Fetch data using AttendanceRecord model with filters
         // Aggregate data (e.g., count present/absent/late, calculate total hours)
         // Format data for display or export (e.g., PDF, Excel)

         $reportData = []; // Placeholder
         $reportTitle = "Attendance Report"; // Placeholder

          // Example: Simple fetch for demonstration
         $query = AttendanceRecord::with('staff:id,first_name,last_name')
            ->whereBetween('attendance_date', [$validated['date_from'], $validated['date_to']]);
         if($request->filled('staff_id')) $query->where('staff_id', $validated['staff_id']);
         if($request->filled('department')) $query->whereHas('staff', fn($q) => $q->where('department', $validated['department']));
         if($request->filled('status') && $validated['report_type'] === 'exception_report') $query->where('status', $validated['status']);

         $reportData = $query->orderBy('attendance_date')->orderBy('staff_id')->get();


         // For now, just return a simple view with the raw data (replace with proper report view/export)
         return view('hr.attendance.report_results', compact('reportData', 'validated', 'reportTitle'));

    }

}