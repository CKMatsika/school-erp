<?php

namespace App\Http\Controllers\Admin\Transport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Display a listing of all vehicles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all vehicles data
        
        return view('admin.transport.vehicles.index');
    }

    /**
     * Show the form for creating a new vehicle.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.transport.vehicles.create');
    }

    /**
     * Store a newly created vehicle in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate vehicle data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'registration_number' => 'required|string|max:50|unique:vehicles,registration_number',
            'model' => 'required|string|max:100',
            'make' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . date('Y'),
            'seating_capacity' => 'required|integer|min:1',
            'fuel_type' => 'required|string|max:50',
            'insurance_expiry' => 'required|date|after:today',
            'registration_expiry' => 'required|date|after:today',
            'purchase_date' => 'required|date',
            'purchase_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,maintenance,out-of-service',
            'gps_enabled' => 'boolean',
            'camera_enabled' => 'boolean',
            'ac_enabled' => 'boolean',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new vehicle
        // Save data to database
        // Upload photos if provided
        
        return redirect()->route('admin.transport.vehicles.index')
            ->with('success', 'Vehicle created successfully');
    }

    /**
     * Display the specified vehicle.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Retrieve the vehicle
        
        return view('admin.transport.vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified vehicle.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Retrieve the vehicle
        
        return view('admin.transport.vehicles.edit', compact('vehicle'));
    }

    /**
     * Update the specified vehicle in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate vehicle data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'registration_number' => 'required|string|max:50|unique:vehicles,registration_number,'.$id,
            'model' => 'required|string|max:100',
            'make' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . date('Y'),
            'seating_capacity' => 'required|integer|min:1',
            'fuel_type' => 'required|string|max:50',
            'insurance_expiry' => 'required|date',
            'registration_expiry' => 'required|date',
            'purchase_date' => 'required|date',
            'purchase_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,maintenance,out-of-service',
            'gps_enabled' => 'boolean',
            'camera_enabled' => 'boolean',
            'ac_enabled' => 'boolean',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update vehicle
        // Save data to database
        // Upload photos if provided
        
        return redirect()->route('admin.transport.vehicles.index')
            ->with('success', 'Vehicle updated successfully');
    }

    /**
     * Remove the specified vehicle from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Delete vehicle
        
        return redirect()->route('admin.transport.vehicles.index')
            ->with('success', 'Vehicle deleted successfully');
    }

    /**
     * Display vehicle documents.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function documents($id)
    {
        // Retrieve the vehicle and documents
        
        return view('admin.transport.vehicles.documents', compact('vehicle'));
    }

    /**
     * Upload vehicle document.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadDocument(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'expiry_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Upload document
        // Save data to database
        
        return redirect()->route('admin.transport.vehicles.documents', $id)
            ->with('success', 'Document uploaded successfully');
    }

    /**
     * Display vehicle maintenance records.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function maintenance($id)
    {
        // Retrieve the vehicle and maintenance records
        
        return view('admin.transport.vehicles.maintenance', compact('vehicle'));
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
            'description' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'done_by' => 'required|string|max:255',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
            'odometer_reading' => 'required|numeric|min:0',
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
        
        return redirect()->route('admin.transport.vehicles.maintenance', $id)
            ->with('success', 'Maintenance record added successfully');
    }

    /**
     * Display vehicle fuel records.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function fuel($id)
    {
        // Retrieve the vehicle and fuel records
        
        return view('admin.transport.vehicles.fuel', compact('vehicle'));
    }

    /**
     * Add fuel record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addFuel(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'fuel_quantity' => 'required|numeric|min:0',
            'fuel_cost' => 'required|numeric|min:0',
            'odometer_reading' => 'required|numeric|min:0',
            'filled_by' => 'required|string|max:255',
            'fuel_station' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Add fuel record
        // Save data to database
        
        return redirect()->route('admin.transport.vehicles.fuel', $id)
            ->with('success', 'Fuel record added successfully');
    }

    /**
     * Display vehicle location and tracking info.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function tracking($id)
    {
        // Retrieve the vehicle and tracking info
        
        return view('admin.transport.vehicles.tracking', compact('vehicle'));
    }
}