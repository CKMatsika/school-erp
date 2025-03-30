<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Content;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
     * Display a listing of the contents.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Content::query()->where('teacher_id', Auth::user()->teacher->id);

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

        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        $contents = $query->latest()->paginate(10);
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();

        return view('teacher.contents.index', compact('contents', 'classes', 'subjects'));
    }

    /**
     * Show the form for creating a new content.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        
        return view('teacher.contents.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created content in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:document,video,link,image',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
            'content_text' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
            'external_url' => 'nullable|url|max:255',
        ]);

        $content = new Content();
        $content->title = $request->title;
        $content->type = $request->type;
        $content->class_id = $request->class_id;
        $content->subject_id = $request->subject_id;
        $content->description = $request->description;
        $content->content_text = $request->content_text;
        $content->teacher_id = Auth::user()->teacher->id;
        $content->status = $request->input('action') == 'draft' ? 'draft' : 'published';
        $content->external_url = $request->external_url;

        // Handle file upload
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('contents', 'public');
            $content->file_path = $path;
        }

        $content->save();

        return redirect()->route('teacher.contents.show', $content)
            ->with('success', 'Content created successfully.');
    }

    /**
     * Display the specified content.
     *
     * @param  \App\Models\Content  $content
     * @return \Illuminate\View\View
     */
    public function show(Content $content)
    {
        $this->authorize('view', $content);
        
        return view('teacher.contents.show', compact('content'));
    }

    /**
     * Show the form for editing the specified content.
     *
     * @param  \App\Models\Content  $content
     * @return \Illuminate\View\View
     */
    public function edit(Content $content)
    {
        $this->authorize('update', $content);
        
        $classes = \App\Models\SchoolClass::all();
        $subjects = \App\Models\Subject::all();
        
        return view('teacher.contents.edit', compact('content', 'classes', 'subjects'));
    }

    /**
     * Update the specified content in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Content $content)
    {
        $this->authorize('update', $content);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:document,video,link,image',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
            'content_text' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
            'external_url' => 'nullable|url|max:255',
        ]);

        $content->title = $request->title;
        $content->type = $request->type;
        $content->class_id = $request->class_id;
        $content->subject_id = $request->subject_id;
        $content->description = $request->description;
        $content->content_text = $request->content_text;
        $content->external_url = $request->external_url;

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($content->file_path) {
                Storage::disk('public')->delete($content->file_path);
            }
            
            $path = $request->file('file')->store('contents', 'public');
            $content->file_path = $path;
        }

        $content->save();

        return redirect()->route('teacher.contents.show', $content)
            ->with('success', 'Content updated successfully.');
    }

    /**
     * Remove the specified content from storage.
     *
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Content $content)
    {
        $this->authorize('delete', $content);
        
        // Delete file if exists
        if ($content->file_path) {
            Storage::disk('public')->delete($content->file_path);
        }

        $content->delete();

        return redirect()->route('teacher.contents.index')
            ->with('success', 'Content deleted successfully.');
    }

    /**
     * Publish the specified content.
     *
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish(Content $content)
    {
        $this->authorize('update', $content);
        
        $content->status = 'published';
        $content->save();

        return redirect()->route('teacher.contents.show', $content)
            ->with('success', 'Content published successfully.');
    }
}