<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\InventoryItem;
use App\Models\Accounting\ItemCategory;
use App\Models\Accounting\StockMovement;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the inventory items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = InventoryItem::with('category');
        
        // Apply filters if any
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('search') && $request->search) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('item_code', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }
        
        // Include low stock filter
        if ($request->has('low_stock') && $request->low_stock) {
            $query->whereRaw('current_stock <= reorder_level');
        }
        
        // Get results
        $items = $query->orderBy('name')->paginate(15);
        $categories = ItemCategory::orderBy('name')->get();
        
        return view('accounting.inventory-items.index', compact('items', 'categories'));
    }

    /**
     * Show the form for creating a new inventory item.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = ItemCategory::where('is_active', true)->orderBy('name')->get();
        return view('accounting.inventory-items.create', compact('categories'));
    }

    /**
     * Store a newly created inventory item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:inventory_items',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:item_categories,id',
            'unit_of_measure' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'barcode' => 'nullable|string|max:100',
            'unit_cost' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_fixed_asset' => 'sometimes|boolean',
            'asset_type' => 'nullable|string|max:100',
            'depreciation_method' => 'nullable|string|max:100',
            'useful_life' => 'nullable|integer|min:0',
        ]);
        
        // Set boolean values
        $validated['is_active'] = $request->has('is_active');
        $validated['is_fixed_asset'] = $request->has('is_fixed_asset');
        
        // Create the item
        $item = InventoryItem::create($validated);
        
        // Create initial stock movement if stock > 0
        if ($item->current_stock > 0) {
            StockMovement::create([
                'item_id' => $item->id,
                'movement_type' => 'adjustment',
                'quantity' => $item->current_stock,
                'movement_date' => now(),
                'notes' => 'Initial stock on item creation',
                'created_by' => auth()->id(),
            ]);
        }
        
        return redirect()->route('accounting.inventory-items.index')
            ->with('success', 'Inventory item created successfully.');
    }

    /**
     * Display the specified inventory item.
     *
     * @param  \App\Models\Accounting\InventoryItem  $inventoryItem
     * @return \Illuminate\Http\Response
     */
    public function show(InventoryItem $inventoryItem)
    {
        // Load recent stock movements
        $stockMovements = $inventoryItem->stockMovements()
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();
            
        // Load recent purchase order items
        $purchaseOrderItems = $inventoryItem->purchaseOrderItems()
            ->with('purchaseOrder')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('accounting.inventory-items.show', compact(
            'inventoryItem', 
            'stockMovements', 
            'purchaseOrderItems'
        ));
    }

    /**
     * Show the form for editing the specified inventory item.
     *
     * @param  \App\Models\Accounting\InventoryItem  $inventoryItem
     * @return \Illuminate\Http\Response
     */
    public function edit(InventoryItem $inventoryItem)
    {
        $categories = ItemCategory::where('is_active', true)->orderBy('name')->get();
        return view('accounting.inventory-items.edit', compact('inventoryItem', 'categories'));
    }

    /**
     * Update the specified inventory item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\InventoryItem  $inventoryItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:inventory_items,item_code,' . $inventoryItem->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:item_categories,id',
            'unit_of_measure' => 'required|string|max:50',
            'reorder_level' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'barcode' => 'nullable|string|max:100',
            'unit_cost' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_fixed_asset' => 'sometimes|boolean',
            'asset_type' => 'nullable|string|max:100',
            'depreciation_method' => 'nullable|string|max:100',
            'useful_life' => 'nullable|integer|min:0',
        ]);
        
        // Set boolean values
        $validated['is_active'] = $request->has('is_active');
        $validated['is_fixed_asset'] = $request->has('is_fixed_asset');
        
        // Handle stock adjustments separately if provided
        if ($request->has('adjust_stock') && $request->adjust_stock) {
            $request->validate([
                'stock_adjustment' => 'required|numeric',
                'adjustment_note' => 'required|string|max:255',
            ]);
            
            // Create stock movement
            $movement = new StockMovement([
                'item_id' => $inventoryItem->id,
                'movement_type' => 'adjustment',
                'quantity' => abs($request->stock_adjustment),
                'movement_date' => now(),
                'notes' => $request->adjustment_note,
                'created_by' => auth()->id(),
            ]);
            
            // Update current stock
            $oldStock = $inventoryItem->current_stock;
            $inventoryItem->current_stock = max(0, $oldStock + $request->stock_adjustment);
            
            // Save both
            $inventoryItem->save();
            $movement->save();
        } else {
            // Just update the item details without changing stock
            $inventoryItem->update($validated);
        }
        
        return redirect()->route('accounting.inventory-items.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    /**
     * Remove the specified inventory item from storage.
     *
     * @param  \App\Models\Accounting\InventoryItem  $inventoryItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventoryItem $inventoryItem)
    {
        // Check if item has related records
        if ($inventoryItem->purchaseOrderItems()->exists() || 
            $inventoryItem->purchaseRequestItems()->exists() ||
            $inventoryItem->stockMovements()->exists()) {
            return back()->with('error', 'Cannot delete inventory item with related records.');
        }
        
        $inventoryItem->delete();
        
        return redirect()->route('accounting.inventory-items.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    /**
     * Display the stock history for an item
     *
     * @param  \App\Models\Accounting\InventoryItem  $inventoryItem
     * @return \Illuminate\Http\Response
     */
    public function stockHistory(InventoryItem $inventoryItem)
    {
        // Load all stock movements
        $stockMovements = $inventoryItem->stockMovements()
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('accounting.inventory-items.stock-history', compact('inventoryItem', 'stockMovements'));
    }
}