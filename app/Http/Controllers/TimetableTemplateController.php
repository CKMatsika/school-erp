<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TimetableTemplateController extends Controller
{
    /**
     * Display a listing of timetable templates.
     */
    public function index()
    {
        $templates = DB::table('timetable_templates')
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('timetable.templates.index', compact('templates'));
    }
    
    /**
     * Show form to create a new timetable template
     */
    public function create()
    {
        return view('timetable.templates.create');
    }

    /**
     * Store a new timetable template
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:50',
            'term' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:1,7'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('timetables.templates.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        // Convert days of week array to JSON
        $daysOfWeek = json_encode($request->input('days_of_week'));
        
        DB::table('timetable_templates')->insert([
            'name' => $request->input('name'),
            'academic_year' => $request->input('academic_year'),
            'term' => $request->input('term'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'description' => $request->input('description'),
            'days_of_week' => $daysOfWeek,
            'is_active' => false,
            'school_id' => auth()->user()->school_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return redirect()->route('timetables.templates')
            ->with('success', 'Timetable template created successfully');
    }
    
    /**
     * Show form to edit timetable template
     */
    public function edit($id)
    {
        $template = DB::table('timetable_templates')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$template) {
            return redirect()->route('timetables.templates')
                ->with('error', 'Template not found');
        }
        
        // Convert days of week from JSON to array
        $template->days_of_week = json_decode($template->days_of_week);
        
        return view('timetable.templates.edit', compact('template'));
    }

    /**
     * Update a timetable template
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:50',
            'term' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:1,7'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('timetables.templates.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if template exists and belongs to this school
        $template = DB::table('timetable_templates')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$template) {
            return redirect()->route('timetables.templates')
                ->with('error', 'Template not found');
        }
        
        // Convert days of week array to JSON
        $daysOfWeek = json_encode($request->input('days_of_week'));
        
        DB::table('timetable_templates')
            ->where('id', $id)
            ->update([
                'name' => $request->input('name'),
                'academic_year' => $request->input('academic_year'),
                'term' => $request->input('term'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'description' => $request->input('description'),
                'days_of_week' => $daysOfWeek,
                'updated_at' => now()
            ]);
        
        return redirect()->route('timetables.templates')
            ->with('success', 'Timetable template updated successfully');
    }

    /**
     * Delete a timetable template
     */
    public function destroy($id)
    {
        // Check if template exists and belongs to this school
        $template = DB::table('timetable_templates')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$template) {
            return redirect()->route('timetables.templates')
                ->with('error', 'Template not found');
        }
        
        // Check if template is used in any timetable entries
        $inUse = DB::table('timetable_entries')
            ->where('timetable_template_id', $id)
            ->exists();
            
        if ($inUse) {
            return redirect()->route('timetables.templates')
                ->with('error', 'Cannot delete template as it has timetable entries. Clear the entries first.');
        }
        
        DB::table('timetable_templates')->where('id', $id)->delete();
        
        return redirect()->route('timetables.templates')
            ->with('success', 'Timetable template deleted successfully');
    }

    /**
     * Set a template as active
     */
    public function setActive($id)
    {
        // Check if template exists and belongs to this school
        $template = DB::table('timetable_templates')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$template) {
            return redirect()->route('timetables.templates')
                ->with('error', 'Template not found');
        }
        
        // Begin transaction to ensure data integrity
        DB::beginTransaction();
        
        try {
            // Set all templates for this school as inactive
            DB::table('timetable_templates')
                ->where('school_id', auth()->user()->school_id)
                ->update(['is_active' => false]);
            
            // Set this template as active
            DB::table('timetable_templates')
                ->where('id', $id)
                ->update(['is_active' => true]);
            
            DB::commit();
            
            return redirect()->route('timetables.templates')
                ->with('success', 'Timetable template "' . $template->name . '" is now active');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('timetables.templates')
                ->with('error', 'Failed to set active template: ' . $e->getMessage());
        }
    }
}