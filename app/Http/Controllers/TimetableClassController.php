<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TimetableClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index()
    {
        $classes = DB::table('timetable_school_classes')
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();
            
        return view('timetable.classes.index', compact('classes'));
    }
    
    /**
     * Show form to create a new class
     */
    public function create()
    {
        return view('timetable.classes.create');
    }
    
    /**
     * Store a new class
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'level' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'home_room' => 'nullable|string|max:100',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('timetables.classes.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::table('timetable_school_classes')->insert([
            'name' => $request->input('name'),
            'level' => $request->input('level'),
            'capacity' => $request->input('capacity'),
            'home_room' => $request->input('home_room'),
            'notes' => $request->input('notes'),
            'active' => true,
            'school_id' => auth()->user()->school_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return redirect()->route('timetables.classes')
            ->with('success', 'Class created successfully');
    }
    
    /**
     * Show form to edit class
     */
    public function edit($id)
    {
        $class = DB::table('timetable_school_classes')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$class) {
            return redirect()->route('timetables.classes')
                ->with('error', 'Class not found');
        }
        
        return view('timetable.classes.edit', compact('class'));
    }
    
    /**
     * Update a class
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'level' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'home_room' => 'nullable|string|max:100',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('timetables.classes.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if class exists and belongs to this school
        $class = DB::table('timetable_school_classes')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$class) {
            return redirect()->route('timetables.classes')
                ->with('error', 'Class not found');
        }
        
        DB::table('timetable_school_classes')
            ->where('id', $id)
            ->update([
                'name' => $request->input('name'),
                'level' => $request->input('level'),
                'capacity' => $request->input('capacity'),
                'home_room' => $request->input('home_room'),
                'notes' => $request->input('notes'),
                'updated_at' => now()
            ]);
        
        return redirect()->route('timetables.classes')
            ->with('success', 'Class updated successfully');
    }
    
    /**
     * Delete a class
     */
    public function destroy($id)
    {
        // Check if class exists and belongs to this school
        $class = DB::table('timetable_school_classes')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$class) {
            return redirect()->route('timetables.classes')
                ->with('error', 'Class not found');
        }
        
        // Check if class is used in any timetable entries
        $inUse = DB::table('timetable_entries')
            ->where('school_class_id', $id)
            ->exists();
            
        if ($inUse) {
            return redirect()->route('timetables.classes')
                ->with('error', 'Cannot delete class as it is being used in timetable entries');
        }
        
        DB::table('timetable_school_classes')->where('id', $id)->delete();
        
        return redirect()->route('timetables.classes')
            ->with('success', 'Class deleted successfully');
    }
}