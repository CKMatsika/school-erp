<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Payroll;
use App\Models\Admin\Salary;
use App\Models\Admin\Staff;
use App\Models\Teacher\Teacher;
use Carbon\Carbon;
use PDF;

class PayrollController extends Controller
{
    /**
     * Display a listing of the payrolls.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Payroll::query();
        
        // Apply filters
        if ($request->has('month') && $request->month) {
            $query->where('month', $request->month);
        }
        
        if ($request->has('year') && $request->year) {
            $query->where('year', $request->year);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $payrolls = $query->latest()->paginate(10);
        
        // Get available years and months for filter
        $years = Payroll::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];
        
        return view('admin.finance.payroll.index', compact('payrolls', 'years', 'months'));
    }

    /**
     * Show the form for creating a new payroll.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];
        
        $currentYear = date('Y');
        $years = range($currentYear - 1, $currentYear + 1);
        
        return view('admin.finance.payroll.create', compact('months', 'years'));
    }

    /**
     * Store a newly created payroll in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|string|size:2',
            'year' => 'required|string|size:4',
            'payment_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // Check if payroll already exists for this month/year
        $exists = Payroll::where('month', $request->month)
            ->where('year', $request->year)
            ->exists();
            
        if ($exists) {
            return back()->withInput()->withErrors([
                'month' => 'Payroll for this month and year already exists.'
            ]);
        }

        // Create the payroll
        $payroll = Payroll::create([
            'month' => $request->month,
            'year' => $request->year,
            'payment_date' => $request->payment_date,
            'description' => $request->description,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        // Generate salary entries for all employees
        $this->generateSalaryEntries($payroll);

        return redirect()->route('admin.finance.payroll.show', $payroll)
            ->with('success', 'Payroll created successfully.');
    }

    /**
     * Display the specified payroll.
     *
     * @param  \App\Models\Admin\Payroll  $payroll
     * @return \Illuminate\View\View
     */
    public function show(Payroll $payroll)
    {
        $payroll->load('salaries.employee');
        
        // Calculate payroll summaries
        $totalBasicSalary = $payroll->salaries->sum('basic_salary');
        $totalAllowances = $payroll->salaries->sum('total_allowances');
        $totalDeductions = $payroll->salaries->sum('total_deductions');
        $totalNetSalary = $payroll->salaries->sum('net_salary');
        
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];
        
