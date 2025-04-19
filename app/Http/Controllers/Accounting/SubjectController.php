<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Subject; // Correct model path
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    // Optional: Add authorization checks using Policies if needed

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        $subjects = Subject::when($school_id, fn($q) => $q->where('school_id', $school_id))
                           ->orderBy('name')
                           ->paginate(20);

        return view('accounting.subjects.index', compact('subjects')); // Point to the view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.subjects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $school_id = Auth::user()?->school_id;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects')->where('school_id', $school_id)],
            'subject_code' => ['nullable', 'string', 'max:50', Rule::unique('subjects')->where('school_id', $school_id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['school_id'] = $school_id;
        $validated['is_active'] = $request->boolean('is_active', true); // Default to true if not sent

        try {
            Subject::create($validated);
            return redirect()->route('accounting.subjects.index')->with('success', 'Subject created successfully.');
        } catch (\Exception $e) {
            Log::error("Subject store failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create subject.');
        }
    }

    /**
     * Display the specified resource. (Optional - often just edit)
     */
    public function show(Subject $subject)
    {
        // Add authorization if needed
        // You might want to load related staff here: $subject->load('staff');
        return view('accounting.subjects.show', compact('subject'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject)
    {
        // Add authorization if needed
        return view('accounting.subjects.edit', compact('subject'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        // Add authorization if needed
        $school_id = $subject->school_id; // Use subject's school ID

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects')->where('school_id', $school_id)->ignore($subject->id)],
            'subject_code' => ['nullable', 'string', 'max:50', Rule::unique('subjects')->where('school_id', $school_id)->ignore($subject->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        try {
            $subject->update($validated);
            return redirect()->route('accounting.subjects.index')->with('success', 'Subject updated successfully.');
        } catch (\Exception $e) {
            Log::error("Subject update failed for ID {$subject->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update subject.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        // Add authorization if needed

        // Optional: Check if subject is assigned to staff before deleting
        // if ($subject->staff()->exists()) {
        //     return back()->with('error', 'Cannot delete subject assigned to staff members.');
        // }

        try {
            $subject->delete(); // Uses SoftDeletes if enabled on model
            return redirect()->route('accounting.subjects.index')->with('success', 'Subject deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Subject delete failed for ID {$subject->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to delete subject.');
        }
    }
}