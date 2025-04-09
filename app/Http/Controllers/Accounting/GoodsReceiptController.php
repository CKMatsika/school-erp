<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\GoodsReceipt;
use App\Models\Accounting\GoodsReceiptItem;
use App\Models\Accounting\PurchaseOrder;
use App\Models\Accounting\PurchaseOrderItem;
use App\Models\Accounting\InventoryItem;
use App\Models\Accounting\StockMovement;
use App\Models\Accounting\Supplier; // <-- Import Supplier model
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <-- Import Auth facade
use Illuminate\Support\Facades\Log;   // <-- Import Log facade

class GoodsReceiptController extends Controller
{
    /**
     * Display a listing of the goods receipts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // Start query builder, eager load necessary relationships for the table
        $query = GoodsReceipt::with([
            // Load PO and Supplier together
            'purchaseOrder' => function($poQuery) {
                $poQuery->with('supplier:id,name'); // Select only needed supplier columns
            },
            'creator:id,name' // Select only needed creator columns
        ]);

        // --- Apply Filters based on the index.blade.php form ---
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('receipt_number', 'like', "%{$searchTerm}%")
                  ->orWhere('delivery_note', 'like', "%{$searchTerm}%") // Match column name in migration
                  // Search related PO Number
                  ->orWhereHas('purchaseOrder', function($poQuery) use ($searchTerm) {
                      $poQuery->where('po_number', 'like', "%{$searchTerm}%");
                  })
                   // Search related Supplier Name
                  ->orWhereHas('purchaseOrder.supplier', function($supQuery) use ($searchTerm) {
                       $supQuery->where('name', 'like', "%{$searchTerm}%");
                  });
                 // Add search by received_by if it's a direct column (e.g., 'received_by_name')
                 // ->orWhere('received_by', 'like', "%{$searchTerm}%");
            });
       }

        if ($request->filled('supplier_id')) {
            // Filter by supplier through the purchaseOrder relationship
            $query->whereHas('purchaseOrder', function($poQuery) use ($request) {
                $poQuery->where('supplier_id', $request->supplier_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('receipt_date', '>=', $request->date_from);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('receipt_date', '<=', $request->date_to);
        }
        // --- End Filters ---


        // Get paginated results, keeping filters in pagination links
        $receipts = $query->latest('receipt_date')->paginate(15)->withQueryString();

        // Fetch suppliers for the filter dropdown
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']); // Select only needed columns
        // $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']); // Use if 'is_active' exists on Supplier

        // Pass data to the view
        return view('accounting.goods-receipts.index', compact(
            'receipts',
            'suppliers' // <-- Pass suppliers variable
        ));
    }

    /**
     * Show the form for creating a new goods receipt.
     * NOTE: The index view suggests this might be initiated from the PO index/show page.
     * This method might only show a selection of POs if no specific po_id is given.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        // Required: A Purchase Order ID to create a GRN against
        if (!$request->has('po_id')) {
             // If no PO specified, maybe redirect to PO list to select one
             return redirect()->route('accounting.purchase-orders.index', ['status' => 'approved']) // Filter for approved/issued POs
                ->with('info', 'Please select an approved Purchase Order to record a receipt.');
        }

        $purchaseOrder = PurchaseOrder::with(['supplier', 'items' => function ($q) {
                // Eager load items that still need receiving
                 $q->whereRaw('quantity > quantity_received')->with('inventoryItem:id,item_code'); // Only load needed item fields
            }])
            ->whereIn('status', ['approved', 'issued', 'partial']) // Only receivable statuses
            ->find($request->po_id);

        // Handle case where PO not found or not receivable
        if (!$purchaseOrder) {
             return redirect()->route('accounting.purchase-orders.index')
                ->with('error', 'Purchase Order not found or cannot be received against.');
        }
        if ($purchaseOrder->items->isEmpty()) { // Check if all items are already received
             return redirect()->route('accounting.purchase-orders.show', $purchaseOrder->id)
                 ->with('info', 'All items for this Purchase Order have already been fully received.');
        }


        // Generate receipt number
        $latestReceipt = GoodsReceipt::latest('id')->first();
        $nextId = $latestReceipt ? $latestReceipt->id + 1 : 1;
        $receiptNumber = 'GRN-' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Pass the specific purchase order to the view
        return view('accounting.goods-receipts.create', compact('purchaseOrder', 'receiptNumber'));
    }

    /**
     * Store a newly created goods receipt header.
     * Items are added in a separate step via the 'items' or 'storeItem' methods typically.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Removed receipt_number validation if it's auto-generated
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'receipt_date' => 'required|date',
            'delivery_note' => 'nullable|string|max:100', // Matched migration
            'received_by' => 'required|string|max:100', // Matched migration
            'notes' => 'nullable|string',
        ]);

        // Generate receipt number before creating
        $latestReceipt = GoodsReceipt::latest('id')->first();
        $nextId = $latestReceipt ? $latestReceipt->id + 1 : 1;
        $validated['receipt_number'] = 'GRN-' . Carbon::parse($validated['receipt_date'])->format('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Validate uniqueness of generated number (rare race condition, but possible)
         if (GoodsReceipt::where('receipt_number', $validated['receipt_number'])->exists()) {
             // Handle collision - maybe regenerate or add suffix? Or just error out.
             return back()->withInput()->with('error', 'Failed to generate unique receipt number. Please try again.');
         }


        // Check if the related PO can still be received against
        $purchaseOrder = PurchaseOrder::find($validated['purchase_order_id']);
        if (!$purchaseOrder || !in_array($purchaseOrder->status, ['approved', 'issued', 'partial'])) {
            return back()->withInput()->with('error', 'The selected Purchase Order cannot be received against anymore.');
        }

        // Set defaults
        $validated['status'] = 'draft'; // Start as draft, items need adding
        $validated['created_by'] = auth()->id();

        try {
            $goodsReceipt = GoodsReceipt::create($validated);

            // Redirect to the page to add/confirm items for this specific GRN
            return redirect()->route('accounting.goods-receipts.items', $goodsReceipt)
                ->with('success', 'Goods receipt header created. Please confirm received items.');

        } catch (\Exception $e) {
            Log::error("GRN Store failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create goods receipt.');
        }
    }

    /**
     * Display the specified goods receipt.
     *
     * @param  \App\Models\Accounting\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Contracts\View\View
     */
    public function show(GoodsReceipt $goodsReceipt)
    {
        // Load necessary relationships for the view
        $goodsReceipt->load([
            'purchaseOrder.supplier', // PO and its supplier
            // Load GRN items, and through them the PO item, and through that the Inventory Item
            'items' => function($q){
                $q->with(['purchaseOrderItem' => function($poItemQuery){
                    $poItemQuery->with('inventoryItem:id,item_code,name'); // Select needed columns
                }]);
            },
            'creator:id,name' // User who created the GRN
        ]);

        return view('accounting.goods-receipts.show', compact('goodsReceipt'));
    }

