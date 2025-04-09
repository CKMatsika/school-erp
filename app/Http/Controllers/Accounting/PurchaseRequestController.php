<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\PurchaseRequest;
use App\Models\Accounting\PurchaseRequestItem;
use App\Models\Accounting\InventoryItem;
// Correct the namespace if AcademicYear is not directly under Accounting
// use App\Models\Settings\AcademicYear; // Example if it's in Settings
use App\Models\Accounting\AcademicYear; // Assuming this is the correct namespace
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon; // <-- Import Carbon for date handling

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
        // ==================================================
        // == UPDATED ACADEMIC YEAR LOGIC ==
        // ==================================================
        $today = Carbon::today();
        // Find the academic year currently active based on date
        $academicYear = AcademicYear::where('start_date', '<=', $today)
                                      ->where('end_date', '>=', $today)
                                      ->first(); // Get the first match

        // Optional: Fallback if no active year is found based on date (e.g., get the latest one)
        if (!$academicYear) {
             $academicYear = AcademicYear::orderBy('start_date', 'desc')->first();
        }
        // ==================================================

        // Generate PR number
        $latestPR = PurchaseRequest::latest('id')->first(); // Order by ID for consistency
        $nextId = $latestPR ? $latestPR->id + 1 : 1;
        // Consider adding a school identifier or prefix if multi-tenant/multi-school
        $prNumber = 'PR-' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Pass the found (or fallback) academic year to the view
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
            'date_required' => 'nullable|date|after_or_equal:date_requested', // Added validation rule
            'department' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        // Add user ID
        $validated['requested_by'] = auth()->id();
        $validated['status'] = 'draft'; // Always start as draft

        $purchaseRequest = PurchaseRequest::create($validated);

        // Redirect to add items, not show page immediately after header creation
        return redirect()->route('accounting.purchase-requests.items', $purchaseRequest->id)
            ->with('success', 'Purchase request created. Please add items.');
    }

    /**
     * Display the specified purchase request.
     *
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseRequest $purchaseRequest)
    {
        // Load relationships efficiently
        $purchaseRequest->load([
            'requester',
            'items' => function ($query) { $query->with('inventoryItem'); }, // Eager load item details within items
            'approver',
            'rejecter',
            'academicYear', // Load academic year if needed
            'purchaseOrders' => function ($query) { $query->with('supplier'); } // Eager load supplier within POs
        ]);

        // Note: $purchaseOrders is already loaded via the relationship eager loading above
        // $purchaseOrders = $purchaseRequest->purchaseOrders; // No need for this extra query

        return view('accounting.purchase-requests.show', compact('purchaseRequest')); // Pass only $purchaseRequest
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

        // Fetch all academic years for dropdown selection during edit
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get(); // Order by date

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
            'date_required' => 'nullable|date|after_or_equal:date_requested', // Added validation rule
            'department' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $purchaseRequest->update($validated);

        // Redirect to show page after update
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
        // Check dependencies before deleting if necessary (e.g., linked POs)
        if ($purchaseRequest->purchaseOrders()->exists()) {
             return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Cannot delete. This purchase request is linked to one or more Purchase Orders.');
        }

        // Only allow deletion of draft or rejected PRs (adjust policy as needed)
        if (!in_array($purchaseRequest->status, ['draft', 'rejected'])) {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Only draft or rejected purchase requests can be deleted.');
        }

        try {
            // Deleting items might happen automatically due to cascadeOnDelete if set up correctly
            // $purchaseRequest->items()->delete(); // May not be needed if FK is set to cascade
            $purchaseRequest->delete();

            return redirect()->route('accounting.purchase-requests.index')
                ->with('success', 'Purchase request deleted successfully.');
        } catch (\Exception $e) {
             // Log error: Log::error("Error deleting PR {$purchaseRequest->id}: " . $e->getMessage());
             return redirect()->route('accounting.purchase-requests.index')
                ->with('error', 'Failed to delete purchase request. Please try again.');
        }
    }

    /**
     * Display the items form for a purchase request.
     *
     * @param  int  $id // Or use Route Model Binding: PurchaseRequest $purchaseRequest
     * @return \Illuminate\Http\Response
     */
    public function items(PurchaseRequest $purchaseRequest) // Using Route Model Binding
    {
        $purchaseRequest->load('items.inventoryItem'); // Eager load items and their inventory details

        // Only allow editing items of draft or rejected PRs (adjust as needed)
        if (!in_array($purchaseRequest->status, ['draft', 'rejected'])) {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Items can only be modified on draft or rejected requests.');
        }

        // Get inventory items for selection
        $inventoryItems = InventoryItem::where('is_active', true) // Make sure InventoryItem model has 'is_active' column
            ->orderBy('name')
            ->get();

        return view('accounting.purchase-requests.items', compact('purchaseRequest', 'inventoryItems'));
    }

    /**
     * Store a new item for a purchase request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest // Use Route Model Binding
     * @return \Illuminate\Http\Response
     */
    public function storeItem(Request $request, PurchaseRequest $purchaseRequest) // Using Route Model Binding
    {
        // Only allow adding items to draft or rejected PRs (adjust as needed)
         if (!in_array($purchaseRequest->status, ['draft', 'rejected'])) {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Items can only be added to draft or rejected requests.');
        }

        $validated = $request->validate([
            'item_id' => 'nullable|exists:inventory_items,id',
            'description' => 'required_without:item_id|string|max:255', // Require description if no item selected
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required_without:item_id|string|max:50', // Require unit if no item selected
            'estimated_unit_cost' => 'nullable|numeric|min:0', // Make nullable, maybe default later
            'budget_line' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // If an inventory item is selected, populate details
        if ($request->filled('item_id')) {
            $item = InventoryItem::find($request->item_id);
            if($item) { // Ensure item exists
                // Use item's name/unit if description/unit not provided in request
                $validated['description'] = $request->filled('description') ? $validated['description'] : $item->name;
                $validated['unit'] = $request->filled('unit') ? $validated['unit'] : $item->unit_of_measure;
                // Use item's cost if estimate not provided
                $validated['estimated_unit_cost'] = $request->filled('estimated_unit_cost') ? $validated['estimated_unit_cost'] : ($item->unit_cost ?? 0);
            } else {
                 // Handle case where item_id is provided but item not found
                 return back()->withInput()->with('error', 'Selected inventory item not found.');
            }
        } elseif (!$request->filled('description') || !$request->filled('unit')) {
             // If no item_id, description and unit are required
             return back()->withInput()->withErrors([
                 'description' => 'Description is required when not selecting an inventory item.',
                 'unit' => 'Unit is required when not selecting an inventory item.'
             ]);
        }

        // Ensure cost is set (default to 0 if null)
        $validated['estimated_unit_cost'] = $validated['estimated_unit_cost'] ?? 0;


        // Create the item using the relationship
        $purchaseRequest->items()->create($validated);

        // Don't automatically change status here, requires explicit submission action
        // if ($purchaseRequest->items()->count() == 1 && $request->has('submit_pr')) { ... }

        return redirect()->route('accounting.purchase-requests.items', $purchaseRequest->id) // Use ID from model
            ->with('success', 'Item added successfully.');
    }

    /**
     * Remove an item from a purchase request.
     *
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @param  \App\Models\Accounting\PurchaseRequestItem $item // Use Route Model Binding for item
     * @return \Illuminate\Http\Response
     */
    public function destroyItem(PurchaseRequest $purchaseRequest, PurchaseRequestItem $item) // Using Route Model Binding
    {
        // Only allow removing items from draft or rejected PRs (adjust as needed)
        if (!in_array($purchaseRequest->status, ['draft', 'rejected'])) {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest->id)
                ->with('error', 'Items can only be removed from draft or rejected requests.');
        }

        // Optional: Verify item belongs to the request (Route Model Binding often handles this implicitly if nested correctly)
        if ($item->purchase_request_id !== $purchaseRequest->id) {
             abort(404); // Or redirect with error
        }

        $item->delete();

        return redirect()->route('accounting.purchase-requests.items', $purchaseRequest->id)
            ->with('success', 'Item removed successfully.');
    }

     /**
     * Submit a draft purchase request for approval.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @return \Illuminate\Http\Response
     */
    public function submit(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'draft') {
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'Only draft requests can be submitted.');
        }
        if ($purchaseRequest->items()->count() === 0) {
             return redirect()->route('accounting.purchase-requests.items', $purchaseRequest)
                ->with('error', 'Cannot submit an empty request. Please add items.');
        }

        $purchaseRequest->status = 'pending';
        $purchaseRequest->save();

        // TODO: Potentially notify approvers

        return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
             ->with('success', 'Purchase request submitted for approval.');
    }


    /**
     * Approve a purchase request.
     *
     * @param  \Illuminate\Http\Request  $request // Request might be needed for comments etc.
     * @param  \App\Models\Accounting\PurchaseRequest  $purchaseRequest
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Add authorization check: Can the current user approve?
        // Gate::authorize('approve', $purchaseRequest);

        // Check if PR is in a state that can be approved (e.g., 'pending')
        if ($purchaseRequest->status !== 'pending') { // Adjust allowable statuses if needed
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'This purchase request is not pending approval.');
        }

        $purchaseRequest->status = 'approved';
        $purchaseRequest->approved_by = auth()->id();
        $purchaseRequest->approved_at = now();
        $purchaseRequest->rejected_by = null; // Clear rejection fields if any
        $purchaseRequest->rejected_at = null;
        $purchaseRequest->rejection_reason = null;
        $purchaseRequest->save();

         // TODO: Potentially notify requester

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
         // Add authorization check: Can the current user reject?
         // Gate::authorize('reject', $purchaseRequest);

        // Check if PR is in a state that can be rejected (e.g., 'pending')
         if ($purchaseRequest->status !== 'pending') { // Adjust allowable statuses if needed
            return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
                ->with('error', 'This purchase request is not pending approval/rejection.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500', // Increased max length
        ]);

        $purchaseRequest->status = 'rejected';
        $purchaseRequest->rejected_by = auth()->id();
        $purchaseRequest->rejected_at = now();
        $purchaseRequest->rejection_reason = $validated['rejection_reason'];
        $purchaseRequest->approved_by = null; // Clear approval fields if any
        $purchaseRequest->approved_at = null;
        $purchaseRequest->save();

         // TODO: Potentially notify requester

        return redirect()->route('accounting.purchase-requests.show', $purchaseRequest)
            ->with('success', 'Purchase request rejected.');
    }
}