<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\PurchaseOrder;
use App\Models\Accounting\PurchaseOrderItem;
use App\Models\Accounting\PurchaseRequest;
use App\Models\Accounting\PurchaseRequestItem;
use App\Models\Accounting\Supplier;
use App\Models\Accounting\InventoryItem;
use App\Models\Accounting\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use PDF;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the purchase orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator']);
        
        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('date_issued', '>=', $request->from_date);
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('date_issued', '<=', $request->to_date);
        }
        
        // Get results
        $purchaseOrders = $query->orderBy('created_at', 'desc')
            ->paginate(15);
            
        // Get suppliers for filter
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('accounting.purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    /**
     * Show the form for creating a new purchase order.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // Get all active suppliers
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        // Get active academic year
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        // Generate PO number
        $latestPO = PurchaseOrder::latest()->first();
        $nextId = $latestPO ? $latestPO->id + 1 : 1;
        $poNumber = 'PO-' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        
        // Check if creating from a purchase request
        $purchaseRequest = null;
        if ($request->has('pr_id')) {
            $purchaseRequest = PurchaseRequest::with('items.inventoryItem')
                ->findOrFail($request->pr_id);
                
            // Verify the PR is approved and doesn't already have a PO
            if (!$purchaseRequest->canBeConvertedToPO()) {
                return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                    ->with('error', 'This purchase request cannot be converted to a purchase order.');
            }
        }
        
        return view('accounting.purchase-orders.create', compact(
            'suppliers', 
            'academicYear', 
            'poNumber',
            'purchaseRequest'
        ));
    }

    /**
     * Store a newly created purchase order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string|max:50|unique:purchase_orders',
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
        
        // Set defaults
        $validated['status'] = 'draft';
        $validated['created_by'] = auth()->id();
        
        // Handle missing numeric values
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['shipping_cost'] = $validated['shipping_cost'] ?? 0;
        
        try {
            DB::beginTransaction();
            
            // Create the purchase order
            $purchaseOrder = PurchaseOrder::create($validated);
            
            // If we're creating from a PR, copy the items
            if ($request->purchase_request_id) {
                $purchaseRequest = PurchaseRequest::with('items.inventoryItem')->find($request->purchase_request_id);
                
                foreach ($purchaseRequest->items as $prItem) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'item_id' => $prItem->item_id,
                        'purchase_request_item_id' => $prItem->id,
                        'description' => $prItem->description,
                        'quantity' => $prItem->quantity,
                        'unit' => $prItem->unit,
                        'unit_price' => $prItem->estimated_unit_cost,
                        'quantity_received' => 0,
                        'notes' => $prItem->notes,
                    ]);
                }
            }
            
            DB::commit();
            
            if ($request->purchase_request_id) {
                // If from PR, go straight to review
                return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                    ->with('success', 'Purchase order created from purchase request.');
            } else {
                // Otherwise go to add items
                return redirect()->route('accounting.purchase-orders.items', $purchaseOrder)
                    ->with('success', 'Purchase order created. Please add items to your order.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase order.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        // Load relationships
        $purchaseOrder->load([
            'supplier', 
            'purchaseRequest.requester', 
            'items.inventoryItem',
            'creator',
            'approver',
            'goodsReceipts'
        ]);
        
        return view('accounting.purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified purchase order.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        // Only allow editing of draft POs
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be edited.');
        }
        
        // Get all active suppliers
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        // Get academic years
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        
        return view('accounting.purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'academicYears'));
    }

    /**
     * Update the specified purchase order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Only allow editing of draft POs
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be edited.');
        }
        
        $validated = $request->validate([
            'po_number' => 'required|string|max:50|unique:purchase_orders,po_number,' . $purchaseOrder->id,
            'supplier_id' => 'required|exists:suppliers,id',
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
        
        // Handle missing numeric values
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['shipping_cost'] = $validated['shipping_cost'] ?? 0;
        
        $purchaseOrder->update($validated);
        
        return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated successfully.');
    }

    /**
     * Remove the specified purchase order from storage.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Only allow deletion of draft POs
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can be deleted.');
        }
        
        // Check if goods have been received
        if ($purchaseOrder->goodsReceipts()->exists()) {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Cannot delete purchase order with goods receipts.');
        }
        
        try {
            DB::beginTransaction();
            
            // Delete items
            $purchaseOrder->items()->delete();
            
            // Delete the PO
            $purchaseOrder->delete();
            
            DB::commit();
            
            return redirect()->route('accounting.purchase-orders.index')
                ->with('success', 'Purchase order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete purchase order: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the items form for a purchase order.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function items(PurchaseOrder $purchaseOrder)
    {
        // Only allow editing items of draft POs
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can have items added or removed.');
        }
        
        $purchaseOrder->load('items.inventoryItem');
        
        // Get inventory items for selection
        $inventoryItems = InventoryItem::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('accounting.purchase-orders.items', compact('purchaseOrder', 'inventoryItems'));
    }
    
    /**
     * Store a new item for a purchase order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function storeItem(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Only allow adding items to draft POs
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can have items added.');
        }
        
        $validated = $request->validate([
            'item_id' => 'nullable|exists:inventory_items,id',
            'description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        
        // If an inventory item is selected, populate details
        if ($request->item_id) {
            $item = InventoryItem::find($request->item_id);
            $validated['description'] = $item->name;
            $validated['unit'] = $item->unit_of_measure;
            
            // Use the item's cost if no unit price provided
            if (!$request->filled('unit_price')) {
                $validated['unit_price'] = $item->unit_cost;
            }
        }
        
        // Set default values
        $validated['quantity_received'] = 0;
        
        // Create the item
        $purchaseOrder->items()->create($validated);
        
        // Set status to pending if submitting the PO
        if ($request->has('submit_po')) {
            $purchaseOrder->status = 'pending';
            $purchaseOrder->save();
            
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order submitted successfully.');
        }
        
        return redirect()->route('accounting.purchase-orders.items', $purchaseOrder)
            ->with('success', 'Item added successfully.');
    }
    
    /**
     * Remove an item from a purchase order.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @param  int  $item
     * @return \Illuminate\Http\Response
     */
    public function destroyItem(PurchaseOrder $purchaseOrder, $item)
    {
        // Only allow removing items from draft POs
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders can have items removed.');
        }
        
        $purchaseOrderItem = PurchaseOrderItem::findOrFail($item);
        
        // Check if item belongs to this PO
        if ($purchaseOrderItem->purchase_order_id != $purchaseOrder->id) {
            return redirect()->route('accounting.purchase-orders.items', $purchaseOrder)
                ->with('error', 'Item does not belong to this purchase order.');
        }
        
        // Check if any has been received
        if ($purchaseOrderItem->quantity_received > 0) {
            return redirect()->route('accounting.purchase-orders.items', $purchaseOrder)
                ->with('error', 'Cannot remove item that has been partially received.');
        }
        
        $purchaseOrderItem->delete();
        
        return redirect()->route('accounting.purchase-orders.items', $purchaseOrder)
            ->with('success', 'Item removed successfully.');
    }
    
    /**
     * Approve a purchase order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Check if PO can be approved
        if (!$purchaseOrder->canBeApproved()) {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'This purchase order cannot be approved.');
        }
        
        $purchaseOrder->status = 'approved';
        $purchaseOrder->approved_by = auth()->id();
        $purchaseOrder->approved_at = now();
        $purchaseOrder->save();
        
        return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order approved successfully.');
    }
    
    /**
     * Issue a purchase order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function issue(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Check if PO can be issued
        if (!$purchaseOrder->canBeIssued()) {
            return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
                ->with('error', 'This purchase order cannot be issued.');
        }
        
        $purchaseOrder->status = 'issued';
        $purchaseOrder->save();
        
        return redirect()->route('accounting.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order issued successfully.');
    }
    
    /**
     * Print a purchase order.
     *
     * @param  \App\Models\Accounting\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function print(PurchaseOrder $purchaseOrder)
    {
        // Load relationships
        $purchaseOrder->load([
            'supplier', 
            'items.inventoryItem',
            'creator',
            'approver'
        ]);
        
        // Generate PDF
        $pdf = PDF::loadView('accounting.purchase-orders.print', compact('purchaseOrder'));
        
        return $pdf->stream("PO-{$purchaseOrder->po_number}.pdf");
    }
}