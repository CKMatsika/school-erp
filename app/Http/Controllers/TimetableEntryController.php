<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TimetableEntryController extends Controller
{
    /**
     * Display timetable entry editor
     */
    public function editor($templateId = null)
    {
        // If no template ID is provided, use the active template
        if (!$templateId) {
            $template = DB::table('timetable_templates')
                ->where('school_id', auth()->user()->school_id)
                ->where('is_active', true)
                ->first();
                
            if (!$template) {
                return redirect()->route('timetables.templates')
                    ->with('error', 'You need to set an active timetable template first.');
            }
            
            $templateId = $template->id;
        } else {
            $template = DB::table('timetable_templates')
                ->where('id', $templateId)
                ->where('school_id', auth()->user()->school_id)
                ->first();
                
            if (!$template) {
                return redirect()->route('timetables.templates')
                    ->with('error', 'Template not found.');
            }
        }
        
        // Get periods, classes, subjects, and teachers for this school
        $periods = DB::table('timetable_periods')
            ->where('school_id', auth()->user()->school_id)
            ->where('active', true)
            ->orderBy('order')
            ->get();
        
        $classes = DB::table('timetable_school_classes')
            ->where('school_id', auth()->user()->school_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();
        
        $subjects = DB::table('timetable_subjects')
            ->where('school_id', auth()->user()->school_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();
        
        $teachers = DB::table('timetable_teachers')
            ->where('school_id', auth()->user()->school_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();
        
        // Get existing entries for this template
        $entries = DB::table('timetable_entries')
            ->where('timetable_template_id', $templateId)
            ->get();
        
        // Parse days of week from the template
        $daysOfWeek = json_decode($template->days_of_week);
        
        return view('timetable.entries.editor', compact(
            'template', 
            'periods', 
            'classes', 
            'subjects', 
            'teachers', 
            'entries', 
            'daysOfWeek'
        ));
    }
    
    /**
     * Store a new timetable entry
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timetable_template_id' => 'required|exists:timetable_templates,id',
            'school_class_id' => 'required|exists:timetable_school_classes,id',
            'subject_id' => 'required|exists:timetable_subjects,id',
            'teacher_id' => 'required|exists:timetable_teachers,id',
            'period_id' => 'required|exists:timetable_periods,id',
            'day_of_week' => 'required|integer|between:1,7',
            'room' => 'nullable|string|max:50',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verify that template belongs to this school
        $template = DB::table('timetable_templates')
            ->where('id', $request->timetable_template_id)
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid template.'
            ], 403);
        }
        
        // Check for conflicts (same class, same period, same day)
        $conflict = DB::table('timetable_entries')
            ->where('timetable_template_id', $request->timetable_template_id)
            ->where('school_class_id', $request->school_class_id)
            ->where('period_id', $request->period_id)
            ->where('day_of_week', $request->day_of_week)
            ->exists();
            
        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'This class already has a lesson scheduled at this time.'
            ], 409);
        }
        
        // Check for teacher conflicts (same teacher, same period, same day)
        $teacherConflict = DB::table('timetable_entries')
            ->where('timetable_template_id', $request->timetable_template_id)
            ->where('teacher_id', $request->teacher_id)
            ->where('period_id', $request->period_id)
            ->where('day_of_week', $request->day_of_week)
            ->exists();
            
        if ($teacherConflict) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already scheduled for another class at this time.'
            ], 409);
        }
        
        // Create the entry
        $entryId = DB::table('timetable_entries')->insertGetId([
            'timetable_template_id' => $request->timetable_template_id,
            'school_class_id' => $request->school_class_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'period_id' => $request->period_id,
            'day_of_week' => $request->day_of_week,
            'room' => $request->room,
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Get entry with related data for response
        $entry = DB::table('timetable_entries')
            ->select('timetable_entries.*', 
                'timetable_school_classes.name as class_name',
                'timetable_subjects.name as subject_name', 
                'timetable_subjects.color_code as subject_color',
                'timetable_teachers.name as teacher_name',
                'timetable_periods.name as period_name')
            ->join('timetable_school_classes', 'timetable_entries.school_class_id', '=', 'timetable_school_classes.id')
            ->join('timetable_subjects', 'timetable_entries.subject_id', '=', 'timetable_subjects.id')
            ->join('timetable_teachers', 'timetable_entries.teacher_id', '=', 'timetable_teachers.id')
            ->join('timetable_periods', 'timetable_entries.period_id', '=', 'timetable_periods.id')
            ->where('timetable_entries.id', $entryId)
            ->first();
        
        return response()->json([
            'success' => true,
            'entry' => $entry,
            'message' => 'Timetable entry created successfully.'
        ]);
    }
    
    /**
     * Update a timetable entry
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:timetable_subjects,id',
            'teacher_id' => 'required|exists:timetable_teachers,id',
            'room' => 'nullable|string|max:50',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verify that entry exists and belongs to a template in this school
        $entry = DB::table('timetable_entries')
            ->select('timetable_entries.*', 'timetable_templates.school_id')
            ->join('timetable_templates', 'timetable_entries.timetable_template_id', '=', 'timetable_templates.id')
            ->where('timetable_entries.id', $id)
            ->first();
            
        if (!$entry || $entry->school_id != auth()->user()->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Entry not found.'
            ], 404);
        }
        
        // Check for teacher conflicts (same teacher, same period, same day)
        $teacherConflict = DB::table('timetable_entries')
            ->where('timetable_template_id', $entry->timetable_template_id)
            ->where('teacher_id', $request->teacher_id)
            ->where('period_id', $entry->period_id)
            ->where('day_of_week', $entry->day_of_week)
            ->where('id', '!=', $id)
            ->exists();
            
        if ($teacherConflict) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already scheduled for another class at this time.'
            ], 409);
        }
        
        // Update the entry
        DB::table('timetable_entries')
            ->where('id', $id)
            ->update([
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
                'room' => $request->room,
                'notes' => $request->notes,
                'updated_at' => now()
            ]);
        
        // Get updated entry with related data for response
        $updatedEntry = DB::table('timetable_entries')
            ->select('timetable_entries.*', 
                'timetable_school_classes.name as class_name',
                'timetable_subjects.name as subject_name', 
                'timetable_subjects.color_code as subject_color',
                'timetable_teachers.name as teacher_name',
                'timetable_periods.name as period_name')
            ->join('timetable_school_classes', 'timetable_entries.school_class_id', '=', 'timetable_school_classes.id')
            ->join('timetable_subjects', 'timetable_entries.subject_id', '=', 'timetable_subjects.id')
            ->join('timetable_teachers', 'timetable_entries.teacher_id', '=', 'timetable_teachers.id')
            ->join('timetable_periods', 'timetable_entries.period_id', '=', 'timetable_periods.id')
            ->where('timetable_entries.id', $id)
            ->first();
        
        return response()->json([
            'success' => true,
            'entry' => $updatedEntry,
            'message' => 'Timetable entry updated successfully.'
        ]);
    }
    
    /**
     * Delete a timetable entry
     */
    public function destroy($id)
    {
        // Verify that entry exists and belongs to a template in this school
        $entry = DB::table('timetable_entries')
            ->select('timetable_entries.id', 'timetable_templates.school_id')
            ->join('timetable_templates', 'timetable_entries.timetable_template_id', '=', 'timetable_templates.id')
            ->where('timetable_entries.id', $id)
            ->first();
            
        if (!$entry || $entry->school_id != auth()->user()->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Entry not found.'
            ], 404);
        }
        
        // Delete the entry
        DB::table('timetable_entries')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Timetable entry deleted successfully.'
        ]);
    }
}