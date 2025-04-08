<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\PurchaseRequest;
use App\Models\Accounting\PurchaseOrder;
use App\Models\Accounting\InventoryItem;
use App\Models\Accounting\GoodsReceipt;
use App\Models\Accounting\Supplier;
use App\Models\Accounting\Tender;
use App\Models\Accounting\ProcurementContract;

class ProcurementController extends Controller
{
    /**
     * Display the procurement dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get pending purchase requests
        $pendingPRs = PurchaseRequest::where('status', 'pending')
            ->orderBy('date_requested', 'desc')
            ->limit(5)
            ->get();
        
        // Get recent purchase orders
        $recentPOs = PurchaseOrder::where('status', '!=', 'draft')
            ->orderBy('date_issued', 'desc')
            ->limit(5)
            ->get();
        
        // Get recent goods receipts
        $recentReceipts = GoodsReceipt::orderBy('receipt_date', 'desc')
            ->limit(5)
            ->get();
        
        // Get low stock items
        $lowStockItems = InventoryItem::whereRaw('current_stock <= reorder_level')
            ->where('is_active', true)
            ->limit(5)
            ->get();
        
        // Get active tenders
        $activeTenders = Tender::where('status', 'published')
            ->whereDate('closing_date', '>=', now())
            ->orderBy('closing_date')
            ->limit(5)
            ->get();
        
        // Get expiring contracts (within 30 days)
        $expiringContracts = ProcurementContract::where('status', 'active')
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->whereDate('end_date', '>=', now())
            ->orderBy('end_date')
            ->limit(5)
            ->get();
        
        // Get summary statistics
        $prCount = PurchaseRequest::count();
        $poCount = PurchaseOrder::count();
        $supplierCount = Supplier::count();
        $inventoryItemCount = InventoryItem::count();
        
        // Get aggregated values
        $totalPurchases = PurchaseOrder::where('status', '!=', 'draft')
            ->where('status', '!=', 'cancelled')
            ->whereYear('date_issued', now()->year)
            ->sum(\DB::raw('(SELECT SUM(quantity * unit_price) FROM purchase_order_items WHERE purchase_order_items.purchase_order_id = purchase_orders.id)'));
        
        $inventoryValue = InventoryItem::sum(\DB::raw('current_stock * unit_cost'));
        
        return view('accounting.procurement.index', compact(
            'pendingPRs',
            'recentPOs',
            'recentReceipts',
            'lowStockItems',
            'activeTenders',
            'expiringContracts',
            'prCount',
            'poCount',
            'supplierCount',
            'inventoryItemCount',
            'totalPurchases',
            'inventoryValue'
        ));
    }
}