<?php

namespace App\Http\Controllers\Admin\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeavePolicyController extends Controller
{
    /**
     * Display a listing of all leave policies.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all leave policies data
        
        return view('admin.leave.policies.index');
    }

    /**
     * Show the form for creating a new leave policy.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Load necessary data for the form
        
        return view('admin.leave.policies.create');
    }

    /**
     * Store a newly created leave policy in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate leave policy data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:leave_policies,name',
            'description' => 'required|string',
            'applicable_to' => 'required|in:all,teaching,non-teaching,specific',
            'department_ids' => 'nullable|array|required_if:applicable_to,specific',
            'department_ids.*' => 'exists:departments,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'is_active' => 'boolean',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new leave policy
        // Save data to database
        
        return redirect()->route('admin.leave.policies.index')
            ->with('success', 'Leave policy created successfully');
    }

    /**
     * Display the specified leave policy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Retrieve the leave policy
        
        return view('admin.leave.policies.show', compact('policy'));
    }

    /**
     * Show the form for editing the specified leave policy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Retrieve the leave policy
        // Load necessary data for the form
        
        return view('admin.leave.policies.edit', compact('policy'));
    }

    /**
     * Update the specified leave policy in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate leave policy data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:leave_policies,name,'.$id,
            'description' => 'required|string',
            'applicable_to' => 'required|in:all,teaching,non-teaching,specific',
            'department_ids' => 'nullable|array|required_if:applicable_to,specific',
            'department_ids.*' => 'exists:departments,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'is_active' => 'boolean',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update leave policy
        // Save data to database
        
        return redirect()->route('admin.leave.policies.index')
            ->with('success', 'Leave policy updated successfully');
    }

    /**
     * Remove the specified leave policy from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Delete leave policy
        
        return redirect()->route('admin.leave.policies.index')
            ->with('success', 'Leave policy deleted successfully');
    }

    /**
     * Display a listing of all leave types.
     *
     * @return \Illuminate\Http\Response
     */
    public function leaveTypes()
    {
        // Get all leave types data
        
        return view('admin.leave.policies.types.index');
    }

    /**
     * Show the form for creating a new leave type.
     *
     * @return \Illuminate\Http\Response
     */
    public function createLeaveType()
    {
        return view('admin.leave.policies.types.create');
    }

    /**
     * Store a newly created leave type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeLeaveType(Request $request)
    {
        // Validate leave type data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:leave_types,name',
            'code' => 'required|string|max:20|unique:leave_types,code',
            'days_allowed' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_paid' => 'boolean',
            'is_carryforward' => 'boolean',
            'max_carryforward_days' => 'nullable|integer|min:0|required_if:is_carryforward,1',
            'is_encashable' => 'boolean',
            'max_encashable_days' => 'nullable|integer|min:0|required_if:is_encashable,1',
            'consecutive_limit' => 'nullable|integer|min:0',
            'attachment_required' => 'boolean',
            'attachment_required_after_days' => 'nullable|integer|min:0|required_if:attachment_required,1',
            'notice_days' => 'nullable|integer|min:0',
            'applicable_to' => 'required|in:all,teaching,non-teaching,males,females',
            'is_active' => 'boolean',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new leave type
        // Save data to database
        
        return redirect()->route('admin.leave.policies.types.index')
            ->with('success', 'Leave type created successfully');
    }

    /**
     * Show the form for editing the specified leave type.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editLeaveType($id)
    {
        // Retrieve the leave type
        
        return view('admin.leave.policies.types.edit', compact('leaveType'));
    }

    /**
     * Update the specified leave type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateLeaveType(Request $request, $id)
    {
        // Validate leave type data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:leave_types,name,'.$id,
            'code' => 'required|string|max:20|unique:leave_types,code,'.$id,
            'days_allowed' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_paid' => 'boolean',
            'is_carryforward' => 'boolean',
            'max_carryforward_days' => 'nullable|integer|min:0|required_if:is_carryforward,1',
            'is_encashable' => 'boolean',
            'max_encashable_days' => 'nullable|integer|min:0|required_if:is_encashable,1',
            'consecutive_limit' => 'nullable|integer|min:0',
            'attachment_required' => 'boolean',
            'attachment_required_after_days' => 'nullable|integer|min:0|required_if:attachment_required,1',
            'notice_days' => 'nullable|integer|min:0',
            'applicable_to' => 'required|in:all,teaching,non-teaching,males,females',
            'is_active' => 'boolean',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update leave type
        // Save data to database
        
        return redirect()->route('admin.leave.policies.types.index')
            ->with('success', 'Leave type updated successfully');
    }

    /**
     * Remove the specified leave type from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyLeaveType($id)
    {
        // Delete leave type
        
        return redirect()->route('admin.leave.policies.types.index')
            ->with('success', 'Leave type deleted successfully');
    }

    /**
     * Display leave allocation page.
     *
     * @return \Illuminate\Http\Response
     */
    public function leaveAllocation()
    {
        // Get leave allocation data
        
        return view('admin.leave.policies.allocation.index');
    }

