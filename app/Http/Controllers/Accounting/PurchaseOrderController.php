<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\PurchaseOrder;
use App\Models\Accounting\PurchaseOrderItem;
use App\Models\Accounting\PurchaseRequest; // <-- Ensure this is imported
use App\Models\Accounting\PurchaseRequestItem;
use App\Models\Accounting\Supplier;
use App\Models\Accounting\InventoryItem; // <-- Ensure this is imported
// Correct namespace if needed (e.g., App\Models\Settings\AcademicYear)
use App\Models\Accounting\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <-- Added for auth()->id()
use Illuminate\Support\Facades\Log;   // <-- Added for logging errors
// use Barryvdh\DomPDF\Facade\Pdf; // <-- Uncomment if using laravel-dompdf
// use PDF; // <-- Alias for Facade (optional)
use Carbon\Carbon; // <-- Ensure Carbon is imported

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the purchase orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('date_issued', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date_issued', '<=', $request->to_date);
        }

        // Get results
        $purchaseOrders = $query->latest('created_at')->paginate(15)->withQueryString(); // Keep filters on pagination

        // Get suppliers for filter dropdown
        // Assuming 'suppliers' table might not have 'is_active'. Fetch all or adjust.
        $suppliers = Supplier::orderBy('name')->get();
        // $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(); // Use if 'is_active' exists

        return view('accounting.purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    /**
     * Show the form for creating a new purchase order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        // Get suppliers for dropdown
        $suppliers = Supplier::orderBy('name')->get(); // Fetch all, assuming no 'is_active' or filter later

        // Get current academic year based on date
        $today = Carbon::today();
        $academicYear = AcademicYear::where('start_date', '<=', $today)
                                      ->where('end_date', '>=', $today)
                                      ->first();
        if (!$academicYear) {
             $academicYear = AcademicYear::orderBy('start_date', 'desc')->first(); // Fallback
             if(!$academicYear) {
                // Handle case where no academic years exist at all
                 return redirect()->route('accounting.academic-years.index') // Adjust route if needed
                    ->with('error', 'Please set up Academic Years before creating Purchase Orders.');
             }
        }

        // Generate PO number
        $latestPO = PurchaseOrder::latest('id')->first();
        $nextId = $latestPO ? $latestPO->id + 1 : 1;
        $poNumber = 'PO-' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Fetch Approved, PO-less Purchase Requests for Dropdown
        $requisitions = PurchaseRequest::where('status', 'approved')
                                        ->whereDoesntHave('purchaseOrders') // Only PRs without existing POs
                                        ->orderBy('pr_number', 'desc')
                                        ->get(['id', 'pr_number', 'department']); // Select only needed columns

        // Get the specific PR ID if passed in the request
        $preSelectedRequisitionId = $request->input('pr_id');
        $purchaseRequest = null; // Variable to hold the specific PR details (if pre-selected)

        if ($preSelectedRequisitionId) {
            $purchaseRequest = PurchaseRequest::with('items.inventoryItem')->find($preSelectedRequisitionId);
            // Validate the pre-selected PR
            if (!$purchaseRequest || $purchaseRequest->status !== 'approved' || $purchaseRequest->purchaseOrders()->exists()) {
                $preSelectedRequisitionId = null;
                $purchaseRequest = null;
                session()->flash('warning', 'The selected Purchase Request cannot be converted to a PO or already has one.');
            }
        }

        // Fetch Inventory Items for item selection dropdown (Needed for direct item adding)
        $inventoryItems = InventoryItem::orderBy('name')->get(); // Fetch all or filter if needed
        // $inventoryItems = InventoryItem::where('is_active', true)->orderBy('name')->get(); // Use if 'is_active' exists

        return view('accounting.purchase-orders.create', compact(
            'suppliers',
            'academicYear',
            'poNumber',
            'purchaseRequest',          // Specific PR details if valid ID was passed
            'requisitions',             // List of valid PRs for the dropdown
            'preSelectedRequisitionId', // ID to pre-select the dropdown
            'inventoryItems'            // List of items for direct entry
        ));
    }

    /**
     * Store a newly created purchase order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Ensure the name attribute for the PR dropdown in the form is 'purchase_request_id'
        $validated = $request->validate([
            'po_number' => 'required|string|max:50|unique:purchase_orders',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id', // Matches dropdown name
            'date_issued' => 'required|date',
            'date_expected' => 'nullable|date|after_or_equal:date_issued',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'reference' => 'nullable|string|max:100',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            // Items array is required only if purchase_request_id is NOT provided
            // Note: The 'items' array might be submitted even if creating from PR, depending on form structure.
            // We prioritize copying from PR if purchase_request_id is set.
            'items' => 'nullable|array', // Allow items array even if PR is set, but ignore if copying
            'items.*.item_id' => 'nullable|exists:inventory_items,id',
            'items.*.description' => 'required_without:items.*.item_id|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required_without:items.*.item_id|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ],[
            // 'items.required_without' => 'Please add at least one item if not creating from a Purchase Request.', // This might conflict if form always sends items array
            'items.*.description.required_without' => 'Description is required if not selecting an inventory item.',
            'items.*.unit.required_without' => 'Unit is required if not selecting an inventory item.',
            'items.*.quantity.required' => 'Item quantity is required.',
            'items.*.unit_price.required' => 'Item unit price is required.',
        ]);

        // Set defaults
        $validated['status'] = 'draft';
        $validated['created_by'] = auth()->id();
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['shipping_cost'] = $validated['shipping_cost'] ?? 0;
        // Ensure foreign keys are null if empty
        $validated['purchase_request_id'] = $validated['purchase_request_id'] ?: null;
        $validated['academic_year_id'] = $validated['academic_year_id'] ?: null;


        try {
            DB::beginTransaction();

            $poData = collect($validated)->except('items')->toArray();
            $purchaseOrder = PurchaseOrder::create($poData);

            $hasItems = false; // Flag to check if any items were added

            // Add items: Prioritize copying from PR if ID is provided and valid
            if ($request->filled('purchase_request_id')) {
                $purchaseRequest = PurchaseRequest::with('items')->find($request->purchase_request_id);
                if($purchaseRequest){
                     // Ensure the selected PR is still valid before copying items
                     if ($purchaseRequest->status !== 'approved' || $purchaseRequest->purchaseOrders()->where('id', '!=', $purchaseOrder->id)->exists()) {
                         throw new \Exception('Selected Purchase Request is no longer valid for PO creation.');
                     }
                    if ($purchaseRequest->items->isEmpty()) {
                         throw new \Exception('Selected Purchase Request has no items to copy.');
                    }
                    foreach ($purchaseRequest->items as $prItem) {
                        $purchaseOrder->items()->create([
                            'item_id' => $prItem->item_id,
                            'purchase_request_item_id' => $prItem->id,
                            'description' => $prItem->description,
                            'quantity' => $prItem->quantity,
                            'unit' => $prItem->unit,
                            'unit_price' => $prItem->estimated_unit_cost ?? 0,
                            'quantity_received' => 0,
                            'notes' => $prItem->notes,
                        ]);
                        $hasItems = true;
                    }
                } else {
                     throw new \Exception('Selected Purchase Request not found.');
                }
            }
            // If not created from PR, OR if creating from PR failed, check for directly submitted items
            elseif ($request->has('items') && is_array($validated['items']) && count($validated['items']) > 0) {
                 // If items were submitted directly on the form
                foreach ($validated['items'] as $itemData) {
                    // Skip potentially empty item rows if form allows dynamic adding/removing
                    if (empty($itemData['quantity']) && empty($itemData['description']) && empty($itemData['item_id'])) {
                        continue;
                    }
                     // Re-validate individual items here if needed, especially if dynamically added
                    if (!isset($itemData['quantity']) || !isset($itemData['unit_price']) || (!isset($itemData['description']) && !isset($itemData['item_id'])) || (!isset($itemData['unit']) && !isset($itemData['item_id']))) {
                        throw new \Exception('Incomplete item data submitted.');
                    }

                    if (!empty($itemData['item_id'])) {
                        $invItem = InventoryItem::find($itemData['item_id']);
                        if ($invItem) {
                             $itemData['description'] = $itemData['description'] ?? $invItem->name;
                             $itemData['unit'] = $itemData['unit'] ?? $invItem->unit_of_measure;
                        } else {
                             throw new \Exception('Invalid Inventory Item ID provided for an item.');
                        }
                    }
                    // Ensure required fields are present if no item_id
                     elseif (empty($itemData['description']) || empty($itemData['unit'])) {
                         throw new \Exception('Description and Unit are required when not selecting an inventory item.');
                     }

                    $itemData['unit_price'] = $itemData['unit_price'] ?? 0;
                    $itemData['quantity_received'] = 0; // Default for new item
                    $purchaseOrder->items()->create($itemData);
                    $hasItems = true;
                }
            }

            // Final check if any items were actually added
            if (!$hasItems) {
                 throw new \Exception('No items were added to the purchase order.');
            }


            DB::commit();

             return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                 ->with('success', 'Purchase order created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation exceptions don't need rollback usually, just redirect back
             return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO Creation Failed for PO#:'.$validated['po_number'].'. Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create purchase order: '. $e->getMessage()); // Show specific error
        }
    }

    /**
     * Display the specified purchase order.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Contracts\View\View
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'purchaseRequest.requester',
            'items' => function ($query) {
                $query->with(['inventoryItem', 'purchaseRequestItem']);
             },
            'creator',
            'approver',
            'goodsReceipts' // Load related GRNs
        ]);

        return view('accounting.purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified purchase order. (Header only)
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be edited.');
        }
        $suppliers = Supplier::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $requisitions = PurchaseRequest::where('status', 'approved')
                                        ->where(function ($query) use ($purchaseOrder) {
                                            $query->whereDoesntHave('purchaseOrders')
                                                  ->orWhere('id', $purchaseOrder->purchase_request_id);
                                        })
                                        ->orderBy('pr_number', 'desc')
                                        ->get(['id', 'pr_number', 'department']);

        return view('accounting.purchase-orders.edit', compact(
            'purchaseOrder',
            'suppliers',
            'academicYears',
            'requisitions' // Pass list to edit view
        ));
    }

    /**
     * Update the specified purchase order in storage. (Header only)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be edited.');
        }

        $validated = $request->validate([
            'po_number' => 'required|string|max:50|unique:purchase_orders,po_number,' . $purchaseOrder->id,
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'date_issued' => 'required|date',
            'date_expected' => 'nullable|date|after_or_equal:date_issued',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'reference' => 'nullable|string|max:100',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['shipping_cost'] = $validated['shipping_cost'] ?? 0;
        $validated['purchase_request_id'] = $validated['purchase_request_id'] ?: null;
        $validated['academic_year_id'] = $validated['academic_year_id'] ?: null;

        try{
            $purchaseOrder->update($validated);
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order header updated successfully.');
        } catch (\Exception $e) {
             Log::error('PO Update Failed for PO ID:'.$purchaseOrder->id.'. Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update purchase order.');
        }
    }

    /**
     * Remove the specified purchase order from storage.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be deleted.');
        }

        try {
            DB::beginTransaction();
            $purchaseOrder->items()->delete(); // Delete items first
            $purchaseOrder->delete();
            DB::commit();
            return redirect()->route('accounting.purchase-orders.index')
                ->with('success', 'Purchase order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO Deletion Failed for PO ID:'.$purchaseOrder->id.'. Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete purchase order.');
        }
    }

    /**
     * Display the items form for a purchase order.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function items(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Items can only be managed for draft purchase orders.');
        }
        $purchaseOrder->load('items.inventoryItem');

        // Fetch Inventory Items for the dropdown
        $inventoryItems = InventoryItem::orderBy('name')->get(); // Fetch all or filter if needed
        // $inventoryItems = InventoryItem::where('is_active', true)->orderBy('name')->get(); // Use if active flag exists

        return view('accounting.purchase-orders.items', compact('purchaseOrder', 'inventoryItems'));
    }

    /**
     * Store a new item for a purchase order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeItem(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Items can only be added to draft purchase orders.');
        }

        $validated = $request->validate([
            'item_id' => 'nullable|exists:inventory_items,id',
            'description' => 'required_without:item_id|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required_without:item_id|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ],[
             'description.required_without' => 'Description is required if not selecting an inventory item.',
             'unit.required_without' => 'Unit is required if not selecting an inventory item.',
        ]);

        if ($request->filled('item_id')) {
            $item = InventoryItem::find($request->item_id);
            if($item){
                 $validated['description'] = $request->filled('description') ? $validated['description'] : $item->name;
                 $validated['unit'] = $request->filled('unit') ? $validated['unit'] : $item->unit_of_measure;
                 // Do NOT automatically overwrite price from inventory cost - PO price is negotiated
                 // $validated['unit_price'] = $request->filled('unit_price') ? $validated['unit_price'] : ($item->unit_cost ?? 0);
            } else {
                 return back()->withInput()->with('error', 'Selected inventory item not found.');
            }
        }
        $validated['quantity_received'] = 0;
        $validated['unit_price'] = $validated['unit_price'] ?? 0;

        $purchaseOrder->items()->create($validated);

        return redirect()->route('accounting.purchase-orders.items', $purchaseOrder->id)
            ->with('success', 'Item added successfully.');
    }

    /**
     * Remove an item from a purchase order.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @param  \App\Models\Accounting\PurchaseOrderItem $item // Use Route Model Binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyItem(PurchaseOrder $purchaseOrder, PurchaseOrderItem $item)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Items can only be removed from draft purchase orders.');
        }
        if ($item->purchase_order_id !== $purchaseOrder->id) {
            abort(404); // Item doesn't belong to this PO
        }
        if ($item->quantity_received > 0) {
            return redirect()->route('accounting.purchase-orders.items', $purchaseOrder->id)
                ->with('error', 'Cannot remove item that has associated goods receipts.');
        }

        $item->delete();

        return redirect()->route('accounting.purchase-orders.items', $purchaseOrder->id)
            ->with('success', 'Item removed successfully.');
    }

     /**
     * Submit a draft purchase order for approval.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Add authorization check: Gate::authorize('submit', $purchaseOrder);
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft orders can be submitted.');
        }
        if ($purchaseOrder->items()->count() === 0) {
             return redirect()->route('accounting.purchase-orders.items', $purchaseOrder)
                ->with('error', 'Cannot submit an empty order. Please add items.');
        }

        $purchaseOrder->status = 'pending';
        $purchaseOrder->save();

        // TODO: Notify approvers

        return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
             ->with('success', 'Purchase order submitted for approval.');
    }


    /**
     * Approve a purchase order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Add authorization check: Gate::authorize('approve', $purchaseOrder);

        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'This purchase order is not pending approval.');
        }

        $purchaseOrder->status = 'approved';
        $purchaseOrder->approved_by = auth()->id();
        $purchaseOrder->approved_at = now();
        $purchaseOrder->save();

        // TODO: Notify relevant parties

        return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order approved successfully.');
    }

    /**
     * Issue an approved purchase order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function issue(Request $request, PurchaseOrder $purchaseOrder)
    {
         // Add authorization check: Gate::authorize('issue', $purchaseOrder);

        if ($purchaseOrder->status !== 'approved') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only approved purchase orders can be issued.');
        }

        $purchaseOrder->status = 'issued';
        // $purchaseOrder->issued_at = now(); // Consider adding an issued_at timestamp
        $purchaseOrder->save();

        // TODO: Implement sending PO to supplier (e.g., Queue email job)

        return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order marked as issued.');
    }

    /**
     * Print a purchase order.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response // Should be Response|ResponseFactory for PDF stream
     */
    // public function print(PurchaseOrder $purchaseOrder) // Uncomment if PDF library is installed
    // {
    //     $purchaseOrder->load([
    //         'supplier',
    //         'items.inventoryItem',
    //         'creator',
    //         'approver',
    //         'academicYear'
    //     ]);

    //     // Ensure view exists: resources/views/accounting/purchase-orders/print.blade.php
    //     try {
    //         $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('accounting.purchase-orders.print', compact('purchaseOrder'));
    //         // $pdf->setPaper('a4', 'portrait');
    //         return $pdf->stream("PO-{$purchaseOrder->po_number}.pdf");
    //     } catch (\Exception $e) {
    //         Log::error("PDF Generation failed for PO ID {$purchaseOrder->id}: " . $e->getMessage());
    //          return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
    //             ->with('error', 'Could not generate PDF. Please check configuration and logs.');
    //     }
    // }

} // End of PurchaseOrderController class