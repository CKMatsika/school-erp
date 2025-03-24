<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Student\AcademicYear;
use App\Models\Student\Application;
use App\Models\Student\Enrollment;
use App\Models\TimetableSchoolClass;
use App\Models\Activity;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $school_id = auth()->user()->school_id;
        
        // Filter parameters
        $class_id = $request->input('class_id');
        $status = $request->input('status', 'active'); // Default to active students
        $academic_year_id = $request->input('academic_year_id');
        $query = $request->input('query');
        
        // Get all classes and academic years for filter dropdowns
        $classes = TimetableSchoolClass::where('school_id', $school_id)->get();
        $academicYears = AcademicYear::where('school_id', $school_id)->get();
        
        // Base queries with school_id filter
        $studentsQuery = Student::where('school_id', $school_id);
        $applicationsQuery = Application::where('school_id', $school_id);
        
        // Apply filters if provided
        if ($status) {
            $studentsQuery->where('status', $status);
        }
        
        if ($class_id) {
            $studentsQuery->where('class_id', $class_id);
        }
        
        // Apply search query if provided
        if ($query) {
            $studentsQuery->where(function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('admission_number', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            });
            
            $applicationsQuery->where(function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('application_number', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            });
        }
        
        // Get counts for dashboard
        $totalStudents = $studentsQuery->count();
        
        $totalApplications = $applicationsQuery->where('status', '!=', 'enrolled')
                                     ->count();
        
        $pendingApplications = $applicationsQuery->whereIn('status', ['submitted', 'under_review', 'pending_documents', 'interview_scheduled'])
                                        ->count();
        
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
        }
        
        // Get recent enrollments
        $recentEnrollments = $enrollmentsQuery->latest()
                                         ->take(5)
                                         ->get();
        
        // Recent applications
        $recentApplications = Application::where('school_id', $school_id)
                                       ->latest()
                                       ->take(5)
                                       ->get();
        
        // Class statistics
        $classStats = [];
        
        if ($academic_year_id && $currentAcademicYear) {
            // If academic year filter is applied, use that
            $targetAcademicYear = $academic_year_id;
        } elseif ($currentAcademicYear) {
            // Otherwise use current academic year
            $targetAcademicYear = $currentAcademicYear->id;
        } else {
            $targetAcademicYear = null;
        }
        
        if ($targetAcademicYear) {
            $enrollmentsForStats = Enrollment::where('academic_year_id', $targetAcademicYear)
                                  ->where('status', 'active');
            
            // Apply class filter if provided
            if ($class_id) {
                $enrollmentsForStats->where('class_id', $class_id);
            }
            
            $classStats = $enrollmentsForStats->with('class')
                                  ->get()
                                  ->groupBy('class_id')
                                  ->map(function ($group) {
                                      return [
                                          'class_name' => $group->first()->class->name ?? 'Unknown',
                                          'count' => $group->count(),
                                      ];
                                  })
                                  ->values();
        }
        
        // Gender distribution
        $genderDistributionQuery = Student::where('school_id', $school_id)
                                       ->where('status', 'active');
        
        // Apply class filter if provided
        if ($class_id) {
            $genderDistributionQuery->where('class_id', $class_id);
        }
        
        $genderDistribution = $genderDistributionQuery->select('gender', DB::raw('count(*) as count'))
                                   ->groupBy('gender')
                                   ->get()
                                   ->pluck('count', 'gender')
                                   ->toArray();
        
        // Calculate additional stats
        $totalEnrollments = Enrollment::whereHas('student', function ($query) use ($school_id) {
                                $query->where('school_id', $school_id);
                            })->count();
        
        $totalGraduated = Student::where('school_id', $school_id)
                               ->where('status', 'graduated')
                               ->count();
        
        $graduationRate = $totalEnrollments > 0 
                        ? round(($totalGraduated / $totalEnrollments) * 100) . '%' 
                        : '0%';
        
        $totalAccepted = Application::where('school_id', $school_id)
                                  ->whereIn('status', ['enrolled', 'accepted'])
                                  ->count();
        
        $totalApplicationsAll = Application::where('school_id', $school_id)->count();
        
        $admissionsRate = $totalApplicationsAll > 0 
                        ? round(($totalAccepted / $totalApplicationsAll) * 100) . '%' 
                        : '0%';
        
        // Simulated attendance rate for now
        $attendanceRate = '95%';
        
        // Get recent activities
        $activities = Activity::where('school_id', $school_id)
                            ->with('user')
                            ->latest()
                            ->take(10)
                            ->get();
        
        // Get recent notifications
        $notifications = Notification::where('school_id', $school_id)
                                   ->where(function($query) {
                                       $query->where('user_id', auth()->id())
                                             ->orWhereNull('user_id');
                                   })
                                   ->latest()
                                   ->take(5)
                                   ->get();
        
        return view('student.dashboard.index', compact(
            'totalStudents',
            'totalApplications', 
            'pendingApplications',
            'currentAcademicYear',
            'recentEnrollments',
            'recentApplications',
            'classStats',
            'genderDistribution',
            'classes',
            'academicYears',
            'attendanceRate',
            'graduationRate',
            'admissionsRate',
            'activities',
            'notifications'
        ));
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
                                                     ->count();
        }
        
        $applicationStats = Application::where('school_id', $school_id)
                                     ->select('status', DB::raw('count(*) as count'))
                                     ->groupBy('status')
                                     ->get()
                                     ->pluck('count', 'status')
                                     ->toArray();
        
        $studentsByStatus = Student::where('school_id', $school_id)
                                 ->select('status', DB::raw('count(*) as count'))
                                 ->groupBy('status')
                                 ->get()
                                 ->pluck('count', 'status')
                                 ->toArray();
        
        return view('student.dashboard.statistics', compact(
            'academicYears',
            'yearlyEnrollments',
            'applicationStats',
            'studentsByStatus'
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
            return redirect()->route('student.dashboard');
        }
        
        $students = Student::where('school_id', $school_id)
                         ->where(function($q) use ($query) {
                             $q->where('first_name', 'like', "%{$query}%")
                               ->orWhere('last_name', 'like', "%{$query}%")
                               ->orWhere('admission_number', 'like', "%{$query}%")
                               ->orWhere('email', 'like', "%{$query}%");
                         })
                         ->limit(15)
                         ->get();
        
        $applications = Application::where('school_id', $school_id)
                                 ->where(function($q) use ($query) {
                                     $q->where('first_name', 'like', "%{$query}%")
                                       ->orWhere('last_name', 'like', "%{$query}%")
                                       ->orWhere('application_number', 'like', "%{$query}%")
                                       ->orWhere('email', 'like', "%{$query}%");
                                 })
                                 ->limit(15)
                                 ->get();
        
        return view('student.search.results', compact('query', 'students', 'applications'));
    }
    
    /**
     * Export student data to CSV
     */
    public function export(Request $request)
    {
        $school_id = auth()->user()->school_id;
        $type = $request->input('type', 'students');
        
        if ($type === 'students') {
            $students = Student::where('school_id', $school_id)->get();
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="students.csv"',
            ];
            
            $callback = function() use ($students) {
                $file = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($file, [
                    'ID', 'Admission Number', 'First Name', 'Last Name', 'Gender', 
                    'Email', 'Phone', 'Status', 'Class', 'Enrollment Date'
                ]);
                
                // Add student data
                foreach ($students as $student) {
                    fputcsv($file, [
                        $student->id,
                        $student->admission_number,
                        $student->first_name,
                        $student->last_name,
                        $student->gender,
                        $student->email,
                        $student->phone,
                        $student->status,
                        $student->class ? $student->class->name : 'N/A',
                        $student->enrollment_date ? $student->enrollment_date->format('Y-m-d') : 'N/A'
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        } elseif ($type === 'applications') {
            $applications = Application::where('school_id', $school_id)->get();
            
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
                        $application->status,
                        $application->applyingForClass ? $application->applyingForClass->name : 'N/A',
                        $application->submission_date ? $application->submission_date->format('Y-m-d') : 'N/A'
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        return redirect()->back()->with('error', 'Invalid export type specified.');
    }
}