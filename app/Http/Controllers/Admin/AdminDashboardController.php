<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        // Placeholder data
        $staffCount = 0;
        $teacherAttendance = 0;
        $staffAttendance = 0;
        $pendingLeaves = 0;
        $activeAssets = 0;
        $maintenanceRequests = 0;
        $libraryBooks = 0;
        $vehicles = 0;
        
        return view('admin.dashboard', compact(
            'user',
            'staffCount',
            'teacherAttendance',
            'staffAttendance',
            'pendingLeaves',
            'activeAssets',
            'maintenanceRequests',
            'libraryBooks',
            'vehicles'
        ));
    }
}