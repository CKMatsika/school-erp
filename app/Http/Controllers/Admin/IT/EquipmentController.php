<?php

namespace App\Http\Controllers\Admin\IT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EquipmentController extends Controller
{
    /**
     * Display a listing of all equipment.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all equipment data
        
        return view('admin.it.equipment.index');
    }

    /**
     * Show the form for creating a new equipment.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Load necessary data for the form
        
        return view('admin.it.equipment.create');
    }

    /**
     * Store a newly created equipment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate equipment data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'manufacturer' => 'required|string|max:100',
            'serial_number' => 'required|string|max:100|unique:equipment,serial_number',
            'status' => 'required|in:available,assigned,maintenance,repair,retired',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'supplier' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new equipment
        // Save data to database
        // Upload photo if provided
        
        return redirect()->route('admin.it.equipment.index')
            ->with('success', 'Equipment created successfully');
    }

    /**
     * Display the specified equipment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Retrieve the equipment
        
        return view('admin.it.equipment.show', compact('equipment'));
    }

    /**
     * Show the form for editing the specified equipment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Retrieve the equipment
        
        return view('admin.it.equipment.edit', compact('equipment'));
    }

    /**
     * Update the specified equipment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate equipment data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'manufacturer' => 'required|string|max:100',
            'serial_number' => 'required|string|max:100|unique:equipment,serial_number,'.$id,
            'status' => 'required|in:available,assigned,maintenance,repair,retired',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'supplier' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update equipment
        // Save data to database
        // Upload photo if provided
        
        return redirect()->route('admin.it.equipment.index')
            ->with('success', 'Equipment updated successfully');
    }

    /**
     * Remove the specified equipment from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Delete equipment
        
        return redirect()->route('admin.it.equipment.index')
            ->with('success', 'Equipment deleted successfully');
    }

    /**
     * Display equipment assignments.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignments()
    {
        // Get equipment assignments data
        
        return view('admin.it.equipment.assignments.index');
    }

    /**
     * Show the form for creating a new equipment assignment.
     *
     * @return \Illuminate\Http\Response
     */
    public function createAssignment()
    {
        // Load necessary data for the form (e.g., equipment list, staff list)
        
        return view('admin.it.equipment.assignments.create');
    }

    /**
     * Store a newly created equipment assignment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAssignment(Request $request)
    {
        // Validate assignment data
        $validator = Validator::make($request->all(), [
            'equipment_id' => 'required|exists:equipment,id',
            'assigned_to' => 'required|exists:staff,id',
            'assigned_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after:assigned_date',
            'notes' => 'nullable|string',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new assignment
        // Save data to database
        // Update equipment status
        
        return redirect()->route('admin.it.equipment.assignments.index')
            ->with('success', 'Equipment assigned successfully');
    }

    /**
     * Return equipment from assignment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function returnEquipment(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'return_date' => 'required|date',
            'return_condition' => 'required|in:good,damaged,needs-repair',
            'return_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Process equipment return
        // Save data to database
        // Update equipment status
        
        return redirect()->route('admin.it.equipment.assignments.index')
            ->with('success', 'Equipment returned successfully');
    }

    /**
     * Display equipment maintenance records.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function maintenance($id)
    {
        // Retrieve the equipment and maintenance records
        
        return view('admin.it.equipment.maintenance', compact('equipment'));
    }

    /**
     * Add maintenance record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addMaintenance(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'maintenance_type' => 'required|string|max:255',
            'maintenance_date' => 'required|date',
            'performed_by' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'description' => 'required|string',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Add maintenance record
        // Save data to database
        // Upload attachments if provided
        // Update equipment status if needed
        
        return redirect()->route('admin.it.equipment.maintenance', $id)
            ->with('success', 'Maintenance record added successfully');
    }

    /**
     * Display equipment categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        // Get equipment categories data
        
        return view('admin.it.equipment.categories.index');
    }

    /**
     * Show the form for creating a new equipment category.
     *
     * @return \Illuminate\Http\Response
     */
    public function createCategory()
    {
        return view('admin.it.equipment.categories.create');
    }

    /**
     * Store a newly created equipment category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCategory(Request $request)
    {
        // Validate category data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:equipment_categories,name',
            'description' => 'nullable|string',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new category
        // Save data to database
        
        return redirect()->route('admin.it.equipment.categories.index')
            ->with('success', 'Equipment category created successfully');
    }

    /**
     * Generate equipment reports.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request)
    {
        // Generate reports based on request parameters
        
        return view('admin.it.equipment.reports.index');
    }
}