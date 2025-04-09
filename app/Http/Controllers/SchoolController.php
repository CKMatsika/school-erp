<?php

namespace App\Http\Controllers; // Adjust namespace if needed

use App\Models\School; // <-- Use the Eloquent Model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <-- Import Storage facade
use Illuminate\Support\Facades\Log;    // <-- Import Log facade
use Illuminate\Validation\Rule;       // <-- Import Rule facade for unique validation

class SchoolController extends Controller
{
    /**
     * Display a listing of schools.
     */
    public function index(Request $request) // Keep Request for potential future filtering
    {
        // Use Eloquent Model
        $schools = School::orderBy('name')->paginate(15); // Add pagination
        return view('schools.index', compact('schools'));
    }

    /**
     * Show the form for creating a new school.
     */
    public function create()
    {
        // Pass an empty school model for form model binding consistency (optional)
        $school = new School();
        return view('schools.create', compact('school'));
    }

    /**
     * Store a newly created school in storage.
     */
    public function store(Request $request)
    {
        // Use Request validation
        $validated = $request->validate([
            // Validation rules MUST match the columns that actually EXIST in your table
            'name' => 'required|string|max:255|unique:schools,name',
            'code' => 'required|string|max:50|unique:schools,code', // Assuming 'code' exists, is required and unique
            // 'email' validation REMOVED as column does not exist
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'principal_name' => 'nullable|string|max:255', // Example field
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Logo validation (2MB max)
            'is_active' => 'boolean', // Validate the is_active field if it comes from the form
            // Add validation for other existing columns like city, state, country, website, subscription dates etc.
            // 'city' => 'nullable|string|max:100',
            // 'website' => 'nullable|url|max:255',
            // 'subscription_start' => 'nullable|date',
            // 'subscription_end' => 'nullable|date|after_or_equal:subscription_start',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            try {
                $logoPath = $request->file('logo')->store('school_logos', 'public');
                $validated['logo_path'] = $logoPath; // Add path to validated data
            } catch (\Exception $e) {
                Log::error("School Logo Upload Failed (Store): " . $e->getMessage());
                return back()->withInput()->with('error', 'Failed to upload school logo.');
            }
        }

        // Remove the 'logo' file key as it's not a database column
        unset($validated['logo']);

        // Handle boolean 'is_active' correctly from request (if checkbox exists)
        // If no checkbox, it defaults based on DB or model ($fillable default if set)
        $validated['is_active'] = $request->boolean('is_active'); // Gets true/false from form input

        try {
            // Create using Eloquent Model - $validated now only contains valid columns
            School::create($validated);
            return redirect()->route('schools.index')->with('success', 'School created successfully.');
        } catch (\Exception $e) {
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath); // Clean up uploaded file on DB error
            }
            Log::error("School Store Failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create school.');
        }
    }

    /**
     * Display the specified school.
     */
    public function show(School $school) // Use Route Model Binding
    {
        return view('schools.show', compact('school'));
    }

    /**
     * Show the form for editing the specified school.
     */
    public function edit(School $school) // Use Route Model Binding
    {
        return view('schools.edit', compact('school'));
    }

    /**
     * Update the specified school in storage.
     */
    public function update(Request $request, School $school) // Use Route Model Binding
    {
         $validated = $request->validate([
            'name' => ['required','string','max:255', Rule::unique('schools')->ignore($school->id)],
            'code' => ['required','string','max:50', Rule::unique('schools')->ignore($school->id)], // Assuming 'code' exists
            // 'email' validation REMOVED as column does not exist
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'principal_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
            // Add validation for other existing columns...
        ]);

         $oldLogoPath = $school->logo_path; // Store old path before potential update

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            try {
                 // Store the new file first
                 $newLogoPath = $request->file('logo')->store('school_logos', 'public');
                 $validated['logo_path'] = $newLogoPath; // Add new path to validated data

                 // Delete the old logo *after* successfully storing the new one
                 if ($oldLogoPath && Storage::disk('public')->exists($oldLogoPath)) {
                    Storage::disk('public')->delete($oldLogoPath);
                 }
            } catch (\Exception $e) {
                Log::error("School Logo Upload Failed (Update): " . $e->getMessage());
                unset($validated['logo_path']); // Ensure logo_path isn't updated if upload fails
                session()->flash('warning', 'Failed to update school logo file, other details were saved.'); // Inform user
            }
        }

        // Remove the 'logo' file key as it's not the database column
        unset($validated['logo']);
        // Handle boolean for is_active
        $validated['is_active'] = $request->boolean('is_active');

        try {
            // Update using Eloquent Model - $validated now only contains valid columns
            $school->update($validated);
            return redirect()->route('schools.index')->with('success', 'School updated successfully.');
        } catch (\Exception $e) {
            Log::error("School Update Failed for ID {$school->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update school.');
        }
    }

    /**
     * Remove the specified school from storage.
     */
    public function destroy(School $school) // Use Route Model Binding
    {
        // Add authorization checks: e.g., Gate::authorize('delete', $school);

        // Add checks: Can this school be deleted? (e.g., check for related users, students, etc.)
        // if ($school->users()->exists() || /* other checks */ ) {
        //     return redirect()->route('schools.index')->with('error', 'Cannot delete school with associated users/data.');
        // }

        try {
            // Delete the logo file first
             if ($school->logo_path && Storage::disk('public')->exists($school->logo_path)) {
                 Storage::disk('public')->delete($school->logo_path);
             }

            // Delete the school record (uses soft delete if enabled in model)
            $school->delete();

            return redirect()->route('schools.index')->with('success', 'School deleted successfully.');

        } catch (\Exception $e) {
             Log::error("School Deletion Failed for ID {$school->id}: " . $e->getMessage());
             return back()->with('error', 'Failed to delete school.');
        }
    }
}