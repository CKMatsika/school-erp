<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the suppliers.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(15);
        return view('accounting.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('accounting.suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'payment_terms' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch_code' => 'nullable|string|max:50',
            'bank_swift_code' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);
        
        // Set is_active default
        $validated['is_active'] = $request->has('is_active');
        
        $supplier = Supplier::create($validated);
        
        return redirect()->route('accounting.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified supplier.
     *
     * @param  \App\Models\Accounting\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
        // Load recent purchase orders
        $purchaseOrders = $supplier->purchaseOrders()
            ->orderBy('date_issued', 'desc')
            ->limit(10)
            ->get();
            
        // Load contracts
        $contracts = $supplier->contracts()
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('accounting.suppliers.show', compact('supplier', 'purchaseOrders', 'contracts'));
    }

    /**
     * Show the form for editing the specified supplier.
     *
     * @param  \App\Models\Accounting\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplier $supplier)
    {
        return view('accounting.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'payment_terms' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch_code' => 'nullable|string|max:50',
            'bank_swift_code' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);
        
        // Set is_active default
        $validated['is_active'] = $request->has('is_active');
        
        $supplier->update($validated);
        
        return redirect()->route('accounting.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified supplier from storage.
     *
     * @param  \App\Models\Accounting\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Supplier $supplier)
    {
        // Check if supplier has related records
        if ($supplier->purchaseOrders()->exists() || $supplier->contracts()->exists() || $supplier->tenderBids()->exists()) {
            return back()->with('error', 'Cannot delete supplier with related records.');
        }
        
        $supplier->delete();
        
        return redirect()->route('accounting.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Display the supplier performance.
     *
     * @param  \App\Models\Accounting\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function performance(Supplier $supplier)
    {
        // Get all purchase orders
        $purchaseOrders = $supplier->purchaseOrders()
            ->with('items')
            ->orderBy('date_issued', 'desc')
            ->get();
            
        // Calculate metrics
        $totalOrders = $purchaseOrders->count();
        $totalValue = $purchaseOrders->sum(function ($po) {
            return $po->items->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });
        });
        
        $onTimeDelivery = $purchaseOrders->where('status', 'completed')->count() > 0 ? 
            ($purchaseOrders->where('status', 'completed')->filter(function ($po) {
                $latestReceipt = $po->goodsReceipts()->latest('receipt_date')->first();
                return $latestReceipt && $latestReceipt->receipt_date <= $po->date_expected;
            })->count() / $purchaseOrders->where('status', 'completed')->count()) * 100 : 0;
        
        // Calculate price trends
        $priceData = [];
        $commonItems = [];
        
        foreach ($purchaseOrders as $po) {
            foreach ($po->items as $item) {
                if ($item->item_id) {
                    if (!isset($commonItems[$item->item_id])) {
                        $commonItems[$item->item_id] = [
                            'item' => $item->inventoryItem->name,
                            'prices' => []
                        ];
                    }
                    
                    $commonItems[$item->item_id]['prices'][$po->date_issued->format('Y-m-d')] = $item->unit_price;
                }
            }
        }
        
        foreach ($commonItems as $id => $data) {
            if (count($data['prices']) > 1) {
                $priceData[] = $data;
            }
        }
            
        return view('accounting.suppliers.performance', compact(
            'supplier',
            'purchaseOrders',
            'totalOrders',
            'totalValue',
            'onTimeDelivery',
            'priceData'
        ));
    }
}