<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use App\Models\HumanResources\Staff;
use App\Models\Accounting\Subject; // Adjust namespace if needed
use App\Models\Accounting\SchoolClass; // Adjust namespace if needed
use App\Models\Accounting\AcademicYear;
use App\Models\User; // Adjust namespace if needed
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // For creating user accounts
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Gate::authorize('viewAny', Staff::class); // Authorization

        $query = Staff::query();

        // Filtering
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                  ->orWhere('last_name', 'like', $term)
                  ->orWhere('staff_number', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('phone_number', 'like', $term);
            });
        }
        if ($request->filled('staff_type')) {
            $query->where('staff_type', $request->staff_type);
        }
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
             $query->where('is_active', $request->status === 'active');
        }

        $staffMembers = $query->orderBy('last_name')->orderBy('first_name')->paginate(20)->withQueryString();
        $departments = Staff::distinct()->whereNotNull('department')->pluck('department'); // For filter dropdown

        return view('hr.staff.index', compact('staffMembers', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Gate::authorize('create', Staff::class);
        $departments = Staff::distinct()->whereNotNull('department')->pluck('department');
        // Generate Staff number (improve this logic based on requirements)
        $latestStaff = Staff::withTrashed()->latest('id')->first(); // Include soft deleted if numbers shouldn't be reused immediately
        $nextId = $latestStaff ? $latestStaff->id + 1 : 1;
        $staffNumber = 'EMP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        return view('hr.staff.create', compact('departments','staffNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Gate::authorize('create', Staff::class);

        $validated = $request->validate([
            'staff_number' => 'required|string|max:50|unique:staff,staff_number',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'gender' => 'nullable|string|in:male,female,other', // Adjust values as needed
            'date_of_birth' => 'nullable|date',
            'email' => 'required|string|email|max:255|unique:staff,email|unique:users,email', // Check both tables
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_joined' => 'required|date',
            'employment_type' => 'required|string|in:permanent,contract,temporary,intern',
            'staff_type' => 'required|string|in:teaching,non-teaching,admin',
            'job_title' => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
            'basic_salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'create_user_account' => 'boolean', // Checkbox from form
            'user_role' => 'required_if:create_user_account,true|nullable|string|exists:roles,name', // Example if using spatie/laravel-permission
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['basic_salary'] = $validated['basic_salary'] ?? null; // Ensure null if not provided

        DB::beginTransaction();
        try {
            $userData = null;
            // Create User account if requested
            if ($request->boolean('create_user_account')) {
                $userData = User::create([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                    'password' => Hash::make(Str::random(10)), // Generate random password - inform user!
                    // Add other necessary User fields
                ]);
                // Assign role (using spatie/laravel-permission example)
                if (!empty($validated['user_role'])) {
                    $userData->assignRole($validated['user_role']);
                }
                $validated['user_id'] = $userData->id;
            }

            // Create Staff record
            $staff = Staff::create($validated);

            DB::commit();

            // Maybe send welcome email with password if user created?
            // if($userData) { ... Mail::to($staff->email)->send(...) ... }

            return redirect()->route('hr.staff.show', $staff)->with('success', 'Staff member created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Staff Creation Failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create staff member. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff)
    {
        // Gate::authorize('view', $staff);
        $staff->load(['user', 'subjects', 'classes.academicYear']); // Eager load relationships
        return view('hr.staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        // Gate::authorize('update', $staff);
        $departments = Staff::distinct()->whereNotNull('department')->pluck('department');
        $user = $staff->user; // Get linked user if exists
        $roles = []; // Fetch roles if using spatie/laravel-permission
        // if (class_exists(\Spatie\Permission\Models\Role::class)) {
        //     $roles = \Spatie\Permission\Models\Role::pluck('name', 'name');
        // }

        return view('hr.staff.edit', compact('staff', 'departments', 'user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        // Gate::authorize('update', $staff);

         $validated = $request->validate([
            'staff_number' => ['required','string','max:50', Rule::unique('staff')->ignore($staff->id)],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'email' => ['required','string','email','max:255', Rule::unique('staff')->ignore($staff->id), Rule::unique('users')->ignore($staff->user_id)], // Check uniqueness ignoring self
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_joined' => 'required|date',
            'employment_type' => 'required|string|in:permanent,contract,temporary,intern',
            'staff_type' => 'required|string|in:teaching,non-teaching,admin',
            'job_title' => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
            'basic_salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
             // User account related (handle carefully)
            'update_user_email' => 'boolean', // Checkbox if email should be synced to user account
            'user_role' => 'nullable|string|exists:roles,name', // If user exists and using roles
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['basic_salary'] = $validated['basic_salary'] ?? null;

        DB::beginTransaction();
        try {
            // Update Staff record
            $staff->update($validated);

            // Update linked User account if exists and requested
            if ($staff->user) {
                $userUpdateData = [];
                 if ($request->boolean('update_user_email')) {
                     // Ensure email is validated for uniqueness on users table too
                     $request->validate([
                         'email' => [Rule::unique('users')->ignore($staff->user_id)]
                     ]);
                     $userUpdateData['email'] = $validated['email'];
                 }
                 // Update name?
                 $userUpdateData['name'] = $validated['first_name'] . ' ' . $validated['last_name'];

                 if(!empty($userUpdateData)){
                     $staff->user->update($userUpdateData);
                 }

                 // Sync roles if provided (using spatie example)
                 if ($request->filled('user_role')) {
                      $staff->user->syncRoles([$validated['user_role']]);
                 } else {
                      $staff->user->syncRoles([]); // Remove roles if none selected? Or leave as is?
                 }
            }


            DB::commit();
            return redirect()->route('hr.staff.show', $staff)->with('success', 'Staff member updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Staff Update Failed for ID {$staff->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update staff member. Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        // Gate::authorize('delete', $staff);

        // Add checks - can this staff member be deleted? (e.g., attendance records, payroll history?)
        // Consider using Soft Deletes (already included in model) instead of hard delete.
        // If using soft delete, just call $staff->delete();

        try {
            // If NOT using soft deletes:
            // DB::beginTransaction();
            // // Manually delete related data IF cascade isn't set up or reliable
            // $staff->subjects()->detach();
            // $staff->classes()->detach();
            // // Delete attendance, payroll elements, payslips etc. carefully or restrict deletion
            // if ($staff->user) {
            //     $staff->user->delete(); // Delete linked user? Or just unlink?
            // }
            // $staff->forceDelete(); // Hard delete
            // DB::commit();

            // If using soft deletes:
            $staff->delete(); // This performs a soft delete

            return redirect()->route('hr.staff.index')->with('success', 'Staff member deactivated/deleted successfully.');

        } catch (\Exception $e) {
            // DB::rollBack(); // Only if NOT using soft deletes and using transaction
            Log::error("Staff Deletion Failed for ID {$staff->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to delete staff member.');
        }
    }

    // --- Subject & Class Assignments ---

    /**
     * Show the form for assigning subjects to a teacher.
     */
    public function assignSubjectsForm(Staff $staff)
    {
        // Gate::authorize('assignSubjects', $staff);
        if ($staff->staff_type !== 'teaching') {
            return redirect()->route('hr.staff.show', $staff)->with('error', 'Subjects can only be assigned to teaching staff.');
        }
        $subjects = Subject::orderBy('name')->get(); // Assuming Subject model exists
        $assignedSubjectIds = $staff->subjects()->pluck('subjects.id')->toArray(); // Get IDs of currently assigned subjects

        return view('hr.staff.assign-subjects', compact('staff', 'subjects', 'assignedSubjectIds'));
    }

    /**
     * Update the subjects assigned to a teacher.
     */
    public function syncSubjects(Request $request, Staff $staff)
    {
        // Gate::authorize('assignSubjects', $staff);
         if ($staff->staff_type !== 'teaching') {
            return redirect()->route('hr.staff.show', $staff)->with('error', 'Subjects can only be assigned to teaching staff.');
        }
        $validated = $request->validate([
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id' // Validate each ID in the array
        ]);

        try {
            $staff->subjects()->sync($validated['subjects'] ?? []); // Sync replaces existing assignments
            return redirect()->route('hr.staff.show', $staff)->with('success', 'Subjects assigned successfully.');
        } catch (\Exception $e) {
             Log::error("Subject Assignment Failed for Staff ID {$staff->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to assign subjects.');
        }
    }

     /**
     * Show the form for assigning classes to a teacher.
     */
    public function assignClassesForm(Staff $staff)
    {
        // Gate::authorize('assignClasses', $staff);
         if ($staff->staff_type !== 'teaching') {
            return redirect()->route('hr.staff.show', $staff)->with('error', 'Classes can only be assigned to teaching staff.');
        }
        $classes = SchoolClass::orderBy('name')->get(); // Assuming SchoolClass model exists
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        // Get currently assigned classes maybe grouped by year?
        $assignedClasses = $staff->classes()->with('academicYear')->get()->groupBy('pivot.academic_year_id');

        return view('hr.staff.assign-classes', compact('staff', 'classes', 'academicYears', 'assignedClasses'));
    }

    /**
     * Update the classes assigned to a teacher. (More complex due to academic year pivot)
     */
    public function syncClasses(Request $request, Staff $staff)
    {
        // Gate::authorize('assignClasses', $staff);
         if ($staff->staff_type !== 'teaching') {
            return redirect()->route('hr.staff.show', $staff)->with('error', 'Classes can only be assigned to teaching staff.');
        }

        // Validation needs to handle array input with academic year
        $validated = $request->validate([
            'assignments' => 'nullable|array',
            'assignments.*.class_id' => 'required|exists:school_classes,id',
            'assignments.*.academic_year_id' => 'required|exists:academic_years,id',
        ]);

        // Prepare data for sync - requires keying by class_id with pivot data
        $syncData = [];
        if(isset($validated['assignments'])) {
            foreach ($validated['assignments'] as $assignment) {
                $syncData[$assignment['class_id']] = ['academic_year_id' => $assignment['academic_year_id']];
            }
        }

        try {
            $staff->classes()->sync($syncData); // Sync with pivot data
            return redirect()->route('hr.staff.show', $staff)->with('success', 'Classes assigned successfully.');
        } catch (\Exception $e) {
             Log::error("Class Assignment Failed for Staff ID {$staff->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to assign classes.');
        }
    }

}