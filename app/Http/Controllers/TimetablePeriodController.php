<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TimetablePeriodController extends Controller
{
    /**
     * Display a listing of periods.
     */
    public function index()
    {
        $periods = DB::table('timetable_periods')
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('order')
            ->get();
            
        return view('timetable.periods.index', compact('periods'));
    }
    
    /**
     * Show form to create a new period
     */
    public function create()
    {
        return view('timetable.periods.create');
    }
    
    /**
     * Store a new period
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'order' => 'required|integer|min:1',
            'is_break' => 'boolean',
            'description' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('timetables.periods.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::table('timetable_periods')->insert([
            'name' => $request->input('name'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'order' => $request->input('order'),
            'is_break' => $request->has('is_break'),
            'description' => $request->input('description'),
            'active' => true,
            'school_id' => auth()->user()->school_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return redirect()->route('timetables.periods')
            ->with('success', 'Period created successfully');
    }
    
    /**
     * Show form to edit period
     */
    public function edit($id)
    {
        $period = DB::table('timetable_periods')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$period) {
            return redirect()->route('timetables.periods')
                ->with('error', 'Period not found');
        }
        
        return view('timetable.periods.edit', compact('period'));
    }
    
    /**
     * Update a period
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'order' => 'required|integer|min:1',
            'is_break' => 'boolean',
            'description' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('timetables.periods.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if period exists and belongs to this school
        $period = DB::table('timetable_periods')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$period) {
            return redirect()->route('timetables.periods')
                ->with('error', 'Period not found');
        }
        
        DB::table('timetable_periods')
            ->where('id', $id)
            ->update([
                'name' => $request->input('name'),
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'order' => $request->input('order'),
                'is_break' => $request->has('is_break'),
                'description' => $request->input('description'),
                'updated_at' => now()
            ]);
        
        return redirect()->route('timetables.periods')
            ->with('success', 'Period updated successfully');
    }
    
    /**
     * Delete a period
     */
    public function destroy($id)
    {
        // Check if period exists and belongs to this school
        $period = DB::table('timetable_periods')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$period) {
            return redirect()->route('timetables.periods')
                ->with('error', 'Period not found');
        }
        
        // Check if period is used in any timetable entries
        $inUse = DB::table('timetable_entries')
            ->where('period_id', $id)
            ->exists();
            
        if ($inUse) {
            return redirect()->route('timetables.periods')
                ->with('error', 'Cannot delete period as it is being used in timetable entries');
        }
        
        DB::table('timetable_periods')->where('id', $id)->delete();
        
        return redirect()->route('timetables.periods')
            ->with('success', 'Period deleted successfully');
    }
}