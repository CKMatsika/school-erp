<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TimetableTeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index()
    {
        $teachers = DB::table('timetable_teachers')
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();
            
        return view('timetable.teachers.index', compact('teachers'));
    }

    /**
     * Show form to create a new teacher
     */
    public function create()
    {
        return view('timetable.teachers.create');
    }

    /**
     * Store a new teacher
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'specialty' => 'nullable|string|max:100',
            'max_weekly_hours' => 'nullable|integer|min:1',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('timetables.teachers.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::table('timetable_teachers')->insert([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'specialty' => $request->input('specialty'),
            'max_weekly_hours' => $request->input('max_weekly_hours'),
            'phone_number' => $request->input('phone_number'),
            'notes' => $request->input('notes'),
            'active' => true,
            'school_id' => auth()->user()->school_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return redirect()->route('timetables.teachers')
            ->with('success', 'Teacher created successfully');
    }

    /**
     * Show form to edit teacher
     */
    public function edit($id)
    {
        $teacher = DB::table('timetable_teachers')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$teacher) {
            return redirect()->route('timetables.teachers')
                ->with('error', 'Teacher not found');
        }
        
        return view('timetable.teachers.edit', compact('teacher'));
    }

    /**
     * Update a teacher
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'specialty' => 'nullable|string|max:100',
            'max_weekly_hours' => 'nullable|integer|min:1',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('timetables.teachers.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if teacher exists and belongs to this school
        $teacher = DB::table('timetable_teachers')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$teacher) {
            return redirect()->route('timetables.teachers')
                ->with('error', 'Teacher not found');
        }
        
        DB::table('timetable_teachers')
            ->where('id', $id)
            ->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'specialty' => $request->input('specialty'),
                'max_weekly_hours' => $request->input('max_weekly_hours'),
                'phone_number' => $request->input('phone_number'),
                'notes' => $request->input('notes'),
                'updated_at' => now()
            ]);
        
        return redirect()->route('timetables.teachers')
            ->with('success', 'Teacher updated successfully');
    }

    /**
     * Delete a teacher
     */
    public function destroy($id)
    {
        // Check if teacher exists and belongs to this school
        $teacher = DB::table('timetable_teachers')
            ->where('id', $id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$teacher) {
            return redirect()->route('timetables.teachers')
                ->with('error', 'Teacher not found');
        }
        
        // Check if teacher is used in any timetable entries
        $inUse = DB::table('timetable_entries')
            ->where('teacher_id', $id)
            ->exists();
            
        if ($inUse) {
            return redirect()->route('timetables.teachers')
                ->with('error', 'Cannot delete teacher as they are being used in timetable entries');
        }
        
        DB::table('timetable_teachers')->where('id', $id)->delete();
        
        return redirect()->route('timetables.teachers')
            ->with('success', 'Teacher deleted successfully');
    }
}