<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchemeOfWork;
use App\Models\SchemeWeek;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SchemeOfWorkController extends Controller
{
    /**
     * Display a listing of the schemes of work.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = SchemeOfWork::query()->where('teacher_id', Auth::user()->teacher->id);

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

        if ($request->has('term') && $request->term != '') {
            $query->where('term', $request->term);
        }

        if ($request->has('academic_year') && $request->academic_year != '') {
            $query->where('academic_year', $request->academic_year);
        }

        $schemes = $query->latest()->paginate(10);
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();

        return view('teacher.schemes.index', compact('schemes', 'classes', 'subjects'));
    }

    /**
     * Show the form for creating a new scheme of work.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        $currentYear = date('Y');
        $years = range($currentYear - 2, $currentYear + 2);
        
        return view('teacher.schemes.create', compact('classes', 'subjects', 'years'));
    }

    /**
     * Store a newly created scheme of work in storage.
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
            'term' => 'required|integer|min:1|max:3',
            'academic_year' => 'required|string|size:9',
            'description' => 'nullable|string',
            'objectives' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'file' => 'nullable|file|max:10240',
            'weeks' => 'required|array|min:1',
            'weeks.*.week_number' => 'required|integer|min:1',
            'weeks.*.topic' => 'required|string|max:255',
            'weeks.*.subtopics' => 'required|string',
            'weeks.*.learning_outcomes' => 'required|string',
            'weeks.*.teaching_activities' => 'required|string',
            'weeks.*.resources' => 'nullable|string',
            'weeks.*.assessment' => 'nullable|string',
        ]);

        $schemeOfWork = new SchemeOfWork();
        $schemeOfWork->title = $request->title;
        $schemeOfWork->class_id = $request->class_id;
        $schemeOfWork->subject_id = $request->subject_id;
        $schemeOfWork->teacher_id = Auth::user()->teacher->id;
        $schemeOfWork->term = $request->term;
        $schemeOfWork->academic_year = $request->academic_year;
        $schemeOfWork->description = $request->description;
        $schemeOfWork->objectives = $request->objectives;
        $schemeOfWork->start_date = $request->start_date;
        $schemeOfWork->end_date = $request->end_date;

        // Handle file upload
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('schemes', 'public');
            $schemeOfWork->file_path = $path;
        }

        $schemeOfWork->save();

        // Save weeks
        foreach ($request->weeks as $weekData) {
            $week = new SchemeWeek();
            $week->scheme_of_work_id = $schemeOfWork->id;
            $week->week_number = $weekData['week_number'];
            $week->topic = $weekData['topic'];
            $week->subtopics = $weekData['subtopics'];
            $week->learning_outcomes = $weekData['learning_outcomes'];
            $week->teaching_activities = $weekData['teaching_activities'];
            $week->resources = $weekData['resources'] ?? null;
            $week->assessment = $weekData['assessment'] ?? null;
            $week->save();
        }

        return redirect()->route('teacher.schemes.show', $schemeOfWork)
            ->with('success', 'Scheme of work created successfully.');
    }

    /**
     * Display the specified scheme of work.
     *
     * @param  \App\Models\SchemeOfWork  $schemeOfWork
     * @return \Illuminate\View\View
     */
    public function show(SchemeOfWork $scheme)
    {
        $this->authorize('view', $scheme);
        
        $weeks = SchemeWeek::where('scheme_of_work_id', $scheme->id)
            ->orderBy('week_number')
            ->get();
        
        return view('teacher.schemes.show', compact('scheme', 'weeks'));
    }

    /**
     * Show the form for editing the specified scheme of work.
     *
     * @param  \App\Models\SchemeOfWork  $schemeOfWork
     * @return \Illuminate\View\View
     */
    public function edit(SchemeOfWork $scheme)
    {
        $this->authorize('update', $scheme);
        
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        $currentYear = date('Y');
        $years = range($currentYear - 2, $currentYear + 2);
        
        $weeks = SchemeWeek::where('scheme_of_work_id', $scheme->id)
            ->orderBy('week_number')
            ->get();
        
        return view('teacher.schemes.edit', compact('scheme', 'weeks', 'classes', 'subjects', 'years'));
    }

    /**
     * Update the specified scheme of work in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SchemeOfWork  $schemeOfWork
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, SchemeOfWork $scheme)
    {
        $this->authorize('update', $scheme);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'term' => 'required|integer|min:1|max:3',
            'academic_year' => 'required|string|size:9',
            'description' => 'nullable|string',
            'objectives' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'file' => 'nullable|file|max:10240',
            'weeks' => 'required|array|min:1',
            'weeks.*.id' => 'nullable|exists:scheme_weeks,id',
            'weeks.*.week_number' => 'required|integer|min:1',
            'weeks.*.topic' => 'required|string|max:255',
            'weeks.*.subtopics' => 'required|string',
            'weeks.*.learning_outcomes' => 'required|string',
            'weeks.*.teaching_activities' => 'required|string',
            'weeks.*.resources' => 'nullable|string',
            'weeks.*.assessment' => 'nullable|string',
        ]);

        $scheme->title = $request->title;
        $scheme->class_id = $request->class_id;
        $scheme->subject_id = $request->subject_id;
        $scheme->term = $request->term;
        $scheme->academic_year = $request->academic_year;
        $scheme->description = $request->description;
        $scheme->objectives = $request->objectives;
        $scheme->start_date = $request->start_date;
        $scheme->end_date = $request->end_date;

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($scheme->file_path) {
                Storage::disk('public')->delete($scheme->file_path);
            }
            
            $path = $request->file('file')->store('schemes', 'public');
            $scheme->file_path = $path;
        }

        $scheme->save();

        // Update or create weeks
        $existingWeekIds = [];
        
        foreach ($request->weeks as $weekData) {
            if (isset($weekData['id'])) {
                // Update existing week
                $week = SchemeWeek::find($weekData['id']);
                if ($week && $week->scheme_of_work_id == $scheme->id) {
                    $week->week_number = $weekData['week_number'];
                    $week->topic = $weekData['topic'];
                    $week->subtopics = $weekData['subtopics'];
                    $week->learning_outcomes = $weekData['learning_outcomes'];
                    $week->teaching_activities = $weekData['teaching_activities'];
                    $week->resources = $weekData['resources'] ?? null;
                    $week->assessment = $weekData['assessment'] ?? null;
                    $week->save();
                    $existingWeekIds[] = $week->id;
                }
            } else {
                // Create new week
                $week = new SchemeWeek();
                $week->scheme_of_work_id = $scheme->id;
                $week->week_number = $weekData['week_number'];
                $week->topic = $weekData['topic'];
                $week->subtopics = $weekData['subtopics'];
                $week->learning_outcomes = $weekData['learning_outcomes'];
                $week->teaching_activities = $weekData['teaching_activities'];
                $week->resources = $weekData['resources'] ?? null;
                $week->assessment = $weekData['assessment'] ?? null;
                $week->save();
                $existingWeekIds[] = $week->id;
            }
        }

        // Delete weeks that are not in the request
        SchemeWeek::where('scheme_of_work_id', $scheme->id)
                ->whereNotIn('id', $existingWeekIds)
                ->delete();

        return redirect()->route('teacher.schemes.show', $scheme)
            ->with('success', 'Scheme of work updated successfully.');
    }

    /**
     * Remove the specified scheme of work from storage.
     *
     * @param  \App\Models\SchemeOfWork  $schemeOfWork
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(SchemeOfWork $scheme)
    {
        $this->authorize('delete', $scheme);
        
        // Delete file if exists
        if ($scheme->file_path) {
            Storage::disk('public')->delete($scheme->file_path);
        }

        // Delete related weeks
        SchemeWeek::where('scheme_of_work_id', $scheme->id)->delete();

        $scheme->delete();

        return redirect()->route('teacher.schemes.index')
            ->with('success', 'Scheme of work deleted successfully.');
    }

    /**
     * Download the scheme of work as PDF.
     *
     * @param  \App\Models\SchemeOfWork  $schemeOfWork
     * @return \Illuminate\Http\Response
     */
    public function download(SchemeOfWork $scheme)
    {
        $this->authorize('view', $scheme);
        
        $weeks = SchemeWeek::where('scheme_of_work_id', $scheme->id)
            ->orderBy('week_number')
            ->get();
        
        $pdf = PDF::loadView('teacher.schemes.pdf', compact('scheme', 'weeks'));
        return $pdf->download($scheme->title . '.pdf');
    }
}