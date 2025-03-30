<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LessonPlan;
use Illuminate\Support\Facades\Auth;

class LessonPlanController extends Controller
{
    /**
     * Display a listing of the lesson plans.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = LessonPlan::query()->where('teacher_id', Auth::user()->teacher->id);

        // Apply filters
        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('class_id') && $request->class_id != '') {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('subject_id') && $request->subject_id != '') {
            $query->where('subject_id', $request->subject_id);
        }

        $lessonPlans = $query->latest()->paginate(10);
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();

        return view('teacher.lessons.index', compact('lessonPlans', 'classes', 'subjects'));
    }

    /**
     * Show the form for creating a new lesson plan.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        
        return view('teacher.lessons.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created lesson plan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'duration' => 'required|integer|min:1',
            'date' => 'required|date',
            'objectives' => 'required|string',
            'description' => 'nullable|string',
            'resources' => 'nullable|string',
            'activities' => 'required|string',
            'assessment' => 'nullable|string',
            'homework' => 'nullable|string',
        ]);

        $lessonPlan = new LessonPlan();
        $lessonPlan->title = $request->title;
        $lessonPlan->class_id = $request->class_id;
        $lessonPlan->subject_id = $request->subject_id;
        $lessonPlan->teacher_id = Auth::user()->teacher->id;
        $lessonPlan->duration = $request->duration;
        $lessonPlan->date = $request->date;
        $lessonPlan->objectives = $request->objectives;
        $lessonPlan->description = $request->description;
        $lessonPlan->resources = $request->resources;
        $lessonPlan->activities = $request->activities;
        $lessonPlan->assessment = $request->assessment;
        $lessonPlan->homework = $request->homework;
        $lessonPlan->status = $request->input('action') == 'draft' ? 'draft' : 'published';
        $lessonPlan->save();

        return redirect()->route('teacher.lessons.show', $lessonPlan)
            ->with('success', 'Lesson plan created successfully.');
    }

    /**
     * Display the specified lesson plan.
     *
     * @param  \App\Models\LessonPlan  $lessonPlan
     * @return \Illuminate\View\View
     */
    public function show(LessonPlan $lesson)
    {
        $this->authorize('view', $lesson);
        
        return view('teacher.lessons.show', compact('lesson'));
    }

    /**
     * Show the form for editing the specified lesson plan.
     *
     * @param  \App\Models\LessonPlan  $lessonPlan
     * @return \Illuminate\View\View
     */
    public function edit(LessonPlan $lesson)
    {
        $this->authorize('update', $lesson);
        
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        
        return view('teacher.lessons.edit', compact('lesson', 'classes', 'subjects'));
    }

    /**
     * Update the specified lesson plan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LessonPlan  $lessonPlan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, LessonPlan $lesson)
    {
        $this->authorize('update', $lesson);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'duration' => 'required|integer|min:1',
            'date' => 'required|date',
            'objectives' => 'required|string',
            'description' => 'nullable|string',
            'resources' => 'nullable|string',
            'activities' => 'required|string',
            'assessment' => 'nullable|string',
            'homework' => 'nullable|string',
        ]);

        $lesson->title = $request->title;
        $lesson->class_id = $request->class_id;
        $lesson->subject_id = $request->subject_id;
        $lesson->duration = $request->duration;
        $lesson->date = $request->date;
        $lesson->objectives = $request->objectives;
        $lesson->description = $request->description;
        $lesson->resources = $request->resources;
        $lesson->activities = $request->activities;
        $lesson->assessment = $request->assessment;
        $lesson->homework = $request->homework;
        $lesson->save();

        return redirect()->route('teacher.lessons.show', $lesson)
            ->with('success', 'Lesson plan updated successfully.');
    }

    /**
     * Remove the specified lesson plan from storage.
     *
     * @param  \App\Models\LessonPlan  $lessonPlan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(LessonPlan $lesson)
    {
        $this->authorize('delete', $lesson);
        
        $lesson->delete();

        return redirect()->route('teacher.lessons.index')
            ->with('success', 'Lesson plan deleted successfully.');
    }
}