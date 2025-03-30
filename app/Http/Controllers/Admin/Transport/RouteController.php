<?php

namespace App\Http\Controllers\Admin\Transport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    /**
     * Display a listing of all routes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all routes data
        
        return view('admin.transport.routes.index');
    }

    /**
     * Show the form for creating a new route.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Load necessary data for the form (e.g., vehicles, drivers)
        
        return view('admin.transport.routes.create');
    }

    /**
     * Store a newly created route in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate route data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'distance' => 'required|numeric|min:0',
            'estimated_time' => 'required|string',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'description' => 'nullable|string',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new route
        // Save data to database
        
        return redirect()->route('admin.transport.routes.index')
            ->with('success', 'Route created successfully');
    }

    /**
     * Display the specified route.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Retrieve the route
        
        return view('admin.transport.routes.show', compact('route'));
    }

    /**
     * Show the form for editing the specified route.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Retrieve the route
        // Load necessary data for the form
        
        return view('admin.transport.routes.edit', compact('route'));
    }

    /**
     * Update the specified route in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate route data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'distance' => 'required|numeric|min:0',
            'estimated_time' => 'required|string',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'description' => 'nullable|string',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update route
        // Save data to database
        
        return redirect()->route('admin.transport.routes.index')
            ->with('success', 'Route updated successfully');
    }

    /**
     * Remove the specified route from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Delete route
        
        return redirect()->route('admin.transport.routes.index')
            ->with('success', 'Route deleted successfully');
    }

    /**
     * Display route stops.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function stops($id)
    {
        // Retrieve the route and stops
        
        return view('admin.transport.routes.stops', compact('route'));
    }

    /**
     * Add stop to route.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addStop(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'stop_name' => 'required|string|max:255',
            'stop_location' => 'required|string|max:255',
            'arrival_time' => 'required|string',
            'departure_time' => 'required|string',
            'sequence' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Add stop to route
        // Save data to database
        
        return redirect()->route('admin.transport.routes.stops', $id)
            ->with('success', 'Stop added successfully');
    }

    /**
     * Display route assignments.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignments()
    {
        // Get route assignments data
        
        return view('admin.transport.routes.assignments');
    }

    /**
     * Assign students to routes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assignStudents(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'pickup_point' => 'required|string|max:255',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Assign students to route
        // Save data to database
        
        return redirect()->route('admin.transport.routes.assignments')
            ->with('success', 'Students assigned to route successfully');
    }

    /**
     * Display route map.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function map($id)
    {
        // Retrieve the route for map display
        
        return view('admin.transport.routes.map', compact('route'));
    }
}