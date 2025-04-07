<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
// Make sure the namespace for AcademicYear is correct
use App\Models\Student\AcademicYear; // Assuming it's here, adjust if needed (e.g., App\Models\Student\AcademicYear)
use App\Models\Student\Application; // Adjust namespace if needed
use App\Models\Student\Enrollment; // Adjust namespace if needed
use App\Models\TimetableSchoolClass; // Adjust namespace if needed
use App\Models\Activity; // Adjust namespace if needed
use App\Models\Notification; // Adjust namespace if needed
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Use auth()->user()->school_id directly or fetch school if needed often
        $school_id = auth()->user()->school_id;
        // If school_id is required but might be null on user, add a check or fallback
        if (!$school_id) {
             // Handle case where user isn't linked to a school appropriately
             // e.g., return redirect()->route('some.error.page')->with('error', 'User not associated with a school.');
             // Or throw an exception, depending on your application logic.
             // For now, we'll let queries potentially return empty results if school_id is needed.
        }

        // Filter parameters
        $class_id = $request->input('class_id');
        $status_filter = $request->input('status', 'active'); // Keep 'active'/'inactive'/'graduated' from request
        $academic_year_id = $request->input('academic_year_id');
        $query = $request->input('query');

        // Get all classes and academic years for filter dropdowns
        // Use optional() or check $school_id if it's potentially null
        $classes = TimetableSchoolClass::where('school_id', $school_id)->get();
        $academicYears = AcademicYear::where('school_id', $school_id)->orderByDesc('start_date')->get();

        // Base queries with school_id filter
        $studentsQuery = Student::where('school_id', $school_id);
        // $applicationsQuery = Application::where('school_id', $school_id); // Defined later

        // Apply status filter using the correct 'is_active' column
        if ($status_filter === 'active') {
            $studentsQuery->where('is_active', true); // Use boolean true for active
        } elseif ($status_filter === 'inactive') {
            $studentsQuery->where('is_active', false); // Use boolean false for inactive
        }
        // Note: The 'students' table schema created via Tinker doesn't currently
        // have a specific way to represent 'graduated' distinct from 'inactive'.
        // The query below for $totalGraduated will need adjustment or the schema updated
        // if you need to distinguish 'graduated'. For now, this filter only handles active/inactive.


        if ($class_id) {
            // Assuming students table has 'current_class_id' based on previous examples
            // If your column name is different, adjust here.
            $studentsQuery->where('current_class_id', $class_id);
        }

        // Apply search query if provided
        if ($query) {
            $studentsQuery->where(function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('student_number', 'like', "%{$query}%") // Assuming student_number exists
                  // ->orWhere('admission_number', 'like', "%{$query}%") // Use student_number OR admission_number
                  ->orWhere('email', 'like', "%{$query}%");
            });

            // $applicationsQuery->where(...) // Apply search to applications query base later
        }

        // Get counts for dashboard
        // Create a clone *before* applying the status filter for the total count if needed
        // $totalStudentsAllStatuses = $studentsQuery->clone()->count(); // Example if you need total regardless of filter

        // Apply the status filter for the displayed count
        $totalStudents = $studentsQuery->count(); // This count respects the $status_filter

        // --- Application Counts ---
        // Base query for applications
         $baseApplicationsQuery = Application::where('school_id', $school_id);

        // Apply search query if provided to application counts
        if ($query) {
            $baseApplicationsQuery->where(function($q) use ($query) {
                 $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('application_number', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            });
        }

        $totalApplications = $baseApplicationsQuery->clone() // Use clone to avoid modifying the base query
                                      ->where('status', '!=', 'enrolled') // Assuming 'enrolled' is a status
                                      ->count();

        $pendingApplications = $baseApplicationsQuery->clone()
                                         ->whereIn('status', ['submitted', 'under_review', 'pending_documents', 'interview_scheduled']) // Assuming these are statuses
                                         ->count();

        // --- Academic Year & Enrollments ---
        $currentAcademicYear = AcademicYear::where('school_id', $school_id)
                                         ->where('is_current', true)
                                         ->first();

        // Recent enrollments query
        $enrollmentsQuery = Enrollment::with(['student', 'class', 'academicYear'])
                                  ->whereHas('student', function ($query) use ($school_id) {
                                      $query->where('school_id', $school_id);
                                  });

        // Filter enrollments if academic year filter is provided
        if ($academic_year_id) {
            $enrollmentsQuery->where('academic_year_id', $academic_year_id);
        } elseif ($currentAcademicYear) {
            // Default to current year if no filter applied
            $enrollmentsQuery->where('academic_year_id', $currentAcademicYear->id);
        }

        // Get recent enrollments
        $recentEnrollments = $enrollmentsQuery->latest() // Assumes created_at or similar
                                         ->take(5)
                                         ->get();

        // Recent applications
        $recentApplications = Application::where('school_id', $school_id)
                                       ->latest()
                                       ->take(5)
                                       ->get();

        // --- Class Statistics ---
        $classStats = [];
        $targetAcademicYearIdForStats = $academic_year_id ?: ($currentAcademicYear ? $currentAcademicYear->id : null);

        if ($targetAcademicYearIdForStats) {
            $enrollmentsForStatsQuery = Enrollment::where('academic_year_id', $targetAcademicYearIdForStats)
                                          ->whereHas('student', function($q) use ($school_id){ // Ensure student belongs to school
                                                $q->where('school_id', $school_id);
                                          });
                                          // Removed ->where('status', 'active') for enrollment status - adjust if needed

            // Apply class filter if provided
            if ($class_id) {
                $enrollmentsForStatsQuery->where('class_id', $class_id);
            }

            $classStats = $enrollmentsForStatsQuery->with('class') // Eager load class
                                          ->select('class_id', DB::raw('count(*) as count')) // Select only needed columns
                                          ->groupBy('class_id')
                                          ->get()
                                          ->map(function ($enrollmentGroup) {
                                              // Access class name via the relationship
                                              return [
                                                  'class_name' => $enrollmentGroup->class->name ?? 'Unknown Class', // Handle potential null class
                                                  'count' => $enrollmentGroup->count,
                                              ];
                                          })
                                          ->values(); // Convert to simple array
        }

        // --- Gender Distribution ---
        $genderDistributionQuery = Student::where('school_id', $school_id)
                                       ->where('is_active', true); // Filter only active students for distribution

        // Apply class filter if provided
        if ($class_id) {
             // Ensure column name is correct, e.g., 'current_class_id'
            $genderDistributionQuery->where('current_class_id', $class_id);
        }

        $genderDistribution = $genderDistributionQuery->select('gender', DB::raw('count(*) as count'))
                                   ->groupBy('gender')
                                   ->pluck('count', 'gender'); // Simpler way to get key/value pair


        // --- Additional Stats ---
        // Total enrollments for the school
        $totalEnrollments = Enrollment::whereHas('student', function ($query) use ($school_id) {
                                $query->where('school_id', $school_id);
                            })->count();

        // Total "graduated" - Requires schema change or different logic
        // For now, count inactive students as a placeholder, but this is NOT accurate
        // TODO: Implement proper graduation tracking (e.g., add a 'graduated_date' or 'status' enum to students table)
        // $totalGraduated = Student::where('school_id', $school_id)
        //                        ->where('is_active', false) // TEMPORARY - counts all inactive
        //                        ->count();
        $totalGraduated = 0; // Set to 0 until graduation status is properly tracked

        $graduationRate = $totalEnrollments > 0
                        ? round(($totalGraduated / $totalEnrollments) * 100) . '%'
                        : '0%';

        // Admissions stats
         $totalApplicationsAll = Application::where('school_id', $school_id)->count();
         $totalAccepted = Application::where('school_id', $school_id)
                                  ->whereIn('status', ['enrolled', 'accepted']) // Assuming these statuses
                                  ->count();

        $admissionsRate = $totalApplicationsAll > 0
                        ? round(($totalAccepted / $totalApplicationsAll) * 100) . '%'
                        : '0%';

        // Simulated attendance rate for now - Needs real data source
        $attendanceRate = 'N/A'; // Changed from '95%' to reflect it's not real data

        // Get recent activities
        $activities = Activity::where('school_id', $school_id)
                            ->with('user') // Eager load user
                            ->latest()
                            ->take(10)
                            ->get();

        // Get recent notifications
        $notifications = Notification::where('school_id', $school_id)
                                   ->where(function($query) {
                                       $query->where('user_id', auth()->id())
                                             ->orWhereNull('user_id'); // Global notifications
                                   })
                                   ->latest()
                                   ->take(5)
                                   ->get();

        // *** CORRECTED COMPACT() CALL ***
        return view('student.dashboard.index', compact(
            'totalStudents',
            'totalApplications',
            'pendingApplications',
            'currentAcademicYear',
            'recentEnrollments',
            'recentApplications',
            'classStats',
            'genderDistribution',
            'classes', // For filter dropdown
            'academicYears', // For filter dropdown
            'attendanceRate',
            'graduationRate',
            'admissionsRate',
            'activities',
            'notifications',
            // Pass filter values back to view to maintain state
            'class_id',        // Correctly included
            'status_filter',   // Correctly included
            'academic_year_id',// Correctly included
            'query'            // Correctly included
        ));
        // *** END OF CORRECTION ***
    }

    public function statistics()
    {
        $school_id = auth()->user()->school_id;

        $academicYears = AcademicYear::where('school_id', $school_id)
                                   ->orderByDesc('start_date')
                                   ->get();

        $yearlyEnrollments = [];
        foreach ($academicYears as $year) {
            $yearlyEnrollments[$year->name] = Enrollment::where('academic_year_id', $year->id)
                                                     // Add school_id constraint if enrollments aren't directly linked but students are
                                                      ->whereHas('student', function ($query) use ($school_id) {
                                                          $query->where('school_id', $school_id);
                                                      })
                                                     ->count();
        }

        $applicationStats = Application::where('school_id', $school_id)
                                     ->select('status', DB::raw('count(*) as count'))
                                     ->groupBy('status')
                                     ->pluck('count', 'status'); // Use pluck directly


        // Use is_active for student status
        $studentsByStatusRaw = Student::where('school_id', $school_id)
                                 ->select('is_active', DB::raw('count(*) as count'))
                                 ->groupBy('is_active')
                                 ->pluck('count', 'is_active'); // Gets { 1: count, 0: count }

        // Map boolean keys to readable strings for the view
        $studentsByStatus = [
            'Active' => $studentsByStatusRaw[1] ?? 0, // Count for is_active = true
            'Inactive' => $studentsByStatusRaw[0] ?? 0, // Count for is_active = false
            // 'Graduated' => 0 // Add logic here if/when graduation status is added
        ];


        return view('student.dashboard.statistics', compact(
            'academicYears',
            'yearlyEnrollments',
            'applicationStats',
            'studentsByStatus' // Use the mapped array
        ));
    }

    /**
     * Search for students and applications
     */
    public function search(Request $request)
    {
        $school_id = auth()->user()->school_id;
        $query = $request->input('query');

        if (empty($query)) {
            return redirect()->route('student.dashboard'); // Redirect if query is empty
        }

        $students = Student::where('school_id', $school_id)
                         ->where(function($q) use ($query) {
                             $q->where('first_name', 'like', "%{$query}%")
                               ->orWhere('last_name', 'like', "%{$query}%")
                               ->orWhere('student_number', 'like', "%{$query}%") // Use student_number
                               // ->orWhere('admission_number', 'like', "%{$query}%") // Or admission_number
                               ->orWhere('email', 'like', "%{$query}%");
                         })
                         ->limit(15) // Limit results for performance
                         ->get();

        $applications = Application::where('school_id', $school_id)
                                 ->where(function($q) use ($query) {
                                     $q->where('first_name', 'like', "%{$query}%")
                                       ->orWhere('last_name', 'like', "%{$query}%")
                                       ->orWhere('application_number', 'like', "%{$query}%")
                                       ->orWhere('email', 'like', "%{$query}%");
                                 })
                                 ->limit(15) // Limit results
                                 ->get();

        return view('student.search.results', compact('query', 'students', 'applications'));
    }

    /**
     * Export student data to CSV
     */
    public function export(Request $request)
    {
        $school_id = auth()->user()->school_id;
        $type = $request->input('type', 'students'); // Default to exporting students

        if ($type === 'students') {
            $students = Student::where('school_id', $school_id)->with('currentClass')->get(); // Eager load class, assuming relationship name 'currentClass'

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="students.csv"',
            ];

            $callback = function() use ($students) {
                $file = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($file, [
                    'ID', 'Student Number', 'First Name', 'Last Name', 'Gender',
                    'Email', 'Phone', 'Status', 'Class', 'Admission Date' // Changed header
                ]);

                // Add student data
                foreach ($students as $student) {
                    fputcsv($file, [
                        $student->id,
                        $student->student_number, // Use student_number
                        $student->first_name,
                        $student->last_name,
                        $student->gender,
                        $student->email,
                        $student->phone,
                        $student->is_active ? 'Active' : 'Inactive', // Use is_active
                        $student->currentClass ? $student->currentClass->name : 'N/A', // Adjusted relationship name check
                        $student->admission_date ? $student->admission_date : 'N/A' // Removed format as it's already date
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } elseif ($type === 'applications') {
            // Assuming Application model has 'applyingForClass' relationship
             $applications = Application::where('school_id', $school_id)->with('applyingForClass')->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="applications.csv"',
            ];

            $callback = function() use ($applications) {
                $file = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($file, [
                    'ID', 'Application Number', 'First Name', 'Last Name', 'Gender',
                    'Email', 'Phone', 'Status', 'Applied For Class', 'Submission Date'
                ]);

                // Add application data
                foreach ($applications as $application) {
                    fputcsv($file, [
                        $application->id,
                        $application->application_number,
                        $application->first_name,
                        $application->last_name,
                        $application->gender,
                        $application->email,
                        $application->phone,
                        $application->status, // Assuming Application model uses 'status'
                        $application->applyingForClass ? $application->applyingForClass->name : 'N/A',
                        $application->submission_date ? $application->submission_date : 'N/A' // Removed format
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Redirect back with error if type is invalid
        return redirect()->back()->with('error', 'Invalid export type specified.');
    }
}