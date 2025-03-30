<?php

namespace App\Http\Controllers\Admin\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Display a listing of all inventory items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all inventory items data
        
        return view('admin.asset.inventory.index');
    }

    /**
     * Show the form for creating a new inventory item.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Load necessary data for the form (e.g., categories, suppliers)
        
        return view('admin.asset.inventory.create');
    }

    /**
     * Store a newly created inventory item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate inventory item data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'item_code' => 'required|string|max:50|unique:inventory_items,item_code',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'current_value' => 'nullable|numeric|min:0',
            'location_id' => 'required|exists:asset_locations,id',
            'condition' => 'required|in:new,good,fair,poor,damaged',
            'status' => 'required|in:in-use,available,under-maintenance,retired,disposed',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'quantity' => 'required|integer|min:1',
            'min_quantity' => 'nullable|integer|min:0',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'depreciation_method' => 'nullable|in:straight-line,reducing-balance,none',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new inventory item
        // Generate asset tag if needed
        // Upload photo if provided
        // Save data to database
        
        return redirect()->route('admin.asset.inventory.index')
            ->with('success', 'Inventory item created successfully');
    }

    /**
     * Display the specified inventory item.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Retrieve the inventory item
        
        return view('admin.asset.inventory.show', compact('item'));
    }

    /**
     * Show the form for editing the specified inventory item.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Retrieve the inventory item
        // Load necessary data for the form
        
        return view('admin.asset.inventory.edit', compact('item'));
    }

    /**
     * Update the specified inventory item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate inventory item data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'item_code' => 'required|string|max:50|unique:inventory_items,item_code,'.$id,
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'current_value' => 'nullable|numeric|min:0',
            'location_id' => 'required|exists:asset_locations,id',
            'condition' => 'required|in:new,good,fair,poor,damaged',
            'status' => 'required|in:in-use,available,under-maintenance,retired,disposed',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'quantity' => 'required|integer|min:1',
            'min_quantity' => 'nullable|integer|min:0',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'depreciation_method' => 'nullable|in:straight-line,reducing-balance,none',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update inventory item
        // Upload photo if provided
        // Save data to database
        
        return redirect()->route('admin.asset.inventory.index')
            ->with('success', 'Inventory item updated successfully');
    }

    /**
     * Remove the specified inventory item from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Check if inventory item is in use
        // Delete inventory item
        
        return redirect()->route('admin.asset.inventory.index')
            ->with('success', 'Inventory item deleted successfully');
    }

    /**
     * Display a listing of inventory categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        // Get all inventory categories data
        
        return view('admin.asset.inventory.categories.index');
    }

    /**
     * Show the form for creating a new inventory category.
     *
     * @return \Illuminate\Http\Response
     */
    public function createCategory()
    {
        return view('admin.asset.inventory.categories.create');
    }

    /**
     * Store a newly created inventory category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCategory(Request $request)
    {
        // Validate category data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:asset_categories,name',
            'description' => 'nullable|string',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'depreciation_method' => 'nullable|in:straight-line,reducing-balance,none',
            'useful_life_years' => 'nullable|integer|min:0',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new category
        // Save data to database
        
        return redirect()->route('admin.asset.inventory.categories.index')
            ->with('success', 'Inventory category created successfully');
    }

    /**
     * Display a listing of inventory suppliers.
     *
     * @return \Illuminate\Http\Response
     */
    public function suppliers()
    {
        // Get all suppliers data
        
        return view('admin.asset.inventory.suppliers.index');
    }

    /**
     * Show the form for creating a new supplier.
     *
     * @return \Illuminate\Http\Response
     */
    public function createSupplier()
    {
        return view('admin.asset.inventory.suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeSupplier(Request $request)
    {
        // Validate supplier data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:suppliers,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new supplier
        // Save data to database
        
        return redirect()->route('admin.asset.inventory.suppliers.index')
            ->with('success', 'Supplier created successfully');
    }

    /**
     * Display a listing of asset locations.
     *
     * @return \Illuminate\Http\Response
     */
    public function locations()
    {
        // Get all asset locations data
        
        return view('admin.asset.inventory.locations.index');
    }

    /**
     * Show the form for creating a new asset location.
     *
     * @return \Illuminate\Http\Response
     */
    public function createLocation()
    {
        return view('admin.asset.inventory.locations.create');
    }

    /**
     * Store a newly created asset location in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeLocation(Request $request)
    {
        // Validate location data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:asset_locations,name',
            'description' => 'nullable|string',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'room' => 'nullable|string|max:50',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new location
        // Save data to database
        
        return redirect()->route('admin.asset.inventory.locations.index')
            ->with('success', 'Asset location created successfully');
    }

    /**
     * Display inventory assignments.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignments()
    {
        // Get inventory assignments data
        
        return view('admin.asset.inventory.assignments.index');
    }

    /**
     * Show the form for creating a new inventory assignment.
     *
     * @return \Illuminate\Http\Response
     */
    public function createAssignment()
    {
        // Load necessary data for the form (e.g., inventory items, staff)
        
        return view('admin.asset.inventory.assignments.create');
    }

    /**
     * Store a newly created inventory assignment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAssignment(Request $request)
    {
        // Validate assignment data
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:inventory_items,id',
            'assigned_to_type' => 'required|in:staff,department,student',
            'assigned_to_id' => 'required',
            'assigned_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after_or_equal:assigned_date',
            'notes' => 'nullable|string',
            'issued_by' => 'required|exists:staff,id',
            'condition_at_assignment' => 'required|in:new,good,fair,poor',
            'quantity' => 'required|integer|min:1',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new assignment
        // Update inventory item status and quantity
        // Save data to database
        
        return redirect()->route('admin.asset.inventory.assignments.index')
            ->with('success', 'Inventory item assigned successfully');
    }

    /**
     * Return inventory item from assignment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function returnItem(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'return_date' => 'required|date',
            'return_condition' => 'required|in:good,damaged,lost',
            'notes' => 'nullable|string',
            'received_by' => 'required|exists:staff,id',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Process item return
        // Update inventory item status and quantity
        // Save data to database
        
        return redirect()->route('admin.asset.inventory.assignments.index')
            ->with('success', 'Inventory item returned successfully');
    }

    /**
     * Display inventory maintenance records.
     *
     * @return \Illuminate\Http\Response
     */
    public function maintenance()
    {
        // Get maintenance records data
        
        return view('admin.asset.inventory.maintenance.index');
    }

    /**
     * Show the form for creating a new maintenance record.
     *
     * @return \Illuminate\Http\Response
     */
    public function createMaintenance()
    {
        // Load necessary data for the form (e.g., inventory items)
        
        return view('admin.asset.inventory.maintenance.create');
    }

    /**
     * Store a newly created maintenance record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeMaintenance(Request $request)
    {
        // Validate maintenance data
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:inventory_items,id',
            'maintenance_type' => 'required|in:preventive,corrective,predictive',
            'maintenance_date' => 'required|date',
            'performed_by' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'notes' => 'required|string',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new maintenance record
        // Update inventory item status if needed
        // Upload attachments if provided
        // Save data to database
        
        return redirect()->route('admin.asset.inventory.maintenance.index')
            ->with('success', 'Maintenance record created successfully');
    }

    /**
     * Display inventory depreciation.
     *
     * @return \Illuminate\Http\Response
     */
    public function depreciation()
    {
        // Get depreciation data
        
        return view('admin.asset.inventory.depreciation');
    }

    /**
     * Calculate depreciation for inventory items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function calculateDepreciation(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'calculation_date' => 'required|date',
            'category_id' => 'nullable|exists:asset_categories,id',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Calculate depreciation
        // Update current values
        // Save data to database
        
        return redirect()->route('admin.asset.inventory.depreciation')
            ->with('success', 'Depreciation calculated successfully');
    }

    /**
     * Display inventory reports.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request)
    {
        // Generate reports based on request parameters
        
        return view('admin.asset.inventory.reports');
    }

    /**
     * Export inventory data to CSV/Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:csv,excel',
            'category_id' => 'nullable|exists:asset_categories,id',
            'location_id' => 'nullable|exists:asset_locations,id',
            'status' => 'nullable|in:in-use,available,under-maintenance,retired,disposed',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate export file
        
        // Return download response
    }

    /**
     * Import inventory data from CSV/Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:csv,xls,xlsx|max:10240',
            'has_header' => 'boolean',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Process import file
        // Create inventory items from file data
        // Save data to database
        
        return redirect()->route('admin.asset.inventory.index')
            ->with('success', 'Inventory data imported successfully');
    }

    /**
     * Generate barcode/QR code labels for inventory items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generateLabels(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:inventory_items,id',
            'label_type' => 'required|in:barcode,qrcode',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate labels for selected inventory items
        
        return view('admin.asset.inventory.labels', compact('labels'));
    }

    /**
     * Perform stocktake/inventory audit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function stocktake(Request $request)
    {
        if ($request->isMethod('post')) {
            // Validate request
            $validator = Validator::make($request->all(), [
                'stocktake_date' => 'required|date',
                'location_id' => 'nullable|exists:asset_locations,id',
                'category_id' => 'nullable|exists:asset_categories,id',
                'counts' => 'required|array',
                'counts.*.item_id' => 'required|exists:inventory_items,id',
                'counts.*.counted_quantity' => 'required|integer|min:0',
                'counts.*.notes' => 'nullable|string',
                // Add other validation rules as needed
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Process stocktake data
            // Update inventory quantities if needed
            // Log discrepancies
            // Save data to database
            
            return redirect()->route('admin.asset.inventory.index')
                ->with('success', 'Stocktake completed successfully');
        }

        // Get stocktake data for form
        
        return view('admin.asset.inventory.stocktake');
    }
}