    /**
     * Show the form for adding/editing items on a draft goods receipt.
     *
     * @param  \App\Models\Accounting\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function items(GoodsReceipt $goodsReceipt)
    {
        // Check if goods receipt is still in draft status
        if ($goodsReceipt->status !== 'draft') {
            return redirect()->route('accounting.goods-receipts.show', $goodsReceipt)
                ->with('error', 'Items can only be managed on draft Goods Receipts.');
        }

        // Load the related Purchase Order and its items that still need receiving
        $purchaseOrder = $goodsReceipt->load([
            'purchaseOrder' => function($poQuery) {
                $poQuery->with(['supplier', 'items' => function ($poiQuery) {
                    // Load PO items with remaining quantity > 0 OR items already on *this* GRN
                    // Eager load inventory item details
                     $poiQuery->with('inventoryItem:id,item_code,name,unit_of_measure');
                }]);
            }
        ])->purchaseOrder; // Access the loaded PO

         // Filter PO items: show items already on this GRN + items with outstanding quantity
         $grnPoItemIds = $goodsReceipt->items->pluck('purchase_order_item_id')->toArray();
         $poItems = $purchaseOrder->items->filter(function ($item) use ($grnPoItemIds) {
             return $item->remaining_quantity > 0 || in_array($item->id, $grnPoItemIds);
         });

        // Get items already added to *this* GRN to pre-fill the form
        $receivedItemsData = $goodsReceipt->items->keyBy('purchase_order_item_id');


        return view('accounting.goods-receipts.items', compact(
            'goodsReceipt',
            'purchaseOrder',
            'poItems', // List of PO items to potentially receive against
            'receivedItemsData' // Data of items already added to this specific GRN
        ));
    }

     /**
     * Update items received on a draft goods receipt (handles adding/editing/removing).
     * This replaces storeItem and potentially needs a dedicated route like goods-receipts/{receipt}/items/update (PUT/PATCH)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateItems(Request $request, GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->status !== 'draft') {
            return redirect()->route('accounting.goods-receipts.show', $goodsReceipt)
                ->with('error', 'Items can only be updated on draft Goods Receipts.');
        }

        $validated = $request->validate([
            'items' => 'nullable|array', // Items are optional, user might just want to complete/cancel
            'items.*.purchase_order_item_id' => 'required_with:items.*.quantity_received|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required_with:items.*.purchase_order_item_id|nullable|numeric|min:0', // Allow 0 to remove/not receive
            'items.*.quantity_rejected' => 'nullable|numeric|min:0',
            'items.*.rejection_reason' => 'nullable|required_if:items.*.quantity_rejected,>,0|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $submittedPoItemIds = [];
            if (isset($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    // Skip if no quantity received or rejected for this potential item
                    $qtyReceived = $itemData['quantity_received'] ?? 0;
                    $qtyRejected = $itemData['quantity_rejected'] ?? 0;
                    if ($qtyReceived <= 0 && $qtyRejected <= 0) {
                        // If quantities are zero, treat it as removing the item if it exists on the GRN
                         $existingItem = $goodsReceipt->items()->where('purchase_order_item_id', $itemData['purchase_order_item_id'])->first();
                         if ($existingItem) {
                             $existingItem->delete();
                         }
                        continue; // Skip to next item
                    }

                     // Validate quantity against remaining on PO item
                    $poItem = PurchaseOrderItem::find($itemData['purchase_order_item_id']);
                    if (!$poItem || $poItem->purchase_order_id !== $goodsReceipt->purchase_order_id) {
                        throw new \Exception("Invalid Purchase Order Item ID ({$itemData['purchase_order_item_id']}) submitted.");
                    }

                    // Calculate remaining quantity *excluding* any quantity already on *this specific* GRN being edited
                    $alreadyOnThisGrn = $goodsReceipt->items()
                                        ->where('purchase_order_item_id', $poItem->id)
                                        ->first()?->quantity_received ?? 0;
                    $qtyAvailableToReceive = ($poItem->quantity - $poItem->quantity_received) + $alreadyOnThisGrn;


                    if (($qtyReceived + $qtyRejected) > $qtyAvailableToReceive + 0.001) { // Add small tolerance for float comparison
                         throw new \Exception("Total quantity for item {$poItem->description} (".($qtyReceived + $qtyRejected).") exceeds remaining available quantity ({$qtyAvailableToReceive}).");
                    }


                    // Update or Create the GoodsReceiptItem
                    $goodsReceipt->items()->updateOrCreate(
                        ['purchase_order_item_id' => $itemData['purchase_order_item_id']], // Find by po_item_id for this GRN
                        [
                            'quantity_received' => $qtyReceived,
                            'quantity_rejected' => $qtyRejected,
                            'rejection_reason' => ($qtyRejected > 0) ? ($itemData['rejection_reason'] ?? null) : null, // Only store reason if rejected > 0
                        ]
                    );
                     $submittedPoItemIds[] = $itemData['purchase_order_item_id'];
                }
            }

             // Remove items from GRN that were not included in the submission (if any were submitted)
            if (isset($validated['items'])) {
                $goodsReceipt->items()->whereNotIn('purchase_order_item_id', $submittedPoItemIds)->delete();
            }


            // Update GRN status if needed (optional - might require explicit "Complete" action)
            // if ($goodsReceipt->items()->count() > 0 && $goodsReceipt->status === 'draft') {
            //     $goodsReceipt->status = 'in_progress'; // Or maybe keep draft until completion?
            //     $goodsReceipt->save();
            // } elseif ($goodsReceipt->items()->count() === 0 && $goodsReceipt->status !== 'draft') {
            //      $goodsReceipt->status = 'draft'; // Revert if all items removed?
            //      $goodsReceipt->save();
            // }


            DB::commit();

            // Decide where to redirect - back to items page is usually best after update
            return redirect()->route('accounting.goods-receipts.items', $goodsReceipt)
                ->with('success', 'Received items updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
             return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("GRN Item Update failed for GRN ID {$goodsReceipt->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update items: '. $e->getMessage());
        }
    }


    /**
     * Complete a draft goods receipt and update inventory/PO status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request, GoodsReceipt $goodsReceipt)
    {
        // Ensure it's in a state that can be completed (e.g., 'draft' or 'in_progress')
        if (!in_array($goodsReceipt->status, ['draft', 'in_progress'])) { // Assuming 'in_progress' if items were added/edited
            return redirect()->route('accounting.goods-receipts.show', $goodsReceipt)
                ->with('error', 'This goods receipt cannot be completed (Status: ' . $goodsReceipt->status . ').');
        }

         // Ensure there are items on the receipt
        if ($goodsReceipt->items()->count() === 0) {
             return redirect()->route('accounting.goods-receipts.items', $goodsReceipt)
                 ->with('error', 'Cannot complete an empty goods receipt. Please add items received.');
        }

        try {
            DB::beginTransaction();

            // Load relationships needed
            $goodsReceipt->load('items.purchaseOrderItem.inventoryItem', 'purchaseOrder');
            $purchaseOrder = $goodsReceipt->purchaseOrder;

            // Update inventory and PO items
            foreach ($goodsReceipt->items as $grnItem) {
                $poItem = $grnItem->purchaseOrderItem;
                if(!$poItem){
                     Log::warning("Skipping GRN Item ID {$grnItem->id} - Corresponding PO Item not found.");
                     continue; // Should not happen with constraints, but defensive check
                }

                // --- Update PO Item Received Quantity ---
                // Calculate total received for this PO Item *across all GRNs*
                $totalReceivedForPoItem = GoodsReceiptItem::where('purchase_order_item_id', $poItem->id)
                                         ->whereHas('goodsReceipt', function ($q) {
                                             $q->where('status', 'completed'); // Only count completed GRNs
                                         })
                                         ->sum('quantity_received');

                $poItem->quantity_received = $totalReceivedForPoItem;
                // Re-calculate remaining? Might be better as an accessor on the PO Item model
                // $poItem->remaining_quantity = $poItem->quantity - $poItem->quantity_received;
                $poItem->save();

                // --- Update Inventory Stock (only if item is inventory-tracked) ---
                if ($poItem->item_id && $grnItem->quantity_received > 0) {
                    $inventoryItem = $poItem->inventoryItem;
                    if($inventoryItem) {
                        // Create stock movement record
                        StockMovement::create([
                            'item_id' => $inventoryItem->id,
                            'movement_type' => 'purchase', // Or 'receipt'
                            'quantity' => $grnItem->quantity_received, // Positive for stock in
                            'movement_date' => $goodsReceipt->receipt_date,
                            'reference_type' => get_class($goodsReceipt), // Use get_class for polymorphism
                            'reference_id' => $goodsReceipt->id,
                            'notes' => "Received against PO #{$purchaseOrder->po_number}, GRN #{$goodsReceipt->receipt_number}",
                            'created_by' => Auth::id(),
                        ]);

                        // Update the inventory item's stock level and last received date
                        // Using increment is generally safer against race conditions than direct assignment
                        $inventoryItem->increment('current_stock', $grnItem->quantity_received);
                        $inventoryItem->last_received_date = $goodsReceipt->receipt_date;
                        $inventoryItem->save();
                    } else {
                         Log::warning("Inventory Item ID {$poItem->item_id} not found for PO Item ID {$poItem->id}. Stock not updated.");
                    }
                }
            } // End foreach loop for GRN items

            // --- Update Goods Receipt Status ---
            $goodsReceipt->status = 'completed'; // Mark this GRN as completed
            $goodsReceipt->completed_at = now(); // Set completion timestamp (if you add the column)
            $goodsReceipt->completed_by = Auth::id(); // Set completer (if you add the column)
            $goodsReceipt->save();

            // --- Update Purchase Order Status ---
            // Reload the PO with fresh item data to check if fully received
            $purchaseOrder->load('items');
            if ($purchaseOrder->isFullyReceived()) { // Assumes isFullyReceived() method exists on PO model
                $purchaseOrder->status = 'completed';
            } else {
                 // Check if ANY items have been received to set partial, otherwise keep as issued/approved
                 $anyReceived = $purchaseOrder->items()->where('quantity_received', '>', 0)->exists();
                 if ($anyReceived && $purchaseOrder->status !== 'completed') { // Avoid overwriting completed status
                     $purchaseOrder->status = 'partial';
                 }
                 // If status was 'approved', maybe move to 'issued' or 'partial' now? Depends on workflow.
                 // elseif ($purchaseOrder->status === 'approved'){
                 //     $purchaseOrder->status = 'partial';
                 // }
            }
            $purchaseOrder->save();

            DB::commit();

            return redirect()->route('accounting.goods-receipts.show', $goodsReceipt)
                ->with('success', 'Goods receipt completed successfully and inventory updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("GRN Complete failed for GRN ID {$goodsReceipt->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to complete goods receipt: ' . $e->getMessage());
        }
    }

     /**
     * Remove the specified resource from storage. (Likely only for draft/cancelled?)
     *
     * @param  \App\Models\Accounting\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(GoodsReceipt $goodsReceipt)
    {
        // Add checks: Can this GRN be deleted? Usually only drafts or maybe specific roles.
        if ($goodsReceipt->status === 'completed') {
            return redirect()->route('accounting.goods-receipts.index')
                 ->with('error', 'Cannot delete a completed Goods Receipt. Consider creating a return or adjustment.');
        }

         // Optional: Add authorization check
         // Gate::authorize('delete', $goodsReceipt);

         try {
             DB::beginTransaction();
             // Delete associated items first (cascade might handle this)
             $goodsReceipt->items()->delete();
             $goodsReceipt->delete();
             DB::commit();
             return redirect()->route('accounting.goods-receipts.index')
                 ->with('success', 'Goods Receipt deleted successfully.');
         } catch (\Exception $e) {
             DB::rollBack();
             Log::error("GRN Delete failed for GRN ID {$goodsReceipt->id}: " . $e->getMessage());
             return back()->with('error', 'Failed to delete Goods Receipt: ' . $e->getMessage());
         }
    }

    // Add other methods as needed (e.g., editItem, updateItem, destroyItem, cancel)

} // End of GoodsReceiptController class