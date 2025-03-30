<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OnlineClass;
use App\Models\Attendee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OnlineClassController extends Controller
{
    /**
     * Display a listing of the online classes.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;
        
        // Upcoming classes (scheduled or ongoing)
        $upcomingClassesQuery = OnlineClass::where('teacher_id', $teacher->id)
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->orderBy('start_time', 'asc');
        
        // Past classes (completed or cancelled)
        $pastClassesQuery = OnlineClass::where('teacher_id', $teacher->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderBy('start_time', 'desc');
        
        // Apply filters to both queries
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $upcomingClassesQuery->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
            
            $pastClassesQuery->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('class_id') && $request->class_id != '') {
            $upcomingClassesQuery->where('class_id', $request->class_id);
            $pastClassesQuery->where('class_id', $request->class_id);
        }

        if ($request->has('subject_id') && $request->subject_id != '') {
            $upcomingClassesQuery->where('subject_id', $request->subject_id);
            $pastClassesQuery->where('subject_id', $request->subject_id);
        }

        if ($request->has('status') && $request->status != '') {
            if (in_array($request->status, ['scheduled', 'ongoing'])) {
                $upcomingClassesQuery->where('status', $request->status);
                $pastClassesQuery->whereNull('id'); // No results
            } elseif (in_array($request->status, ['completed', 'cancelled'])) {
                $pastClassesQuery->where('status', $request->status);
                $upcomingClassesQuery->whereNull('id'); // No results
            }
        }

        $upcomingClasses = $upcomingClassesQuery->get();
        $pastClasses = $pastClassesQuery->paginate(10);
        
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();

        return view('teacher.online-classes.index', compact('upcomingClasses', 'pastClasses', 'classes', 'subjects'));
    }

    /**
     * Show the form for creating a new online class.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        
        return view('teacher.online-classes.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created online class in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'platform' => 'required|string|in:zoom,google_meet,microsoft_teams,other',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'meeting_link' => 'required|url|max:255',
            'password' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'required_if:recurring,1|nullable|string|in:daily,weekly,biweekly,monthly',
            'recurrence_end_date' => 'required_if:recurring,1|nullable|date|after:date',
            'notify_students' => 'nullable|boolean',
            'send_reminder' => 'nullable|boolean',
        ]);

        $teacher = Auth::user()->teacher;

        $startDateTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endDateTime = Carbon::parse($request->date . ' ' . $request->end_time);

        $onlineClass = new OnlineClass();
        $onlineClass->title = $request->title;
        $onlineClass->platform = $request->platform;
        $onlineClass->class_id = $request->class_id;
        $onlineClass->subject_id = $request->subject_id;
        $onlineClass->teacher_id = $teacher->id;
        $onlineClass->start_time = $startDateTime;
        $onlineClass->end_time = $endDateTime;
        $onlineClass->meeting_link = $request->meeting_link;
        $onlineClass->password = $request->password;
        $onlineClass->description = $request->description;
        $onlineClass->instructions = $request->instructions;
        $onlineClass->status = 'scheduled';
        $onlineClass->recurring = $request->has('recurring');
        $onlineClass->recurrence_pattern = $request->recurrence_pattern;
        $onlineClass->recurrence_end_date = $request->recurrence_end_date;
        $onlineClass->send_reminder = $request->has('send_reminder');
        $onlineClass->save();

        // Send notifications if requested
        if ($request->has('notify_students')) {
            // TODO: Send notifications to students
        }

        // If recurring, create additional class sessions
        if ($request->has('recurring') && $request->recurrence_end_date) {
            $this->createRecurringClasses($onlineClass, $request->recurrence_pattern, $request->recurrence_end_date);
        }

        return redirect()->route('teacher.online-classes.show', $onlineClass)
            ->with('success', 'Online class scheduled successfully.');
    }

    /**
     * Create recurring class sessions based on pattern.
     *
     * @param  \App\Models\OnlineClass  $onlineClass
     * @param  string  $pattern
     * @param  string  $endDate
     * @return void
     */
    private function createRecurringClasses(OnlineClass $onlineClass, $pattern, $endDate)
    {
        $endDate = Carbon::parse($endDate);
        $currentDate = Carbon::parse($onlineClass->start_time)->addDay();
        $interval = 1; // days
        
        switch ($pattern) {
            case 'daily':
                $interval = 1;
                break;
            case 'weekly':
                $interval = 7;
                break;
            case 'biweekly':
                $interval = 14;
                break;
            case 'monthly':
                $interval = 30;
                break;
        }
        
        while ($currentDate->lte($endDate)) {
            $newStartTime = $currentDate->copy()->setTimeFromTimeString($onlineClass->start_time->format('H:i:s'));
            $newEndTime = $currentDate->copy()->setTimeFromTimeString($onlineClass->end_time->format('H:i:s'));
            
            $newClass = $onlineClass->replicate();
            $newClass->start_time = $newStartTime;
            $newClass->end_time = $newEndTime;
            $newClass->parent_class_id = $onlineClass->id;
            $newClass->save();
            
            $currentDate->addDays($interval);
        }
    }

    /**
     * Display the specified online class.
     *
     * @param  \App\Models\OnlineClass  $onlineClass
     * @return \Illuminate\View\View
     */
    public function show(OnlineClass $onlineClass)
    {
        $this->authorize('view', $onlineClass);
        
        return view('teacher.online-classes.show', compact('onlineClass'));
    }

    /**
     * Show the form for editing the specified online class.
     *
     * @param  \App\Models\OnlineClass  $onlineClass
     * @return \Illuminate\View\View
     */
    public function edit(OnlineClass $onlineClass)
    {
        $this->authorize('update', $onlineClass);
        
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        
        return view('teacher.online-classes.edit', compact('onlineClass', 'classes', 'subjects'));
    }

    /**
     * Update the specified online class in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OnlineClass  $onlineClass
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, OnlineClass $onlineClass)
    {
        $this->authorize('update', $onlineClass);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'platform' => 'required|string|in:zoom,google_meet,microsoft_teams,other',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'meeting_link' => 'required|url|max:255',
            'password' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'required_if:recurring,1|nullable|string|in:daily,weekly,biweekly,monthly',
            'recurrence_end_date' => 'required_if:recurring,1|nullable|date|after:date',
            'recording_url' => 'nullable|url|max:255',
            'send_reminder' => 'nullable|boolean',
            'send_update_notification' => 'nullable|boolean',
        ]);

        $startDateTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endDateTime = Carbon::parse($request->date . ' ' . $request->end_time);

        $onlineClass->title = $request->title;
        $onlineClass->platform = $request->platform;
        $onlineClass->class_id = $request->class_id;
        $onlineClass->subject_id = $request->subject_id;
        $onlineClass->start_time = $startDateTime;
        $onlineClass->end_time = $endDateTime;
        $onlineClass->meeting_link = $request->meeting_link;
        $onlineClass->password = $request->password;
        $onlineClass->description = $request->description;
        $onlineClass->instructions = $request->instructions;
        $onlineClass->recurring = $request->has('recurring');
        $onlineClass->recurrence_pattern = $request->recurrence_pattern;
        $onlineClass->recurrence_end_date = $request->recurrence_end_date;
        $onlineClass->recording_url = $request->recording_url;
        $onlineClass->send_reminder = $request->has('send_reminder');
        $onlineClass->save();

        // Send notifications if requested
        if ($request->has('send_update_notification')) {
            // TODO: Send notifications to students
        }

        return redirect()->route('teacher.online-classes.show', $onlineClass)
            ->with('success', 'Online class updated successfully.');
    }

    /**
     * Remove the specified online class from storage.
     *
     * @param  \App\Models\OnlineClass  $onlineClass
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(OnlineClass $onlineClass)
    {
        $this->authorize('delete', $onlineClass);
        
        // Delete attendees
        Attendee::where('online_class_id', $onlineClass->id)->delete();

        $onlineClass->delete();

        return redirect()->route('teacher.online-classes.index')
            ->with('success', 'Online class deleted successfully.');
    }

    /**
     * Start the specified online class.
     *
     * @param  \App\Models\OnlineClass  $onlineClass
     * @return \Illuminate\Http\RedirectResponse
     */
    public function start(OnlineClass $onlineClass)
    {
        $this->authorize('update', $onlineClass);
        
        if ($onlineClass->status != 'scheduled') {
            return back()->with('error', 'This class cannot be started. It is not in scheduled status.');
        }

        $onlineClass->status = 'ongoing';
        $onlineClass->actual_start_time = now();
        $onlineClass->save();

        // TODO: Send notifications to students

        return redirect()->route('teacher.online-classes.show', $onlineClass)
            ->with('success', 'Class has been started successfully.');
    }

    /**
     * End the specified online class.
     *
     * @param  \App\Models\OnlineClass  $onlineClass
     * @return \Illuminate\Http\RedirectResponse
     */
    public function end(OnlineClass $onlineClass)
    {
        $this->authorize('update', $onlineClass);
        
        if ($onlineClass->status != 'ongoing') {
            return back()->with('error', 'This class cannot be ended. It is not ongoing.');
        }

        $onlineClass->status = 'completed';
        $onlineClass->actual_end_time = now();
        $onlineClass->save();

        // Close all active attendees
        Attendee::where('online_class_id', $onlineClass->id)
            ->whereNull('leave_time')
            ->update(['leave_time' => now()]);

        return redirect()->route('teacher.online-classes.show', $onlineClass)
            ->with('success', 'Class has been ended successfully.');
    }

    /**
     * Join the specified online class.
     *
     * @param  \App\Models\OnlineClass  $onlineClass
     * @return \Illuminate\Http\RedirectResponse
     */
    public function join(OnlineClass $onlineClass)
    {
        $this->authorize('view', $onlineClass);
        
        if (!in_array($onlineClass->status, ['scheduled', 'ongoing'])) {
            return back()->with('error', 'This class cannot be joined. It is not scheduled or ongoing.');
        }

        // Record teacher attendance
        if ($onlineClass->status == 'ongoing') {
            $teacher = Auth::user()->teacher;
            
            $attendee = Attendee::firstOrNew([
                'online_class_id' => $onlineClass->id,
                'teacher_id' => $teacher->id,
            ]);
            
            if (!$attendee->exists) {
                $attendee->join_time = now();
                $attendee->save();
            } elseif ($attendee->leave_time) {
                // Rejoin
                $attendee->leave_time = null;
                $attendee->save();
            }
        }

        return redirect()->away($onlineClass->meeting_link);
    }
}