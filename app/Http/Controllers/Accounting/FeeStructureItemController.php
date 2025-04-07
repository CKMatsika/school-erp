<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\FeeStructure;
use App\Models\Accounting\FeeStructureItem;
use App\Models\Accounting\ChartOfAccount; // Needed for validation
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FeeStructureItemController extends Controller
{
    /**
     * Store a newly created fee item within a fee structure.
     * Typically called from the FeeStructure show/edit page form.
     */
    public function store(Request $request, FeeStructure $feeStructure) // Route model binding for parent
    {
        // TODO: Add Authorization check - can user modify this fee structure?

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            // Ensure income account exists and is an income/revenue type
            'income_account_id' => [
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')->where(function ($query) {
                    // Optionally check if account is active and belongs to the correct school
                    $query->where('is_active', true);
                    // Add school check if CoA has school_id: ->where('school_id', $feeStructure->school_id);
                }),
                 // Further check if it's an Income/Revenue account
                 function ($attribute, $value, $fail) {
                     $account = ChartOfAccount::with('accountType')->find($value);
                     if (!$account || !in_array($account->accountType?->code, ['INCOME', 'REVENUE'])) {
                         $fail('The selected :attribute must be an Income or Revenue account.');
                     }
                 },
            ],
        ]);

        try {
            $feeStructure->items()->create([
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'income_account_id' => $validated['income_account_id'],
            ]);

            return redirect()->route('accounting.fee-structures.show', $feeStructure)
                   ->with('success', 'Fee item added successfully.');

        } catch (\Exception $e) {
            Log::error("Error adding fee item to structure {$feeStructure->id}: " . $e->getMessage());
             return redirect()->back() // Redirect back to the form
                   ->with('error', 'Failed to add fee item: ' . $e->getMessage())
                   ->withInput(); // Keep old input
        }
    }

    /**
     * Remove the specified fee item from storage.
     * Typically called via a form with DELETE method from FeeStructure show/edit page.
     */
    public function destroy(FeeStructure $feeStructure, FeeStructureItem $item) // Double route model binding
    {
         // TODO: Add Authorization check

        // Optional: Double check item belongs to the structure (though binding should handle it)
        if ($item->fee_structure_id !== $feeStructure->id) {
            abort(404); // Or redirect with error
        }

        try {
            $itemName = $item->name;
            $item->delete();

            return redirect()->route('accounting.fee-structures.show', $feeStructure)
                  ->with('success', "Fee item '{$itemName}' deleted successfully.");

        } catch (\Exception $e) {
             Log::error("Error deleting fee item {$item->id} from structure {$feeStructure->id}: " . $e->getMessage());
             return redirect()->route('accounting.fee-structures.show', $feeStructure)
                   ->with('error', 'Failed to delete fee item.');
        }
    }

    // You might add edit/update methods here later if you want a separate page/modal
    // to edit items, but often editing is done inline or by deleting/re-adding.
}