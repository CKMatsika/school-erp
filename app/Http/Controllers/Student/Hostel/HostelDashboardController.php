<?php
// File: app/Http/Controllers/Student/Hostel/HostelDashboardController.php

namespace App\Http\Controllers\Student\Hostel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student\HostelHouse;
use App\Models\Student\HostelRoom;
use App\Models\Student\HostelBed;
use App\Models\Student\HostelAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HostelDashboardController extends Controller
{
    /**
     * Display the hostel management dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // Get total bed capacity
            $totalBeds = HostelBed::count();
            
            // Get occupied and available beds
            $occupiedBeds = HostelBed::where('status', 'occupied')->count();
            $availableBeds = $totalBeds - $occupiedBeds;
            
            // Calculate occupancy rate
            $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;
            
            // Get pending allocations
            $pendingAllocations = HostelAllocation::where('status', 'pending')->count();
            
            // Get houses with occupancy data
            $houses = HostelHouse::all();
            
            // Calculate occupancy rate for each house
            foreach ($houses as $house) {
                $total_beds = HostelBed::whereHas('room', function($query) use ($house) {
                    $query->where('house_id', $house->id);
                })->count();
                
                $occupied_beds = HostelBed::whereHas('room', function($query) use ($house) {
                    $query->where('house_id', $house->id);
                })->where('status', 'occupied')->count();
                
                $house->total_beds = $total_beds;
                $house->occupied_beds = $occupied_beds;
                $house->occupancy_rate = $total_beds > 0 
                    ? round(($occupied_beds / $total_beds) * 100, 1) 
                    : 0;
            }
            
            // Check if student_profiles table exists and has a name column
            if (Schema::hasTable('students') && Schema::hasColumn('students', 'name')) {
                // Use the students table instead of student_profiles
                $recentAllocations = DB::table('hostel_allocations')
                    ->join('hostel_beds', 'hostel_allocations.bed_id', '=', 'hostel_beds.id')
                    ->join('hostel_rooms', 'hostel_beds.room_id', '=', 'hostel_rooms.id')
                    ->join('hostel_houses', 'hostel_rooms.house_id', '=', 'hostel_houses.id')
                    ->join('students', 'hostel_allocations.student_id', '=', 'students.id')
                    ->select(
                        'hostel_allocations.*',
                        'students.name as student_name',
                        'hostel_beds.bed_number',
                        'hostel_rooms.room_number',
                        'hostel_houses.name as house_name'
                    )
                    ->orderBy('hostel_allocations.created_at', 'desc')
                    ->limit(5)
                    ->get();
            } else {
                // Just get the allocations without joins if student table isn't available
                $recentAllocations = DB::table('hostel_allocations')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            }
            
            // Empty collection for maintenance issues
            $maintenanceIssues = collect([]);
            
            return view('student.hostel.dashboard', compact(
                'totalBeds',
                'occupiedBeds',
                'availableBeds',
                'occupancyRate',
                'pendingAllocations',
                'houses',
                'recentAllocations',
                'maintenanceIssues'
            ));
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Hostel Dashboard Error: ' . $e->getMessage());
            
            // Return a basic view with error message
            return view('student.hostel.dashboard', [
                'error' => 'There was an error loading the dashboard: ' . $e->getMessage()
            ]);
        }
    }
}