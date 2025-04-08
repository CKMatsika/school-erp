<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\PurchaseRequest;
use App\Models\Accounting\PurchaseRequestItem;
use App\Models\Accounting\InventoryItem;
use App\Models\Accounting\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseRequestController extends Controller
{
    /**
     * Display a listing of the purchase requests.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = PurchaseRequest::with('requester');
        
        // Apply filters if any
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('date_requested', '>=', $request->from_date);
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('date_requested', '<=', $request->to_date);
        }
        
        if ($request->has('department') && $request->department) {
            $query->where('department', $request->department);
        }
        
        // Get results
        $purchaseRequests = $query->orderBy('date_requested', 'desc')
            ->paginate(15);
            
        // Get departments for filter
        $departments = PurchaseRequest::distinct()
            ->whereNotNull('department')
            ->pluck('department');
            
        return view('accounting.purchase-requests.index', compact('purchaseRequests', 'departments'));
    }

    /**
     * Show the form for creating a new purchase request.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get active academic year
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        // Generate PR number
        $latestPR = PurchaseRequest::latest()->first();
        $nextId = $latestPR ? $latestPR->id + 1 : 1;
        $prNumber = 'PR-' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        
        return view('accounting.purchase-requests.create', compact('academicYear', 'prNumber'));
    }

    /**
     * Store a newly created purchase request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_number' => 'required|string|max:50|unique:purchase_requests',
            'date_requested' => 'required|date',
            'date_required' => 'nullable|date',
            'department' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);
        
        // Add user ID
        $validated['requested_by'] = auth()->id();
        $validated['status'] = 'draft';
        
        $purchaseRequest = PurchaseRequest::create($validated);
        
        return redirect()->route('accounting.purchase-requests.items', $purchaseRequest->id)
            ->with('success', 'Purchase request created. Please add items to your request.');
    }

    /**
     * Display the specified purchase request.
     *
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseRequest $purchaseRequest)
    {
        // Load relationships
        $purchaseRequest->load('requester', 'items.inventoryItem', 'approver', 'rejecter');
        
        // Check for purchase orders
        $purchaseOrders = $purchaseRequest->purchaseOrders()->with('supplier')->get();
        
        return view('accounting.purchase-requests.show', compact('purchaseRequest', 'purchaseOrders'));
    }

    /**
     * Show the form for editing the specified purchase request.
     *
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseRequest $purchaseRequest)
    {
        // Only allow editing of draft PRs
        if ($purchaseRequest->status !== 'draft') {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Only draft purchase requests can be edited.');
        }
        
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        
        return view('accounting.purchase-requests.edit', compact('purchaseRequest', 'academicYears'));
    }

    /**
     * Update the specified purchase request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Only allow editing of draft PRs
        if ($purchaseRequest->status !== 'draft') {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Only draft purchase requests can be edited.');
        }
        
        $validated = $request->validate([
            'pr_number' => 'required|string|max:50|unique:purchase_requests,pr_number,' . $purchaseRequest->id,
            'date_requested' => 'required|date',
            'date_required' => 'nullable|date',
            'department' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);
        
        $purchaseRequest->update($validated);
        
        return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
            ->with('success', 'Purchase request updated successfully.');
    }

    /**
     * Remove the specified purchase request from storage.
     *
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseRequest $purchaseRequest)
    {
        // Only allow deletion of draft PRs
        if ($purchaseRequest->status !== 'draft') {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Only draft purchase requests can be deleted.');
        }
        
        // Delete related items
        $purchaseRequest->items()->delete();
        
        // Delete the PR
        $purchaseRequest->delete();
        
        return redirect()->route('accounting.purchase-requests.index')
            ->with('success', 'Purchase request deleted successfully.');
    }
    
    /**
     * Display the items form for a purchase request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function items($id)
    {
        $purchaseRequest = PurchaseRequest::with('items.inventoryItem')->findOrFail($id);
        
        // Only allow editing items of draft PRs
        if ($purchaseRequest->status !== 'draft') {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Only draft purchase requests can have items added or removed.');
        }
        
        // Get inventory items for selection
        $inventoryItems = InventoryItem::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('accounting.purchase-requests.items', compact('purchaseRequest', 'inventoryItems'));
    }
    
    /**
     * Store a new item for a purchase request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeItem(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        
        // Only allow adding items to draft PRs
        if ($purchaseRequest->status !== 'draft') {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Only draft purchase requests can have items added.');
        }
        
        $validated = $request->validate([
            'item_id' => 'nullable|exists:inventory_items,id',
            'description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'estimated_unit_cost' => 'required|numeric|min:0',
            'budget_line' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        
        // If an inventory item is selected, populate details
        if ($request->item_id) {
            $item = InventoryItem::find($request->item_id);
            $validated['description'] = $item->name;
            $validated['unit'] = $item->unit_of_measure;
            
            // Use the item's cost if no estimate provided
            if (!$request->filled('estimated_unit_cost')) {
                $validated['estimated_unit_cost'] = $item->unit_cost;
            }
        }
        
        // Create the item
        $purchaseRequestItem = new PurchaseRequestItem($validated);
        $purchaseRequest->items()->save($purchaseRequestItem);
        
        // Set PR status to pending if it's the first item and was created via quick-create
        if ($purchaseRequest->items()->count() == 1 && $request->has('submit_pr')) {
            $purchaseRequest->status = 'pending';
            $purchaseRequest->save();
            
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('success', 'Purchase request submitted successfully.');
        }
        
        return redirect()->route('accounting.purchase-requests.items', $purchaseRequest)
            ->with('success', 'Item added successfully.');
    }
    
    /**
     * Remove an item from a purchase request.
     *
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @param  int  $item
     * @return \Illuminate\Http\Response
     */
    public function destroyItem(PurchaseRequest $purchaseRequest, $item)
    {
        // Only allow removing items from draft PRs
        if ($purchaseRequest->status !== 'draft') {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Only draft purchase requests can have items removed.');
        }
        
        $purchaseRequestItem = PurchaseRequestItem::findOrFail($item);
        
        // Check if item belongs to this PR
        if ($purchaseRequestItem->purchase_request_id != $purchaseRequest->id) {
            return redirect()->route('accounting.purchase-requests.items', $purchaseRequest)
                ->with('error', 'Item does not belong to this purchase request.');
        }
        
        $purchaseRequestItem->delete();
        
        return redirect()->route('accounting.purchase-requests.items', $purchaseRequest)
            ->with('success', 'Item removed successfully.');
    }
    
    /**
     * Approve a purchase request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Check if PR can be approved
        if (!$purchaseRequest->canBeApproved()) {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'This purchase request cannot be approved.');
        }
        
        $purchaseRequest->status = 'approved';
        $purchaseRequest->approved_by = auth()->id();
        $purchaseRequest->approved_at = now();
        $purchaseRequest->save();
        
        return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
            ->with('success', 'Purchase request approved successfully.');
    }
    
    /**
     * Reject a purchase request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Check if PR can be rejected
        if (!$purchaseRequest->canBeRejected()) {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'This purchase request cannot be rejected.');
        }
        
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);
        
        $purchaseRequest->status = 'rejected';
        $purchaseRequest->rejected_by = auth()->id();
        $purchaseRequest->rejected_at = now();
        $purchaseRequest->rejection_reason = $validated['rejection_reason'];
        $purchaseRequest->save();
        
        return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
            ->with('success', 'Purchase request rejected.');
    }
}