    /**
     * Allocate leave to staff.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function allocateLeave(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'leave_type_id' => 'required|exists:leave_types,id',
            'staff_ids' => 'required|array',
            'staff_ids.*' => 'exists:staff,id',
            'days' => 'required|integer|min:1',
            'year' => 'required|integer|min:' . date('Y'),
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Allocate leave to staff
        // Save data to database
        // Notify staff members
        
        return redirect()->route('admin.leave.policies.allocation.index')
            ->with('success', 'Leave allocated successfully');
    }

    /**
     * Display leave encashment page.
     *
     * @return \Illuminate\Http\Response
     */
    public function leaveEncashment()
    {
        // Get leave encashment data
        
        return view('admin.leave.policies.encashment.index');
    }

    /**
     * Process leave encashment request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function processEncashment(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:staff,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'days' => 'required|integer|min:1',
            'rate_per_day' => 'required|numeric|min:0',
            'encashment_date' => 'required|date',
            'payment_method' => 'required|string|max:100',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Process leave encashment
        // Check leave balance
        // Calculate amount
        // Save data to database
        // Update leave balance
        
        return redirect()->route('admin.leave.policies.encashment.index')
            ->with('success', 'Leave encashment processed successfully');
    }

    /**
     * Display holidays management page.
     *
     * @return \Illuminate\Http\Response
     */
    public function holidays()
    {
        // Get holidays data
        
        return view('admin.leave.policies.holidays.index');
    }

    /**
     * Show the form for creating a new holiday.
     *
     * @return \Illuminate\Http\Response
     */
    public function createHoliday()
    {
        return view('admin.leave.policies.holidays.create');
    }

    /**
     * Store a newly created holiday in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeHoliday(Request $request)
    {
        // Validate holiday data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean',
            'applicable_to' => 'required|string|max:100',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new holiday
        // Save data to database
        
        return redirect()->route('admin.leave.policies.holidays.index')
            ->with('success', 'Holiday created successfully');
    }

    /**
     * Display leave reports.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request)
    {
        // Generate reports based on request parameters
        
        return view('admin.leave.policies.reports.index');
    }

    /**
     * Reset annual leave balances.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetLeaveBalances(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:' . date('Y'),
            'leave_type_ids' => 'required|array',
            'leave_type_ids.*' => 'exists:leave_types,id',
            'carryforward' => 'boolean',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Reset leave balances
        // Apply carryforward if enabled
        // Save data to database
        // Notify staff members
        
        return redirect()->route('admin.leave.policies.index')
            ->with('success', 'Leave balances reset successfully');
    }

    /**
     * Display weekly off settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function weeklyOffSettings()
    {
        // Get weekly off settings
        
        return view('admin.leave.policies.weekly_off.index');
    }

    /**
     * Update weekly off settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateWeeklyOffSettings(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'weekly_off_days' => 'required|array',
            'weekly_off_days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'applicable_to' => 'required|in:all,teaching,non-teaching,specific',
            'department_ids' => 'nullable|array|required_if:applicable_to,specific',
            'department_ids.*' => 'exists:departments,id',
            'effective_from' => 'required|date',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update weekly off settings
        // Save data to database
        
        return redirect()->route('admin.leave.policies.weekly_off.index')
            ->with('success', 'Weekly off settings updated successfully');
    }
}