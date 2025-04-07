<?php

namespace App\Http\Controllers\Accounting; // Ensure correct namespace

use App\Http\Controllers\Controller;
use App\Models\Student\AcademicYear; // Namespace for AcademicYear
use App\Models\Accounting\Term;       // Namespace for Term
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // If needed for authorization/school_id

class TermController extends Controller
{
    /**
     * Store a newly created term within an academic year.
     */
    public function store(Request $request, AcademicYear $academicYear) // Route model binding for parent
    {
        // TODO: Authorization - Check if user can modify this academicYear
        $this->authorizeSchoolAccess($academicYear);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:'.$academicYear->start_date->format('Y-m-d'),
            'end_date' => 'required|date|after:start_date|before_or_equal:'.$academicYear->end_date->format('Y-m-d'),
             // Add validation for is_active if you have it
        ]);

        try {
            $academicYear->terms()->create([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                // 'is_active' => $request->boolean('is_active'), // If you have this field
            ]);

            return redirect()->route('accounting.academic-years.show', $academicYear)
                   ->with('term_success', 'Term added successfully.'); // Use specific flash key

        } catch (\Exception $e) {
            Log::error("Error adding term to academic year {$academicYear->id}: " . $e->getMessage());
             return redirect()->back()
                   ->with('term_error', 'Failed to add term: ' . $e->getMessage()) // Use specific flash key
                   ->withInput()
                   ->withErrors($e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : [], 'store_term'); // Put errors in specific bag
        }
    }

    /**
     * Remove the specified term from storage.
     */
    public function destroy(AcademicYear $academicYear, Term $term) // Double route model binding
    {
        // TODO: Authorization
        $this->authorizeSchoolAccess($academicYear);

        // Optional: Double check term belongs to the year
        if ($term->academic_year_id !== $academicYear->id) abort(404);

        // TODO: Add checks - is this term used by fee structures, enrollments etc.?
        // Example: if($term->enrollments()->exists()){ return back()->with('term_error', 'Cannot delete term...'); }

        try {
            $termName = $term->name;
            $term->delete();

            return redirect()->route('accounting.academic-years.show', $academicYear)
                  ->with('term_success', "Term '{$termName}' deleted successfully.");

        } catch (\Exception $e) {
             Log::error("Error deleting term {$term->id} from year {$academicYear->id}: " . $e->getMessage());
             return redirect()->route('accounting.academic-years.show', $academicYear)
                   ->with('term_error', 'Failed to delete term.');
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