<?php

namespace App\Http\Controllers\Accounting; // Controller is in Accounting

use App\Http\Controllers\Controller;
// *** CORRECT THE MODEL NAMESPACE HERE ***
use App\Models\Student\AcademicYear; // Likely correct - adjust if your model is in App\Models\AcademicYear
// *** END CORRECTION ***
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- Import DB facade
use Illuminate\Support\Facades\Log; // <-- Import Log facade
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        $academicYears = AcademicYear::when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                    ->orderByDesc('start_date')
                                    ->get(); // Consider pagination

        // *** CORRECT VIEW PATH ***
        return view('accounting.academic-years.index', compact('academicYears'));
    }

    /** Show the form for creating a new resource. */
    public function create()
    {
        // *** CORRECT VIEW PATH ***
        return view('accounting.academic-years.create');
    }

    /** Store a newly created resource in storage. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        $school_id = Auth::user()?->school_id;

        try { // Wrap in try-catch
            DB::transaction(function () use ($validated, $school_id, $request) {
                if ($request->boolean('is_current')) {
                    AcademicYear::when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                ->update(['is_current' => false]);
                }

                AcademicYear::create([
                    'school_id' => $school_id,
                    'name' => $validated['name'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'is_current' => $request->boolean('is_current'),
                ]);
            });

             // *** CORRECT ROUTE NAME ***
            return redirect()->route('accounting.academic-years.index')->with('success', 'Academic Year created.');

        } catch (\Exception $e) {
             Log::error("Error creating academic year: ".$e->getMessage());
             return redirect()->back()->withInput()->with('error', 'Failed to create academic year.');
        }
    }

    /** Display the specified resource. */
    public function show(AcademicYear $academicYear) // Use route model binding
    {
        // TODO: Authorization
        $this->authorizeSchoolAccess($academicYear);

        // Load terms if needed for the view
        $academicYear->load('terms'); // Make sure Term model and relationship exist

        // *** CORRECT VIEW PATH ***
        // Also pass data needed for Add Term form (if implemented here)
        return view('accounting.academic-years.show', compact('academicYear'));
    }

    /** Show the form for editing the specified resource. */
    public function edit(AcademicYear $academicYear) // Use route model binding
    {
        // TODO: Authorization
        $this->authorizeSchoolAccess($academicYear);
        // *** CORRECT VIEW PATH ***
        return view('accounting.academic-years.edit', compact('academicYear'));
    }

    /** Update the specified resource in storage. */
    public function update(Request $request, AcademicYear $academicYear) // Use route model binding
    {
        // TODO: Authorization
        $this->authorizeSchoolAccess($academicYear);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        try { // Wrap in try-catch
             DB::transaction(function () use ($validated, $request, $academicYear) {
                 if ($request->boolean('is_current')) {
                      $school_id = Auth::user()?->school_id;
                      AcademicYear::when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                  ->where('id', '!=', $academicYear->id)
                                  ->update(['is_current' => false]);
                 }

                 $academicYear->update([
                     'name' => $validated['name'],
                     'start_date' => $validated['start_date'],
                     'end_date' => $validated['end_date'],
                     'is_current' => $request->boolean('is_current'),
                 ]);
             });

            // *** CORRECT ROUTE NAME ***
            return redirect()->route('accounting.academic-years.index')->with('success', 'Academic Year updated.');

        } catch (\Exception $e) {
             Log::error("Error updating academic year {$academicYear->id}: ".$e->getMessage());
             return redirect()->back()->withInput()->with('error', 'Failed to update academic year.');
        }
    }

    /** Remove the specified resource from storage. */
    public function destroy(AcademicYear $academicYear) // Use route model binding
    {
        // TODO: Authorization
        $this->authorizeSchoolAccess($academicYear);

        // TODO: Add checks - is it used by enrollments, fee structures etc.? Prevent deletion if used.
        // Example: if ($academicYear->enrollments()->exists() || $academicYear->feeStructures()->exists()) { return back()->with('error', 'Cannot delete: Academic year is in use.'); }

        try {
             $academicYear->delete(); // Soft delete if model uses SoftDeletes
             // *** CORRECT ROUTE NAME ***
             return redirect()->route('accounting.academic-years.index')->with('success', 'Academic Year deleted.');
        } catch (\Exception $e) {
             Log::error("Error deleting academic year {$academicYear->id}: ".$e->getMessage());
             // *** CORRECT ROUTE NAME ***
             return redirect()->route('accounting.academic-years.index')->with('error', 'Failed to delete academic year.');
        }
    }

     // Basic authorization helper
     private function authorizeSchoolAccess($model) {
         $userSchoolId = Auth::user()?->school_id;
         if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
             abort(403, 'Unauthorized action.');
         }
     }
}