        return view('admin.finance.payroll.show', compact(
            'payroll',
            'totalBasicSalary',
            'totalAllowances',
            'totalDeductions',
            'totalNetSalary',
            'months'
        ));
    }

    /**
     * Show the form for editing the specified payroll.
     *
     * @param  \App\Models\Admin\Payroll  $payroll
     * @return \Illuminate\View\View
     */
    public function edit(Payroll $payroll)
    {
        if ($payroll->status == 'processed') {
            return redirect()->route('admin.finance.payroll.show', $payroll)
                ->with('error', 'Processed payrolls cannot be edited.');
        }
        
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];
        
        $currentYear = date('Y');
        $years = range($currentYear - 1, $currentYear + 1);
        
        return view('admin.finance.payroll.edit', compact('payroll', 'months', 'years'));
    }

    /**
     * Update the specified payroll in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Payroll  $payroll
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->status == 'processed') {
            return redirect()->route('admin.finance.payroll.show', $payroll)
                ->with('error', 'Processed payrolls cannot be edited.');
        }
        
        $request->validate([
            'payment_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $payroll->update([
            'payment_date' => $request->payment_date,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.finance.payroll.show', $payroll)
            ->with('success', 'Payroll updated successfully.');
    }

    /**
     * Remove the specified payroll from storage.
     *
     * @param  \App\Models\Admin\Payroll  $payroll
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->status == 'processed') {
            return redirect()->route('admin.finance.payroll.index')
                ->with('error', 'Processed payrolls cannot be deleted.');
        }
        
        // Delete associated salary entries
        $payroll->salaries()->delete();
        
        // Delete payroll
        $payroll->delete();

        return redirect()->route('admin.finance.payroll.index')
            ->with('success', 'Payroll deleted successfully.');
    }
    
    /**
     * Process the payroll and mark it as processed.
     *
     * @param  \App\Models\Admin\Payroll  $payroll
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Payroll $payroll)
    {
        if ($payroll->status == 'processed') {
            return redirect()->route('admin.finance.payroll.show', $payroll)
                ->with('error', 'Payroll is already processed.');
        }
        
        // Update payroll status
        $payroll->status = 'processed';
        $payroll->processed_at = now();
        $payroll->processed_by = auth()->id();
        $payroll->save();
        
        // You might want to trigger other processes here like:
        // - Sending email notifications
        // - Generating financial entries
        // - Updating employee payment records
        
        return redirect()->route('admin.finance.payroll.show', $payroll)
            ->with('success', 'Payroll has been processed successfully.');
    }
    
    /**
     * Generate PDF of the payroll.
     *
     * @param  \App\Models\Admin\Payroll  $payroll
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(Payroll $payroll)
    {
        $payroll->load('salaries.employee');
        
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];
        
        $monthName = $months[$payroll->month] ?? '';
        
        $pdf = PDF::loadView('admin.finance.payroll.pdf', compact('payroll', 'monthName'));
        
        return $pdf->download("payroll_" . $payroll->month . "_" . $payroll->year . ".pdf");
    }
    
    /**
     * Generate salary entries for all employees.
     *
     * @param  \App\Models\Admin\Payroll  $payroll
     * @return void
     */
    private function generateSalaryEntries(Payroll $payroll)
    {
        // Get all staff
        $staffMembers = Staff::where('status', 'active')->get();
        
        foreach ($staffMembers as $staff) {
            $this->createSalaryEntry($payroll, $staff, 'staff');
        }
        
        // Get all teachers
        $teachers = Teacher::where('status', 'active')->get();
        
        foreach ($teachers as $teacher) {
            $this->createSalaryEntry($payroll, $teacher, 'teacher');
        }
    }
    
    /**
     * Create a salary entry for an employee.
     *
     * @param  \App\Models\Admin\Payroll  $payroll
     * @param  mixed  $employee
     * @param  string  $employeeType
     * @return void
     */
    private function createSalaryEntry($payroll, $employee, $employeeType)
    {
        // Get basic salary based on employee type and department
        $basicSalary = $this->getBasicSalary($employee, $employeeType);
        
        // Calculate allowances
        $allowances = $this->calculateAllowances($employee, $basicSalary, $employeeType);
        
        // Calculate deductions
        $deductions = $this->calculateDeductions($employee, $basicSalary, $employeeType);
        
        // Calculate net salary
        $netSalary = $basicSalary + $allowances - $deductions;
        
        // Create salary record
        Salary::create([
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'employee_type' => $employeeType,
            'basic_salary' => $basicSalary,
            'total_allowances' => $allowances,
            'total_deductions' => $deductions,
            'net_salary' => $netSalary,
            'payment_status' => 'pending',
            'allowances_json' => json_encode($this->getAllowancesDetails($employee, $basicSalary, $employeeType)),
            'deductions_json' => json_encode($this->getDeductionsDetails($employee, $basicSalary, $employeeType)),
        ]);
    }
    
    /**
     * Get employee basic salary.
     *
     * @param  mixed  $employee
     * @param  string  $employeeType
     * @return float
     */
    private function getBasicSalary($employee, $employeeType)
    {
        // This would typically come from a salary scale or employee configuration
        // For demo purposes, using placeholder values
        if ($employeeType == 'teacher') {
            return 50000; // Example basic salary for teachers
        } else {
            // Different departments might have different salary scales
            switch ($employee->department ?? 'general') {
                case 'administration':
                    return 45000;
                case 'maintenance':
                    return 35000;
                case 'security':
                    return 30000;
                default:
                    return 40000; // Default salary
            }
        }
    }
    
    /**
     * Calculate total allowances.
     *
     * @param  mixed  $employee
     * @param  float  $basicSalary
     * @param  string  $employeeType
     * @return float
     */
    private function calculateAllowances($employee, $basicSalary, $employeeType)
    {
        $allowancesDetails = $this->getAllowancesDetails($employee, $basicSalary, $employeeType);
        return array_sum(array_values($allowancesDetails));
    }
    
    /**
     * Get detailed breakdown of allowances.
     *
     * @param  mixed  $employee
     * @param  float  $basicSalary
     * @param  string  $employeeType
     * @return array
     */
    private function getAllowancesDetails($employee, $basicSalary, $employeeType)
    {
        // Sample allowance calculation - in a real system, this would be more dynamic
        $allowances = [
            'house_rent' => $basicSalary * 0.15,
            'transport' => 5000,
            'medical' => 3000,
        ];
        
        // Additional allowances for teachers based on experience or qualifications
        if ($employeeType == 'teacher') {
            if (($employee->experience ?? 0) > 5) {
                $allowances['experience'] = 5000;
            }
            
            if (($employee->experience ?? 0) > 10) {
                $allowances['experience'] += 5000;
            }
        }
        
        return $allowances;
    }
    
    /**
     * Calculate total deductions.
     *
     * @param  mixed  $employee
     * @param  float  $basicSalary
     * @param  string  $employeeType
     * @return float
     */
    private function calculateDeductions($employee, $basicSalary, $employeeType)
    {
        $deductionsDetails = $this->getDeductionsDetails($employee, $basicSalary, $employeeType);
        return array_sum(array_values($deductionsDetails));
    }
    
    /**
     * Get detailed breakdown of deductions.
     *
     * @param  mixed  $employee
     * @param  float  $basicSalary
     * @param  string  $employeeType
     * @return array
     */
    private function getDeductionsDetails($employee, $basicSalary, $employeeType)
    {
        // Sample deduction calculation - in a real system, this would be more dynamic
        $deductions = [
            'tax' => $basicSalary * 0.10,
            'pension' => $basicSalary * 0.05,
            'insurance' => 2000,
        ];
        
        return $deductions;
    }
}