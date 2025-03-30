<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the assignments.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Assignment::query()->where('teacher_id', Auth::user()->teacher->id);

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

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $assignments = $query->latest()->paginate(10);
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();

        return view('teacher.assignments.index', compact('assignments', 'classes', 'subjects'));
    }

    /**
     * Show the form for creating a new assignment.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        $markingSchemes = \App\Models\MarkingScheme::where('teacher_id', Auth::user()->teacher->id)
            ->where('status', 'published')
            ->get();
        
        return view('teacher.assignments.create', compact('classes', 'subjects', 'markingSchemes'));
    }

    /**
     * Store a newly created assignment in storage.
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
            'description' => 'required|string',
            'instructions' => 'nullable|string',
            'total_marks' => 'required|integer|min:1',
            'due_date' => 'required|date|after:today',
            'file' => 'nullable|file|max:10240',
            'marking_scheme_id' => 'nullable|exists:marking_schemes,id',
            'allow_late_submissions' => 'nullable|boolean',
            'late_submission_deadline' => 'nullable|required_if:allow_late_submissions,1|date|after:due_date',
            'late_submission_deduction' => 'nullable|required_if:allow_late_submissions,1|integer|min:0|max:100',
        ]);

        $assignment = new Assignment();
        $assignment->title = $request->title;
        $assignment->class_id = $request->class_id;
        $assignment->subject_id = $request->subject_id;
        $assignment->teacher_id = Auth::user()->teacher->id;
        $assignment->description = $request->description;
        $assignment->instructions = $request->instructions;
        $assignment->total_marks = $request->total_marks;
        $assignment->due_date = $request->due_date;
        $assignment->marking_scheme_id = $request->marking_scheme_id;
        $assignment->status = $request->input('action') == 'draft' ? 'draft' : 'published';
        $assignment->allow_late_submissions = $request->has('allow_late_submissions');
        $assignment->late_submission_deadline = $request->late_submission_deadline;
        $assignment->late_submission_deduction = $request->late_submission_deduction;

        // Handle file upload
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('assignments', 'public');
            $assignment->file_path = $path;
        }

        $assignment->save();

        return redirect()->route('teacher.assignments.show', $assignment)
            ->with('success', 'Assignment created successfully.');
    }

    /**
     * Display the specified assignment.
     *
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\View\View
     */
    public function show(Assignment $assignment)
    {
        $this->authorize('view', $assignment);
        
        $submissions = Submission::where('assignment_id', $assignment->id)
            ->with('student.user')
            ->latest()
            ->get();
        
        return view('teacher.assignments.show', compact('assignment', 'submissions'));
    }

    /**
     * Show the form for editing the specified assignment.
     *
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\View\View
     */
    public function edit(Assignment $assignment)
    {
        $this->authorize('update', $assignment);
        
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        $markingSchemes = \App\Models\MarkingScheme::where('teacher_id', Auth::user()->teacher->id)
            ->where('status', 'published')
            ->get();
        
        return view('teacher.assignments.edit', compact('assignment', 'classes', 'subjects', 'markingSchemes'));
    }

    /**
     * Update the specified assignment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'required|string',
            'instructions' => 'nullable|string',
            'total_marks' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'file' => 'nullable|file|max:10240',
            'marking_scheme_id' => 'nullable|exists:marking_schemes,id',
            'allow_late_submissions' => 'nullable|boolean',
            'late_submission_deadline' => 'nullable|required_if:allow_late_submissions,1|date|after:due_date',
            'late_submission_deduction' => 'nullable|required_if:allow_late_submissions,1|integer|min:0|max:100',
        ]);

        $assignment->title = $request->title;
        $assignment->class_id = $request->class_id;
        $assignment->subject_id = $request->subject_id;
        $assignment->description = $request->description;
        $assignment->instructions = $request->instructions;
        $assignment->total_marks = $request->total_marks;
        $assignment->due_date = $request->due_date;
        $assignment->marking_scheme_id = $request->marking_scheme_id;
        $assignment->allow_late_submissions = $request->has('allow_late_submissions');
        $assignment->late_submission_deadline = $request->late_submission_deadline;
        $assignment->late_submission_deduction = $request->late_submission_deduction;

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($assignment->file_path) {
                Storage::disk('public')->delete($assignment->file_path);
            }
            
            $path = $request->file('file')->store('assignments', 'public');
            $assignment->file_path = $path;
        }

        $assignment->save();

        return redirect()->route('teacher.assignments.show', $assignment)
            ->with('success', 'Assignment updated successfully.');
    }

    /**
     * Remove the specified assignment from storage.
     *
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Assignment $assignment)
    {
        $this->authorize('delete', $assignment);
        
        // Delete file if exists
        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        // Delete related submissions
        foreach ($assignment->submissions as $submission) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $submission->delete();
        }

        $assignment->delete();

        return redirect()->route('teacher.assignments.index')
            ->with('success', 'Assignment deleted successfully.');
    }

    /**
     * Publish the specified assignment.
     *
     * @param  \App\Models\Assignment  $assignment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish(Assignment $assignment)
    {
        $this->authorize('update', $assignment);
        
        $assignment->status = 'published';
        $assignment->save();

        return redirect()->route('teacher.assignments.show', $assignment)
            ->with('success', 'Assignment published successfully.');
    }

    /**
     * Grade the specified submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Submission  $submission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function gradeSubmission(Request $request, Submission $submission)
    {
        $this->authorize('update', $submission->assignment);
        
        $request->validate([
            'marks' => 'required|integer|min:0|max:' . $submission->assignment->total_marks,
            'feedback' => 'nullable|string',
        ]);

        $submission->marks = $request->marks;
        $submission->feedback = $request->feedback;
        $submission->graded_at = now();
        $submission->graded_by = Auth::id();
        $submission->save();

        return redirect()->route('teacher.assignments.show', $submission->assignment)
            ->with('success', 'Submission graded successfully.');
    }
}