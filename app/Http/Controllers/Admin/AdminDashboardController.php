<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Asset;
use App\Models\Book;
use App\Models\Staff;
use App\Models\Vehicle;

class AdminDashboardController extends Controller
{
    /**
     * Display the administration dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get real counts if models exist, otherwise use placeholders
        $staffCount = class_exists('App\Models\Staff') ? Staff::count() : 0;
        $teacherAttendance = 0; // Replace with actual calculation if available
        $staffAttendance = 0;   // Replace with actual calculation if available
        $pendingLeaves = 0;     // Replace with actual calculation if available
        $activeAssets = class_exists('App\Models\Asset') ? Asset::where('status', 'Available')->count() : 0;
        $maintenanceRequests = class_exists('App\Models\MaintenanceRequest') ? \App\Models\MaintenanceRequest::count() : 0;
        $libraryBooks = class_exists('App\Models\Book') ? Book::count() : 0;
        $vehicles = class_exists('App\Models\Vehicle') ? Vehicle::count() : 0;
        
        // Add variables to match exactly what the view is expecting
        // This should cover all potential variables used in the dashboard
        $totalAssets = class_exists('App\Models\Asset') ? Asset::count() : 0;
        $totalStaff = $staffCount; // The view is using totalStaff instead of staffCount
        $totalBooks = $libraryBooks; // Mapping libraryBooks to totalBooks 
        $totalVehicles = $vehicles; // The view might be using this variable name
        
        // Make sure we have variables for any additional items that might be in the view
        $totalRooms = class_exists('App\Models\Room') ? \App\Models\Room::count() : 0;
        $totalITEquipment = class_exists('App\Models\ITEquipment') ? \App\Models\ITEquipment::count() : 0;
        $totalMembers = class_exists('App\Models\LibraryMember') ? \App\Models\LibraryMember::count() : 0;
        $totalCirculations = class_exists('App\Models\BookCirculation') ? \App\Models\BookCirculation::count() : 0;
        
        // Empty collections for activities and tasks
        $recentActivities = collect([]);
        $pendingTasks = collect([]);
        
        // Bundle all data in an array for easier access in the view as $data['key']
        $data = [
            'totalAssets' => $totalAssets,
            'totalStaff' => $totalStaff,
            'totalBooks' => $totalBooks,
            'totalVehicles' => $totalVehicles,
            'totalRooms' => $totalRooms,
            'totalITEquipment' => $totalITEquipment,
            'totalMembers' => $totalMembers,
            'totalCirculations' => $totalCirculations
        ];
        
        // Return view with every possible variable that might be referenced
        return view('admin.dashboard', compact(
            'user',
            'data',
            // Original variables
            'staffCount',
            'teacherAttendance',
            'staffAttendance',
            'pendingLeaves',
            'activeAssets',
            'maintenanceRequests',
            'libraryBooks',
            'vehicles',
            // Additional variables that the view might be expecting
            'totalAssets',
            'totalStaff',
            'totalBooks',
            'totalVehicles',
            'totalRooms',
            'totalITEquipment',
            'totalMembers',
            'totalCirculations',
            'recentActivities',
            'pendingTasks'
        ));
    }
}