<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\SchoolClass; // Correct model namespace
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SchoolClassController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        $classes = SchoolClass::when($school_id, fn($q, $id)=>$q->where('school_id', $id))
                              ->orderBy('level')->orderBy('name')
                              ->get(); // Consider pagination
        return view('accounting.classes.index', compact('classes'));
    }

    /** Show the form for creating a new resource. */
    public function create()
    {
        return view('accounting.classes.create');
    }

    /** Store a newly created resource in storage. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:100',
            'capacity' => 'nullable|integer|min:0',
            'home_room' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $school_id = Auth::user()?->school_id;

        try {
            SchoolClass::create([
                'school_id' => $school_id,
                'name' => $validated['name'],
                'level' => $validated['level'],
                'capacity' => $validated['capacity'],
                'home_room' => $validated['home_room'],
                'notes' => $validated['notes'],
                'is_active' => $request->boolean('is_active'),
            ]);
            return redirect()->route('accounting.classes.index')->with('success', 'Class created successfully.');
        } catch (\Exception $e) {
             Log::error("Error creating class: ".$e->getMessage());
             return redirect()->back()->withInput()->with('error', 'Failed to create class.');
        }
    }

    /** Display the specified resource. (Optional) */
    public function show(SchoolClass $class) // Route model binding uses 'class' parameter name
    {
        $this->authorizeSchoolAccess($class);
        // Eager load students if needed for display
        // $class->load('students');
        return view('accounting.classes.show', compact('class'));
    }

    /** Show the form for editing the specified resource. */
    public function edit(SchoolClass $class)
    {
         $this->authorizeSchoolAccess($class);
        return view('accounting.classes.edit', compact('class'));
    }

    /** Update the specified resource in storage. */
    public function update(Request $request, SchoolClass $class)
    {
        $this->authorizeSchoolAccess($class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:100',
            'capacity' => 'nullable|integer|min:0',
            'home_room' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

         try {
             $class->update([
                'name' => $validated['name'],
                'level' => $validated['level'],
                'capacity' => $validated['capacity'],
                'home_room' => $validated['home_room'],
                'notes' => $validated['notes'],
                'is_active' => $request->boolean('is_active'),
            ]);
            return redirect()->route('accounting.classes.index')->with('success', 'Class updated successfully.');
        } catch (\Exception $e) {
             Log::error("Error updating class {$class->id}: ".$e->getMessage());
             return redirect()->back()->withInput()->with('error', 'Failed to update class.');
        }
    }

    /** Remove the specified resource from storage. */
    public function destroy(SchoolClass $class)
    {
        $this->authorizeSchoolAccess($class);
        // TODO: Add checks - are students enrolled in this class? Prevent deletion if so.
        // Example: if ($class->students()->exists()) { return back()->with('error', 'Cannot delete class with enrolled students.'); }

         try {
             $className = $class->name;
             $class->delete(); // Soft delete if model uses trait
             return redirect()->route('accounting.classes.index')->with('success', "Class '{$className}' deleted successfully.");
        } catch (\Exception $e) {
             Log::error("Error deleting class {$class->id}: ".$e->getMessage());
             return redirect()->route('accounting.classes.index')->with('error', 'Failed to delete class.');
        }
    }

    // Add the helper if not already present in this controller or a base controller
     private function authorizeSchoolAccess($model)
     {
         $userSchoolId = Auth::user()?->school_id;
         if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
             abort(403, 'Unauthorized action.');
         }
     }
}