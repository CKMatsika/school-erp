<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display a listing of all staff members.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all staff data
        
        return view('admin.hr.staff.index');
    }

    /**
     * Show the form for creating a new staff member.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Load necessary data for the form (e.g., departments, designations)
        
        return view('admin.hr.staff.create');
    }

    /**
     * Store a newly created staff member in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate staff data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:staff,email',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date|before:18 years ago',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'joining_date' => 'required|date',
            'employment_type' => 'required|in:full-time,part-time,contract,temporary',
            'basic_salary' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relation' => 'required|string|max:100',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new staff member
        // Save data to database
        // Upload photo if provided
        // Create user account with hashed password
        
        return redirect()->route('admin.hr.staff.index')
            ->with('success', 'Staff member created successfully');
    }

    /**
     * Display the specified staff member.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Retrieve the staff member
        
        return view('admin.hr.staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified staff member.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Retrieve the staff member
        // Load necessary data for the form
        
        return view('admin.hr.staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff member in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate staff data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:staff,email,'.$id,
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date|before:18 years ago',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'joining_date' => 'required|date',
            'employment_type' => 'required|in:full-time,part-time,contract,temporary',
            'basic_salary' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relation' => 'required|string|max:100',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update staff member
        // Save data to database
        // Upload photo if provided
        
        return redirect()->route('admin.hr.staff.index')
            ->with('success', 'Staff member updated successfully');
    }

    /**
     * Remove the specified staff member from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Delete staff member
        
        return redirect()->route('admin.hr.staff.index')
            ->with('success', 'Staff member deleted successfully');
    }

    /**
     * Display staff attendance.
     *
     * @return \Illuminate\Http\Response
     */
    public function attendance()
    {
        // Get staff attendance data
        
        return view('admin.hr.staff.attendance.index');
    }

    /**
     * Mark staff attendance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function markAttendance(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'staff_ids' => 'required|array',
            'staff_ids.*' => 'exists:staff,id',
            'statuses' => 'required|array',
            'statuses.*' => 'in:present,absent,late,leave,half-day',
            'remarks.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Mark staff attendance
        // Save data to database
        
        return redirect()->route('admin.hr.staff.attendance.index')
            ->with('success', 'Attendance marked successfully');
    }

    /**
     * Display staff documents.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function documents($id)
    {
        // Retrieve the staff member and documents
        
        return view('admin.hr.staff.documents', compact('staff'));
    }

    /**
     * Upload staff document.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadDocument(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'expiry_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Upload document
        // Save data to database
        
        return redirect()->route('admin.hr.staff.documents', $id)
            ->with('success', 'Document uploaded successfully');
    }

    /**
     * Display staff payroll.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function payroll($id)
    {
        // Retrieve the staff member and payroll data
        
        return view('admin.hr.staff.payroll', compact('staff'));
    }

    /**
     * Generate salary slip for staff member.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generateSalarySlip(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|array',
            'allowances.*' => 'numeric|min:0',
            'deductions' => 'nullable|array',
            'deductions.*' => 'numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate salary slip
        // Save data to database
        
        return redirect()->route('admin.hr.staff.payroll', $id)
            ->with('success', 'Salary slip generated successfully');
    }

    /**
     * Display staff leave records.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function leaves($id)
    {
        // Retrieve the staff member and leave records
        
        return view('admin.hr.staff.leaves', compact('staff'));
    }

    /**
     * Display staff reports.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request)
    {
        // Generate reports based on request parameters
        
        return view('admin.hr.staff.reports.index');
    }

    /**
     * Display staff qualifications.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function qualifications($id)
    {
        // Retrieve the staff member and qualifications
        
        return view('admin.hr.staff.qualifications', compact('staff'));
    }

    /**
     * Add staff qualification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addQualification(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'qualification_type' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'grade' => 'nullable|string|max:50',
            'certificate' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Add qualification
        // Save data to database
        // Upload certificate if provided
        
        return redirect()->route('admin.hr.staff.qualifications', $id)
            ->with('success', 'Qualification added successfully');
    }

    /**
     * Display staff experience.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function experience($id)
    {
        // Retrieve the staff member and experience records
        
        return view('admin.hr.staff.experience', compact('staff'));
    }

    /**
     * Add staff experience.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addExperience(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'organization' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'currently_working' => 'boolean',
            'description' => 'nullable|string',
            'certificate' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Add experience
        // Save data to database
        // Upload certificate if provided
        
        return redirect()->route('admin.hr.staff.experience', $id)
            ->with('success', 'Experience added successfully');
    }

    /**
     * Reset staff password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Reset password
        // Hash new password
        // Save data to database
        
        return redirect()->route('admin.hr.staff.show', $id)
            ->with('success', 'Password reset successfully');
    }

    /**
     * Display departments.
     *
     * @return \Illuminate\Http\Response
     */
    public function departments()
    {
        // Get all departments
        
        return view('admin.hr.staff.departments.index');
    }

    /**
     * Display designations.
     *
     * @return \Illuminate\Http\Response
     */
    public function designations()
    {
        // Get all designations
        
        return view('admin.hr.staff.designations.index');
    }
}