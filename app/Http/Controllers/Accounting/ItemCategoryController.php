<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = ItemCategory::with('parent')
            ->orderBy('name')
            ->paginate(15);
            
        return view('accounting.item-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parentCategories = ItemCategory::orderBy('name')->get();
        return view('accounting.item-categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:item_categories,id',
            'is_active' => 'sometimes|boolean',
        ]);
        
        // Set is_active default
        $validated['is_active'] = $request->has('is_active');
        
        ItemCategory::create($validated);
        
        return redirect()->route('accounting.item-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     *
     * @param  \App\Models\Accounting\ItemCategory  $itemCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ItemCategory $itemCategory)
    {
        // Load related items
        $items = $itemCategory->items()->paginate(10);
        
        // Load sub-categories
        $subCategories = $itemCategory->children()->get();
        
        return view('accounting.item-categories.show', compact('itemCategory', 'items', 'subCategories'));
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param  \App\Models\Accounting\ItemCategory  $itemCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ItemCategory $itemCategory)
    {
        $parentCategories = ItemCategory::where('id', '!=', $itemCategory->id)
            ->orderBy('name')
            ->get();
            
        return view('accounting.item-categories.edit', compact('itemCategory', 'parentCategories'));
    }

    /**
     * Update the specified category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\ItemCategory  $itemCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ItemCategory $itemCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable',
                'exists:item_categories,id',
                function ($attribute, $value, $fail) use ($itemCategory) {
                    if ($value == $itemCategory->id) {
                        $fail('A category cannot be its own parent.');
                    }
                }
            ],
            'is_active' => 'sometimes|boolean',
        ]);
        
        // Set is_active default
        $validated['is_active'] = $request->has('is_active');
        
        $itemCategory->update($validated);
        
        return redirect()->route('accounting.item-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  \App\Models\Accounting\ItemCategory  $itemCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ItemCategory $itemCategory)
    {
        // Check if category has items or sub-categories
        if ($itemCategory->items()->exists() || $itemCategory->children()->exists()) {
            return back()->with('error', 'Cannot delete category with items or sub-categories.');
        }
        
        $itemCategory->delete();
        
        return redirect()->route('accounting.item-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}