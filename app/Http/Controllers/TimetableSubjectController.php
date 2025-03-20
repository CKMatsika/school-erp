<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TimetableSubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index()
    {
        $subjects = DB::table('timetable_subjects')
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();
            
        return view('timetable.subjects.index', compact('subjects'));
    }

    /**
     * Show form to create a new subject
     */
    public function create()
    {
        return view('timetable.subjects.create');
    }

    /**
     * Store a new subject
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('timetables.subjects')
            ->with('success', 'Subject created successfully');
    }

    /**
     * Show form to edit subject
     */
    public function edit($id)
    {
        $subject = DB::table('timetable_subjects')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$subject) {
            return redirect()->route('timetables.subjects')
                ->with('error', 'Subject not found');
        }
        
        return view('timetable.subjects.edit', compact('subject'));
    }

    /**
     * Update a subject
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('timetables.subjects.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if subject exists and belongs to this school
        $subject = DB::table('timetable_subjects')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$subject) {
            return redirect()->route('timetables.subjects')
                ->with('error', 'Subject not found');
        }
        
        DB::table('timetable_subjects')
            ->where('id', $id)
            ->update([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'description' => $request->input('description'),
                'color_code' => $request->input('color_code', '#3490dc'),
                'updated_at' => now()
            ]);
        
        return redirect()->route('timetables.subjects')
            ->with('success', 'Subject updated successfully');
    }

    /**
     * Delete a subject
     */
    public function destroy($id)
    {
        // Check if subject exists and belongs to this school
        $subject = DB::table('timetable_subjects')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$subject) {
            return redirect()->route('timetables.subjects')
                ->with('error', 'Subject not found');
        }
        
        // Check if subject is used in any timetable entries
        $inUse = DB::table('timetable_entries')
            ->where('subject_id', $id)
            ->exists();
            
        if ($inUse) {
            return redirect()->route('timetables.subjects')
                ->with('error', 'Cannot delete subject as it is being used in timetable entries');
        }
        
        DB::table('timetable_subjects')->where('id', $id)->delete();
        
        return redirect()->route('timetables.subjects')
            ->with('success', 'Subject deleted successfully');
    }('timetables.subjects.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::table('timetable_subjects')->insert([
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'description' => $request->input('description'),
            'color_code' => $request->input('color_code', '#3490dc'),
            'active' => true,
            'school_id' => auth()->user()->school_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return redirect()->route