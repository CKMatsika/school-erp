<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Teacher\Teacher;
use App\Models\SchoolClass;
use App\Models\LessonPlan;
use App\Models\Assignment;
use App\Models\OnlineClass;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use stdClass;

class TeacherDashboardController extends Controller
{
    /**
     * Display the teacher dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the current user
        $user = Auth::user();
        
        try {
            // Try to get the teacher record
            $teacher = Teacher::where('user_id', $user->id)->first();
            
            if ($teacher) {
                // Get teacher's classes
                $classes = SchoolClass::whereHas('teachers', function($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id);
                })->get();
                
                // Get upcoming lessons
                $upcomingLessons = LessonPlan::where('teacher_id', $teacher->id)
                    ->where('date', '>=', Carbon::today())
                    ->orderBy('date')
                    ->orderBy('start_time')
                    ->take(5)
                    ->get();
                
                // Get assignments with pending submissions
                $pendingAssignments = Assignment::where('teacher_id', $teacher->id)
                    ->where('status', 'published')
                    ->where('due_date', '>=', Carbon::today())
                    ->withCount(['submissions as pending_count' => function($query) {
                        $query->whereNull('graded_at');
                    }])
                    ->get();
                
                // Get upcoming online classes
                $upcomingOnlineClasses = OnlineClass::where('teacher_id', $teacher->id)
                    ->whereIn('status', ['scheduled', 'ongoing'])
                    ->where('start_time', '>=', Carbon::now())
                    ->orderBy('start_time')
                    ->take(5)
                    ->get();
            } else {
                // Create a placeholder teacher object
                $teacher = $this->createPlaceholderTeacher($user);
                
                // Create empty collections for other data
                $classes = new Collection();
                $upcomingLessons = new Collection();
                $pendingAssignments = new Collection();
                $upcomingOnlineClasses = new Collection();
            }
        } catch (\Exception $e) {
            // Create a placeholder teacher object
            $teacher = $this->createPlaceholderTeacher($user);
            
            // Create empty collections for other data
            $classes = new Collection();
            $upcomingLessons = new Collection();
            $pendingAssignments = new Collection();
            $upcomingOnlineClasses = new Collection();
        }
        
        return view('teacher.dashboard', compact(
            'teacher',
            'classes',
            'upcomingLessons',
            'pendingAssignments',
            'upcomingOnlineClasses'
        ));
    }
    
    /**
     * Create a placeholder teacher object with default values.
     *
     * @param \App\Models\User $user
     * @return stdClass
     */
    private function createPlaceholderTeacher($user)
    {
        $teacher = new stdClass();
        $teacher->id = 0;
        $teacher->title = '';
        $teacher->specialization = 'Teacher';
        $teacher->employee_id = 'T-' . rand(1000, 9999);
        $teacher->user = $user;
        $teacher->department = 'General';
        $teacher->status = 'active';
        
        return $teacher;
    }
}