<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\Guardian;
use App\Models\Student\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuardianController extends Controller
{
    public function index()
    {
        $guardians = Guardian::where('school_id', auth()->user()->school_id)
                            ->withCount('students')
                            ->orderBy('last_name')
                            ->orderBy('first_name')
                            ->paginate(20);
        
        return view('student.guardians.index', compact('guardians'));
    }

    public function create()
    {
        $students = Student::where('school_id', auth()->user()->school_id)
                          ->where('status', 'active')
                          ->get();
        
        return view('student.guardians.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'relationship' => 'required|in:father,mother,guardian,other',
            'phone_primary' => 'required|string|max:20',
            'is_primary_contact' => 'nullable|boolean',
            'receives_communication' => 'nullable|boolean',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
            'is_emergency_contact' => 'nullable|array',
            'is_emergency_contact.*' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Create guardian
            $guardian = Guardian::create([
                'school_id' => auth()->user()->school_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'relationship' => $request->relationship,
                'email' => $request->email,
                'phone_primary' => $request->phone_primary,
                'phone_secondary' => $request->phone_secondary,
                'address' => $request->address,
                'occupation' => $request->occupation,
                'is_primary_contact' => $request->has('is_primary_contact'),
                'receives_communication' => $request->has('receives_communication'),
            ]);
            
            // Attach students
            if ($request->has('student_ids') && is_array($request->student_ids)) {
                foreach ($request->student_ids as $index => $studentId) {
                    $isEmergencyContact = isset($request->is_emergency_contact[$index]) ? true : false;
                    $guardian->students()->attach($studentId, ['is_emergency_contact' => $isEmergencyContact]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('student.guardians.show', $guardian)
                ->with('success', 'Guardian created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Error creating guardian: ' . $e->getMessage());
        }
    }

    public function show(Guardian $guardian)
    {
        $guardian->load('students');
        
        return view('student.guardians.show', compact('guardian'));
    }

    public function edit(Guardian $guardian)
    {
        $students = Student::where('school_id', auth()->user()->school_id)
                          ->where('status', 'active')
                          ->get();
        
        $attachedStudents = $guardian->students->pluck('id')->toArray();
        $emergencyContacts = $guardian->students->pluck('pivot.is_emergency_contact', 'id')->toArray();
        
        return view('student.guardians.edit', compact('guardian', 'students', 'attachedStudents', 'emergencyContacts'));
    }

    public function update(Request $request, Guardian $guardian)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'relationship' => 'required|in:father,mother,guardian,other',
            'phone_primary' => 'required|string|max:20',
            'is_primary_contact' => 'nullable|boolean',
            'receives_communication' => 'nullable|boolean',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
            'is_emergency_contact' => 'nullable|array',
            'is_emergency_contact.*' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Update guardian
            $guardian->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'relationship' => $request->relationship,
                'email' => $request->email,
                'phone_primary' => $request->phone_primary,
                'phone_secondary' => $request->phone_secondary,
                'address' => $request->address,
                'occupation' => $request->occupation,
                'is_primary_contact' => $request->has('is_primary_contact'),
                'receives_communication' => $request->has('receives_communication'),
            ]);
            
            // Sync students
            $syncData = [];
            if ($request->has('student_ids') && is_array($request->student_ids)) {
                foreach ($request->student_ids as $index => $studentId) {
                    $isEmergencyContact = isset($request->is_emergency_contact[$index]) ? true : false;
                    $syncData[$studentId] = ['is_emergency_contact' => $isEmergencyContact];
                }
            }
            $guardian->students()->sync($syncData);
            
            DB::commit();
            
            return redirect()->route('student.guardians.show', $guardian)
                ->with('success', 'Guardian updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Error updating guardian: ' . $e->getMessage());
        }
    }

    public function destroy(Guardian $guardian)
    {
        try {
            // Detach all students
            $guardian->students()->detach();
            
            // Delete guardian
            $guardian->delete();
            
            return redirect()->route('student.guardians.index')
                ->with('success', 'Guardian deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting guardian: ' . $e->getMessage());
        }
    }
    
    public function addToStudent(Request $request, Student $student)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'relationship' => 'required|in:father,mother,guardian,other',
            'phone_primary' => 'required|string|max:20',
            'is_primary_contact' => 'nullable|boolean',
            'receives_communication' => 'nullable|boolean',
            'is_emergency_contact' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Create guardian
            $guardian = Guardian::create([
                'school_id' => auth()->user()->school_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'relationship' => $request->relationship,
                'email' => $request->email,
                'phone_primary' => $request->phone_primary,
                'phone_secondary' => $request->phone_secondary,
                'address' => $request->address,
                'occupation' => $request->occupation,
                'is_primary_contact' => $request->has('is_primary_contact'),
                'receives_communication' => $request->has('receives_communication'),
            ]);
            
            // Attach to student
            $student->guardians()->attach($guardian->id, [
                'is_emergency_contact' => $request->has('is_emergency_contact')
            ]);
            
            DB::commit();
            
            return redirect()->route('student.students.show', $student)
                ->with('success', 'Guardian added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Error adding guardian: ' . $e->getMessage());
        }
    }
}