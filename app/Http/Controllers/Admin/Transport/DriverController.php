<?php

namespace App\Http\Controllers\Admin\Transport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    /**
     * Display a listing of all drivers.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all drivers data
        
        return view('admin.transport.drivers.index');
    }

    /**
     * Show the form for creating a new driver.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.transport.drivers.create');
    }

    /**
     * Store a newly created driver in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate driver data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:50|unique:drivers,license_number',
            'license_expiry' => 'required|date|after:today',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'date_of_birth' => 'required|date|before:18 years ago',
            'joining_date' => 'required|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new driver
        // Save data to database
        // Upload photo if provided
        
        return redirect()->route('admin.transport.drivers.index')
            ->with('success', 'Driver created successfully');
    }

    /**
     * Display the specified driver.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Retrieve the driver
        
        return view('admin.transport.drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified driver.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Retrieve the driver
        
        return view('admin.transport.drivers.edit', compact('driver'));
    }

    /**
     * Update the specified driver in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate driver data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:50|unique:drivers,license_number,'.$id,
            'license_expiry' => 'required|date',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'date_of_birth' => 'required|date|before:18 years ago',
            'joining_date' => 'required|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update driver
        // Save data to database
        // Upload photo if provided
        
        return redirect()->route('admin.transport.drivers.index')
            ->with('success', 'Driver updated successfully');
    }

    /**
     * Remove the specified driver from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Delete driver
        
        return redirect()->route('admin.transport.drivers.index')
            ->with('success', 'Driver deleted successfully');
    }

    /**
     * Display driver attendance.
     *
     * @return \Illuminate\Http\Response
     */
    public function attendance()
    {
        // Get driver attendance data
        
        return view('admin.transport.drivers.attendance');
    }

    /**
     * Mark driver attendance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function markAttendance(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:drivers,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,leave',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Mark driver attendance
        // Save data to database
        
        return redirect()->route('admin.transport.drivers.attendance')
            ->with('success', 'Attendance marked successfully');
    }

    /**
     * Display driver documents.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function documents($id)
    {
        // Retrieve the driver and documents
        
        return view('admin.transport.drivers.documents', compact('driver'));
    }

    /**
     * Upload driver document.
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
        
        return redirect()->route('admin.transport.drivers.documents', $id)
            ->with('success', 'Document uploaded successfully');
    }
}