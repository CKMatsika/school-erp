<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\GoodsReceipt;
use App\Models\Accounting\GoodsReceiptItem;
use App\Models\Accounting\PurchaseOrder;
use App\Models\Accounting\PurchaseOrderItem;
use App\Models\Accounting\InventoryItem;
use App\Models\Accounting\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    /**
     * Display a listing of the goods receipts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['purchaseOrder.supplier', 'creator']);
        
        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('po_number') && $request->po_number) {
            $poIds = PurchaseOrder::where('po_number', 'like', '%' . $request->po_number . '%')
                ->pluck('id');
            $query->whereIn('purchase_order_id', $poIds);
        }
        
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('receipt_date', '>=', $request->from_date);
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('receipt_date', '<=', $request->to_date);
        }
        
        // Get results
        $goodsReceipts = $query->orderBy('receipt_date', 'desc')
            ->paginate(15);
            
        return view('accounting.goods-receipts.index', compact('goodsReceipts'));
    }

    /**
     * Show the form for creating a new goods receipt.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // Get purchase orders that haven't been fully received
        $purchaseOrdersQuery = PurchaseOrder::whereIn('status', ['approved', 'issued', 'partial'])
            ->with('supplier');
            
        // If a specific PO is requested
        if ($request->has('po_id')) {
            $purchaseOrdersQuery->where('id', $request->po_id);
        }
        
        $purchaseOrders = $purchaseOrdersQuery->get()
            ->filter(function ($po) {
                return !$po->isFullyReceived();
            });
            
        // Generate receipt number
        $latestReceipt = GoodsReceipt::latest()->first();
        $nextId = $latestReceipt ? $latestReceipt->id + 1 : 1;
        $receiptNumber = 'GRN-' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        
        return view('accounting.goods-receipts.create', compact('purchaseOrders', 'receiptNumber'));
    }

    /**
     * Store a newly created goods receipt in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receipt_number' => 'required|string|max:50|unique:goods_receipts',
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'receipt_date' => 'required|date',
            'delivery_note' => 'nullable|string|max:100',
            'received_by' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);
        
        // Set defaults
        $validated['status'] = 'draft';
        $
        // Set defaults
        $validated['status'] = 'draft';
        $validated['created_by'] = auth()->id();
        
        // Create the goods receipt
        $goodsReceipt = GoodsReceipt::create($validated);
        
        // Redirect to the page to add items
        return redirect()->route('accounting.goods-receipts.items', $goodsReceipt)
            ->with('success', 'Goods receipt created. Please add the received items.');
    }

    /**
     * Display the specified goods receipt.
     *
     * @param  \App\Models\Accounting\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Http\Response
     */
    public function show(GoodsReceipt $goodsReceipt)
    {
        // Load relationships
        $goodsReceipt->load([
            'purchaseOrder.supplier',
            'purchaseOrder.items.inventoryItem',
            'items.purchaseOrderItem.inventoryItem',
            'creator'
        ]);
        
        return view('accounting.goods-receipts.show', compact('goodsReceipt'));
    }

    /**
     * Show the form for adding items to a goods receipt.
     *
     * @param  \App\Models\Accounting\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Http\Response
     */
    public function items(GoodsReceipt $goodsReceipt)
    {
        // Check if goods receipt is still in draft status
        if ($goodsReceipt->status !== 'draft') {
            return redirect()->route('accounting.goods-receipts.show', $goodsReceipt)
                ->with('error', 'This goods receipt has already been completed.');
        }
        
        // Load the purchase order items
        $purchaseOrder = $goodsReceipt->purchaseOrder;
        $purchaseOrder->load('items.inventoryItem');
        
        // Get the PO items that still have quantity to receive
        $poItems = $purchaseOrder->items->filter(function ($item) {
            return $item->remaining_quantity > 0;
        });
        
        // Get already received items for this GRN
        $receivedItems = $goodsReceipt->items()
            ->with('purchaseOrderItem.inventoryItem')
            ->get();
        
        return view('accounting.goods-receipts.items', compact(
            'goodsReceipt', 
            'purchaseOrder', 
            'poItems',
            'receivedItems'
        ));
    }

    /**
     * Store a received item for a goods receipt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Http\Response
     */
    public function storeItem(Request $request, GoodsReceipt $goodsReceipt)
    {
        // Check if goods receipt is still in draft status
        if ($goodsReceipt->status !== 'draft') {
            return redirect()->route('accounting.goods-receipts.show', $goodsReceipt)
                ->with('error', 'This goods receipt has already been completed.');
        }
        
        $validated = $request->validate([
            'purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'quantity_received' => 'required|numeric|min:0.01',
            'quantity_rejected' => 'nullable|numeric|min:0',
            'rejection_reason' => 'nullable|required_if:quantity_rejected,>,0|string|max:255',
        ]);
        
        // Make sure quantity is not more than remaining
        $poItem = PurchaseOrderItem::find($validated['purchase_order_item_id']);
        $totalQuantity = ($validated['quantity_received'] ?? 0) + ($validated['quantity_rejected'] ?? 0);
        
        if ($totalQuantity > $poItem->remaining_quantity) {
            return back()->withInput()->with('error', 'Total quantity (received + rejected) cannot exceed the remaining quantity to be received.');
        }
        
        // Check if this PO item has already been added to this receipt
        $existingItem = $goodsReceipt->items()
            ->where('purchase_order_item_id', $validated['purchase_order_item_id'])
            ->first();
            
        if ($existingItem) {
            return back()->withInput()->with('error', 'This item has already been added to this receipt. Please edit the existing entry.');
        }
        
        // Set defaults
        $validated['quantity_rejected'] = $validated['quantity_rejected'] ?? 0;
        
        // Create the item
        $goodsReceipt->items()->create($validated);
        
        // Set status to "in progress"
        if ($goodsReceipt->items()->count() === 1) {
            $goodsReceipt->status = 'in_progress';
            $goodsReceipt->save();
        }
        
        return redirect()->route('accounting.goods-receipts.items', $goodsReceipt)
            ->with('success', 'Item added successfully.');
    }

    /**
     * Complete a goods receipt and update inventory.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Http\Response
     */
    public function complete(Request $request, GoodsReceipt $goodsReceipt)
    {
        // Check if goods receipt can be completed
        if (!$goodsReceipt->canBeCompleted()) {
            return redirect()->route('accounting.goods-receipts.show', $goodsReceipt)
                ->with('error', 'This goods receipt cannot be completed.');
        }
        
        try {
            DB::beginTransaction();
            
            // Load relationships
            $goodsReceipt->load('items.purchaseOrderItem.inventoryItem');
            
            // Update inventory and PO items
            foreach ($goodsReceipt->items as $item) {
                $poItem = $item->purchaseOrderItem;
                
                // Update PO item received quantity
                $poItem->quantity_received += $item->quantity_received;
                $poItem->save();
                
                // Update inventory if there's an inventory item
                if ($poItem->item_id) {
                    $inventoryItem = $poItem->inventoryItem;
                    
                    // Only update if we actually received something
                    if ($item->quantity_received > 0) {
                        // Create stock movement
                        StockMovement::create([
                            'item_id' => $inventoryItem->id,
                            'movement_type' => 'purchase',
                            'quantity' => $item->quantity_received,
                            'movement_date' => $goodsReceipt->receipt_date,
                            'reference_type' => GoodsReceipt::class,
                            'reference_id' => $goodsReceipt->id,
                            'notes' => "Received from PO {$goodsReceipt->purchaseOrder->po_number}",
                            'created_by' => auth()->id(),
                        ]);
                        
                        // Update inventory quantity
                        $inventoryItem->current_stock += $item->quantity_received;
                        $inventoryItem->last_received_date = $goodsReceipt->receipt_date;
                        $inventoryItem->save();
                    }
                }
            }
            
            // Update goods receipt status
            $goodsReceipt->status = 'completed';
            $goodsReceipt->save();
            
            // Update purchase order status
            $purchaseOrder = $goodsReceipt->purchaseOrder;
            
            if ($purchaseOrder->isFullyReceived()) {
                $purchaseOrder->status = 'completed';
            } else {
                $purchaseOrder->status = 'partial';
            }
            
            $purchaseOrder->save();
            
            DB::commit();
            
            return redirect()->route('accounting.goods-receipts.show', $goodsReceipt)
                ->with('success', 'Goods receipt completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete goods receipt: ' . $e->getMessage());
        }
    }
}