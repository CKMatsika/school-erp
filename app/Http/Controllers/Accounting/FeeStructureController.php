<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
// Removed FeeCategory as it seems items link directly to ChartOfAccount now
// use App\Models\Accounting\FeeCategory;
use App\Models\Accounting\FeeStructure;
use App\Models\Accounting\FeeStructureItem;
use App\Models\Student\AcademicYear; // Corrected Namespace assumed
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Added Auth
use Illuminate\Support\Facades\Log;   // Added Log
use Illuminate\Validation\Rule;       // Added Rule

class FeeStructureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        $feeStructures = FeeStructure::with('academicYear') // Eager load year
                                     ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                     ->orderBy('name')
                                     ->get(); // Consider pagination
        return view('accounting.fee-structures.index', compact('feeStructures'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $school_id = Auth::user()?->school_id;
         // Fetch academic years associated with the school
         $academicYears = AcademicYear::when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                     ->orderByDesc('start_date')
                                     ->get();
         // Removed $feeCategories as it's not used in the updated structure
        return view('accounting.fee-structures.create', compact('academicYears'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
             // Validate against academic_years table, check if it belongs to the school
            'academic_year_id' => [
                'nullable',
                'integer',
                 Rule::exists('academic_years', 'id')->where(function ($query) {
                    $query->where('school_id', Auth::user()?->school_id);
                 })
             ],
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean', // Handled by boolean() below
            // Removed item validation here, items are added on the show page now
            // 'items' => 'required|array|min:1',
            // 'items.*.fee_category_id' => 'required|exists:fee_categories,id',
            // ... other item fields ...
        ]);

        $school_id = Auth::user()?->school_id;

        DB::beginTransaction(); // Still good practice even without items initially
        try {
            // Create the fee structure header
            $feeStructure = FeeStructure::create([
                'school_id' => $school_id,
                'name' => $validated['name'],
                'academic_year_id' => $validated['academic_year_id'], // Use the validated ID
                'description' => $validated['description'],
                'is_active' => $request->boolean('is_active'), // Use boolean helper, defaults to false if not present
            ]);

            // Items are now added via FeeStructureItemController from the show page

            DB::commit();

            // Redirect to the SHOW page to allow adding items immediately
            return redirect()->route('accounting.fee-structures.show', $feeStructure)
                ->with('success', 'Fee Structure created successfully. You can now add items.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating fee structure: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating fee structure: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     * This now needs to pass income accounts for the "Add Item" form.
     */
    public function show(FeeStructure $feeStructure)
    {
         // TODO: Authorization Check (e.g., using the helper method if defined)
         $this->authorizeSchoolAccess($feeStructure);

         // Eager load items and their linked accounts & academic year
         $feeStructure->load(['items.incomeAccount', 'academicYear']);

         $school_id = Auth::user()?->school_id;

         // Get Income/Revenue accounts for the "Add Item" form dropdown
         $incomeAccounts = ChartOfAccount::whereHas('accountType', fn($q) => $q->whereIn('code', ['INCOME', 'REVENUE']))
                                    ->where('is_active', true)
                                    // Decide if CoA is school-specific or global
                                    // ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                    ->orderBy('name')
                                    ->get(['id', 'name', 'account_code']); // Select necessary fields


        return view('accounting.fee-structures.show', compact('feeStructure', 'incomeAccounts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FeeStructure $feeStructure)
    {
         // TODO: Authorization Check
         $this->authorizeSchoolAccess($feeStructure);

         $school_id = Auth::user()?->school_id;
         $academicYears = AcademicYear::when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                     ->orderByDesc('start_date')
                                     ->get();
         // Items are edited/deleted on the show page now, so no need to load them here.
         // Removed $feeCategories
        return view('accounting.fee-structures.edit', compact('feeStructure', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     * Items are now updated via FeeStructureItemController.
     */
    public function update(Request $request, FeeStructure $feeStructure)
    {
         // TODO: Authorization Check
         $this->authorizeSchoolAccess($feeStructure);

         $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => [
                'nullable',
                'integer',
                 Rule::exists('academic_years', 'id')->where(function ($query) {
                    $query->where('school_id', Auth::user()?->school_id);
                 })
             ],
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            // Removed item validation - handled separately
        ]);

        DB::beginTransaction(); // Use transaction for safety
        try {
            // Update the fee structure header only
            $feeStructure->update([
                'name' => $validated['name'],
                'academic_year_id' => $validated['academic_year_id'],
                'description' => $validated['description'],
                'is_active' => $request->boolean('is_active'),
            ]);

            // Items are managed via FeeStructureItemController

            DB::commit();

            // Redirect to the show page after update
            return redirect()->route('accounting.fee-structures.show', $feeStructure)
                ->with('success', 'Fee Structure updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
             Log::error("Error updating fee structure {$feeStructure->id}: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating fee structure: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeeStructure $feeStructure)
    {
         // TODO: Authorization Check
         $this->authorizeSchoolAccess($feeStructure);

        // Optional: Add check if used by invoices before allowing deletion
        // if (Invoice::where('fee_schedule_id', $feeStructure->id)->exists()) {
        //     return redirect()->route('accounting.fee-structures.index')->with('error', 'Cannot delete: Structure is used by invoices.');
        // }

        DB::beginTransaction();
        try {
            $structureName = $feeStructure->name;
            // Items should be deleted by cascade constraint if set up correctly.
            // If not, delete them manually first: $feeStructure->items()->delete();
            $feeStructure->delete();
            DB::commit();
            return redirect()->route('accounting.fee-structures.index')
                   ->with('success', "Fee Structure '{$structureName}' deleted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
             Log::error("Error deleting fee structure {$feeStructure->id}: " . $e->getMessage());
              return redirect()->route('accounting.fee-structures.index')
                   ->with('error', 'Failed to delete fee structure.');
        }
    }

     /**
     * Basic authorization check helper (Example)
     */
    private function authorizeSchoolAccess($model)
    {
        $userSchoolId = Auth::user()?->school_id;
        if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
            abort(403, 'Unauthorized action.');
        }
    }
}