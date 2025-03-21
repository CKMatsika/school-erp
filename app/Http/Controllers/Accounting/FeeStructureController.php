<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FeeCategory;
use App\Models\Accounting\FeeStructure;
use App\Models\Accounting\FeeStructureItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeStructureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feeStructures = FeeStructure::all();
        return view('accounting.fee-structures.index', compact('feeStructures'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $feeCategories = FeeCategory::where('is_active', true)->get();
        return view('accounting.fee-structures.create', compact('feeCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:20',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.fee_category_id' => 'required|exists:fee_categories,id',
            'items.*.grade_level' => 'nullable|string',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.frequency' => 'required|in:once,term,monthly,annually',
            'items.*.due_date' => 'nullable|date',
        ]);

        // Get school ID if available
        $schoolId = null;
        if (auth()->user()->school) {
            $schoolId = auth()->user()->school->id;
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the fee structure
            $feeStructure = FeeStructure::create([
                'school_id' => $schoolId,
                'name' => $request->name,
                'academic_year' => $request->academic_year,
                'description' => $request->description,
                'is_active' => true,
            ]);

            // Create the fee structure items
            foreach ($request->items as $item) {
                FeeStructureItem::create([
                    'fee_structure_id' => $feeStructure->id,
                    'fee_category_id' => $item['fee_category_id'],
                    'grade_level' => $item['grade_level'] ?? null,
                    'amount' => $item['amount'],
                    'frequency' => $item['frequency'],
                    'due_date' => $item['due_date'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('accounting.fee-structures.index')
                ->with('success', 'Fee structure created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating fee structure: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FeeStructure $feeStructure)
    {
        $feeStructure->load(['items.feeCategory']);
        return view('accounting.fee-structures.show', compact('feeStructure'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FeeStructure $feeStructure)
    {
        $feeStructure->load(['items.feeCategory']);
        $feeCategories = FeeCategory::where('is_active', true)->get();
        
        return view('accounting.fee-structures.edit', compact('feeStructure', 'feeCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FeeStructure $feeStructure)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:fee_structure_items,id',
            'items.*.fee_category_id' => 'required|exists:fee_categories,id',
            'items.*.grade_level' => 'nullable|string',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.frequency' => 'required|in:once,term,monthly,annually',
            'items.*.due_date' => 'nullable|date',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the fee structure
            $feeStructure->update([
                'name' => $request->name,
                'academic_year' => $request->academic_year,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            // Get the IDs of existing items
            $existingItemIds = $feeStructure->items->pluck('id')->toArray();
            $updatedItemIds = [];

            // Update or create the fee structure items
            foreach ($request->items as $itemData) {
                if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                    // Update existing item
                    $item = FeeStructureItem::find($itemData['id']);
                    $item->update([
                        'fee_category_id' => $itemData['fee_category_id'],
                        'grade_level' => $itemData['grade_level'] ?? null,
                        'amount' => $itemData['amount'],
                        'frequency' => $itemData['frequency'],
                        'due_date' => $itemData['due_date'] ?? null,
                    ]);
                    $updatedItemIds[] = $item->id;
                } else {
                    // Create new item
                    $item = FeeStructureItem::create([
                        'fee_structure_id' => $feeStructure->id,
                        'fee_category_id' => $itemData['fee_category_id'],
                        'grade_level' => $itemData['grade_level'] ?? null,
                        'amount' => $itemData['amount'],
                        'frequency' => $itemData['frequency'],
                        'due_date' => $itemData['due_date'] ?? null,
                    ]);
                    $updatedItemIds[] = $item->id;
                }
            }

            // Delete removed items
            foreach ($existingItemIds as $itemId) {
                if (!in_array($itemId, $updatedItemIds)) {
                    FeeStructureItem::destroy($itemId);
                }
            }

            DB::commit();

            return redirect()->route('accounting.fee-structures.show', $feeStructure)
                ->with('success', 'Fee structure updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
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
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Delete the fee structure items
            $feeStructure->items()->delete();
            
            // Delete the fee structure
            $feeStructure->delete();

            DB::commit();

            return redirect()->route('accounting.fee-structures.index')
                ->with('success', 'Fee structure deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('accounting.fee-structures.index')
                ->with('error', 'Error deleting fee structure: ' . $e->getMessage());
        }
    }
}