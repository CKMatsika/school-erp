<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    /**
     * Display the timetable dashboard.
     */
    public function index()
    {
        // Get counts for the dashboard
        $counts = [
            'periods' => DB::table('timetable_periods')->where('school_id', auth()->user()->school_id)->count(),
            'classes' => DB::table('timetable_school_classes')->where('school_id', auth()->user()->school_id)->count(),
            'subjects' => DB::table('timetable_subjects')->where('school_id', auth()->user()->school_id)->count(),
            'teachers' => DB::table('timetable_teachers')->where('school_id', auth()->user()->school_id)->count(),
            'templates' => DB::table('timetable_templates')->where('school_id', auth()->user()->school_id)->count(),
        ];
        
        return view('timetable.index', compact('counts'));
    }
    
    /**
     * Display current timetable
     */
    public function currentTimetable()
    {
        // Get active timetable template
        $template = DB::table('timetable_templates')
            ->where('school_id', auth()->user()->school_id)
            ->where('is_active', true)
            ->first();
            
        // Get timetable entries if template exists
        $entries = [];
        if ($template) {
            $entries = DB::table('timetable_entries')
                ->where('timetable_template_id', $template->id)
                ->get();
        }
        
        return view('timetable.current', compact('template', 'entries'));
    }
}