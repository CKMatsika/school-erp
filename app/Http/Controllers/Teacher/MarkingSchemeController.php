<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarkingScheme;
use App\Models\Criterion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PDF;

class MarkingSchemeController extends Controller
{
    /**
     * Display a listing of the marking schemes.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = MarkingScheme::query()->where('teacher_id', Auth::user()->teacher->id);

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

        $markingSchemes = $query->latest()->paginate(10);
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();

        return view('teacher.marking-schemes.index', compact('markingSchemes', 'classes', 'subjects'));
    }

    /**
     * Show the form for creating a new marking scheme.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        
        return view('teacher.marking-schemes.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created marking scheme in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'assessment_type' => 'required|string|in:exam,test,quiz,assignment,project',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:1|lte:total_marks',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
            'criteria' => 'required|array|min:1',
            'criteria.*.name' => 'required|string|max:255',
            'criteria.*.description' => 'nullable|string',
            'criteria.*.marks' => 'required|integer|min:1',
            'criteria.*.weight' => 'required|integer|min:1|max:100',
        ]);

        $markingScheme = new MarkingScheme();
        $markingScheme->title = $request->title;
        $markingScheme->assessment_type = $request->assessment_type;
        $markingScheme->class_id = $request->class_id;
        $markingScheme->subject_id = $request->subject_id;
        $markingScheme->teacher_id = Auth::user()->teacher->id;
        $markingScheme->total_marks = $request->total_marks;
        $markingScheme->passing_marks = $request->passing_marks;
        $markingScheme->description = $request->description;
        $markingScheme->notes = $request->notes;
        $markingScheme->status = $request->input('action') == 'draft' ? 'draft' : 'published';

        // Handle file upload
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('marking-schemes', 'public');
            $markingScheme->file_path = $path;
        }

        $markingScheme->save();

        // Save marking criteria
        foreach ($request->criteria as $criterionData) {
            $criterion = new Criterion();
            $criterion->marking_scheme_id = $markingScheme->id;
            $criterion->name = $criterionData['name'];
            $criterion->description = $criterionData['description'] ?? null;
            $criterion->marks = $criterionData['marks'];
            $criterion->weight = $criterionData['weight'];
            $criterion->save();
        }

        return redirect()->route('teacher.marking-schemes.show', $markingScheme)
            ->with('success', 'Marking scheme created successfully.');
    }

    /**
     * Display the specified marking scheme.
     *
     * @param  \App\Models\MarkingScheme  $markingScheme
     * @return \Illuminate\View\View
     */
    public function show(MarkingScheme $markingScheme)
    {
        $this->authorize('view', $markingScheme);
        
        return view('teacher.marking-schemes.show', compact('markingScheme'));
    }

    /**
     * Show the form for editing the specified marking scheme.
     *
     * @param  \App\Models\MarkingScheme  $markingScheme
     * @return \Illuminate\View\View
     */
    public function edit(MarkingScheme $markingScheme)
    {
        $this->authorize('update', $markingScheme);
        
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        
        return view('teacher.marking-schemes.edit', compact('markingScheme', 'classes', 'subjects'));
    }

    /**
     * Update the specified marking scheme in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MarkingScheme  $markingScheme
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, MarkingScheme $markingScheme)
    {
        $this->authorize('update', $markingScheme);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'assessment_type' => 'required|string|in:exam,test,quiz,assignment,project',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:1|lte:total_marks',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
            'criteria' => 'required|array|min:1',
            'criteria.*.id' => 'nullable|exists:criteria,id',
            'criteria.*.name' => 'required|string|max:255',
            'criteria.*.description' => 'nullable|string',
            'criteria.*.marks' => 'required|integer|min:1',
            'criteria.*.weight' => 'required|integer|min:1|max:100',
        ]);

        $markingScheme->title = $request->title;
        $markingScheme->assessment_type = $request->assessment_type;
        $markingScheme->class_id = $request->class_id;
        $markingScheme->subject_id = $request->subject_id;
        $markingScheme->total_marks = $request->total_marks;
        $markingScheme->passing_marks = $request->passing_marks;
        $markingScheme->description = $request->description;
        $markingScheme->notes = $request->notes;

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($markingScheme->file_path) {
                Storage::disk('public')->delete($markingScheme->file_path);
            }
            
            $path = $request->file('file')->store('marking-schemes', 'public');
            $markingScheme->file_path = $path;
        }

        $markingScheme->save();

        // Update or create criteria
        $existingCriteriaIds = [];
        
        foreach ($request->criteria as $criterionData) {
            if (isset($criterionData['id'])) {
                // Update existing criterion
                $criterion = Criterion::find($criterionData['id']);
                if ($criterion && $criterion->marking_scheme_id == $markingScheme->id) {
                    $criterion->name = $criterionData['name'];
                    $criterion->description = $criterionData['description'] ?? null;
                    $criterion->marks = $criterionData['marks'];
                    $criterion->weight = $criterionData['weight'];
                    $criterion->save();
                    $existingCriteriaIds[] = $criterion->id;
                }
            } else {
                // Create new criterion
                $criterion = new Criterion();
                $criterion->marking_scheme_id = $markingScheme->id;
                $criterion->name = $criterionData['name'];
                $criterion->description = $criterionData['description'] ?? null;
                $criterion->marks = $criterionData['marks'];
                $criterion->weight = $criterionData['weight'];
                $criterion->save();
                $existingCriteriaIds[] = $criterion->id;
            }
        }

        // Delete criteria that are not in the request
        Criterion::where('marking_scheme_id', $markingScheme->id)
                ->whereNotIn('id', $existingCriteriaIds)
                ->delete();

        return redirect()->route('teacher.marking-schemes.show', $markingScheme)
            ->with('success', 'Marking scheme updated successfully.');
    }

    /**
     * Remove the specified marking scheme from storage.
     *
     * @param  \App\Models\MarkingScheme  $markingScheme
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(MarkingScheme $markingScheme)
    {
        $this->authorize('delete', $markingScheme);
        
        // Delete file if exists
        if ($markingScheme->file_path) {
            Storage::disk('public')->delete($markingScheme->file_path);
        }

        // Delete related criteria
        Criterion::where('marking_scheme_id', $markingScheme->id)->delete();

        $markingScheme->delete();

        return redirect()->route('teacher.marking-schemes.index')
            ->with('success', 'Marking scheme deleted successfully.');
    }

    /**
     * Publish the specified marking scheme.
     *
     * @param  \App\Models\MarkingScheme  $markingScheme
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish(MarkingScheme $markingScheme)
    {
        $this->authorize('update', $markingScheme);
        
        $markingScheme->status = 'published';
        $markingSch
        /**
     * Publish the specified marking scheme.
     *
     * @param  \App\Models\MarkingScheme  $markingScheme
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish(MarkingScheme $markingScheme)
    {
        $this->authorize('update', $markingScheme);
        
        $markingScheme->status = 'published';
        $markingScheme->save();

        return redirect()->route('teacher.marking-schemes.show', $markingScheme)
            ->with('success', 'Marking scheme published successfully.');
    }

    /**
     * Download the marking scheme as PDF.
     *
     * @param  \App\Models\MarkingScheme  $markingScheme
     * @return \Illuminate\Http\Response
     */
    public function download(MarkingScheme $markingScheme)
    {
        $this->authorize('view', $markingScheme);
        
        $pdf = PDF::loadView('teacher.marking-schemes.pdf', compact('markingScheme'));
        return $pdf->download($markingScheme->title . '.pdf');
    }
}