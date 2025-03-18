<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $school = $user->school;
        
        // Get active modules for this school
        $activeModules = [];
        if ($school) {
            $activeModules = $school->modules()
                ->wherePivot('is_active', true)
                ->get();
        }
        
        // Super admin stats
        $totalSchools = School::count();
        $totalUsers = User::count();
        
        return view('dashboard', compact(
            'user',
            'school',
            'activeModules',
            'totalSchools',
            'totalUsers'
        ));
    }
}