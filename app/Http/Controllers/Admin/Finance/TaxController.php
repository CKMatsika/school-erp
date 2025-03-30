<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Tax;
use App\Models\Admin\Staff;
use App\Models\Teacher\Teacher;
use Carbon\Carbon;

class TaxController extends Controller
{
    /**
     * Display a listing of taxes.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Tax::with(['employee']);
        
        // Apply filters
        if ($request->has('year') && $request->year) {
            $query->where('year', $request->year);
        }
        
        if ($request->has('month') && $request->month) {
            $query->where('month', $request->month);
        }
        
        if ($request->has('employee_type') && $request->employee_type) {
            $query->where('employee_type', $request->employee_type);
        }
        
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        
        $taxes = $query->latest()->paginate(15
        $taxes = $query->latest()->paginate(15);
        
        // Get years and months for filter
        $years = Tax::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];
        
        // Get employees for filter
        $teachers = Teacher::with('user')->get();
        $staffMembers = Staff::with('user')->get();
        
        return view('admin.finance.tax.index', compact('taxes', 'years', 'months', 'teachers', 'staffMembers'));
    }

    /**
     * Show the form for creating a new tax record.
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
        
        $teachers = Teacher::with('user')->get();
        $staffMembers = Staff::with('user')->get();
        
        return view('admin.finance.tax.create', compact('months', 'years', 'teachers', 'staffMembers'));
    }

    /**
     * Store a newly created tax record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_type' => 'required|string|in:teacher,staff',
            'employee_id' => 'required|integer',
            'month' => 'required|string|size:2',
            'year' => 'required|string|size:4',
            'gross_income' => 'required|numeric|min:0',
            'taxable_income' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check if tax record already exists
        $exists = Tax::where('employee_type', $request->employee_type)
            ->where('employee_id', $request->employee_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->exists();
            
        if ($exists) {
            return back()->withInput()->withErrors([
                'employee_id' => 'Tax record already exists for this employee in the specified month and year.'
            ]);
        }

        Tax::create($request->all());

        return redirect()->route('admin.finance.tax.index')
            ->with('success', 'Tax record created successfully.');
    }

    /**
     * Display the specified tax record.
     *
     * @param  \App\Models\Admin\Tax  $tax
     * @return \Illuminate\View\View
     */
    public function show(Tax $tax)
    {
        $tax->load('employee');
        
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];
        
        return view('admin.finance.tax.show', compact('tax', 'months'));
    }

    /**
     * Show the form for editing the specified tax record.
     *
     * @param  \App\Models\Admin\Tax  $tax
     * @return \Illuminate\View\View
     */
    public function edit(Tax $tax)
    {
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];
        
        $currentYear = date('Y');
        $years = range($currentYear - 1, $currentYear + 1);
        
        $teachers = Teacher::with('user')->get();
        $staffMembers = Staff::with('user')->get();
        
        return view('admin.finance.tax.edit', compact('tax', 'months', 'years', 'teachers', 'staffMembers'));
    }

    /**
     * Update the specified tax record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Tax  $tax
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Tax $tax)
    {
        $request->validate([
            'gross_income' => 'required|numeric|min:0',
            'taxable_income' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $tax->update($request->all());

        return redirect()->route('admin.finance.tax.show', $tax)
            ->with('success', 'Tax record updated successfully.');
    }

    /**
     * Remove the specified tax record from storage.
     *
     * @param  \App\Models\Admin\Tax  $tax
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Tax $tax)
    {
        $tax->delete();

        return redirect()->route('admin.finance.tax.index')
            ->with('success', 'Tax record deleted successfully.');
    }
    
    /**
     * Generate tax records for all employees.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateBulk(Request $request)
    {
        $request->validate([
            'month' => 'required|string|size:2',
            'year' => 'required|string|size:4',
        ]);

        $month = $request->month;
        $year = $request->year;
        
        // Check if records already exist
        $exists = Tax::where('month', $month)
            ->where('year', $year)
            ->exists();
            
        if ($exists) {
            return back()->withErrors([
                'month' => 'Tax records already exist for some employees in the specified month and year.'
            ]);
        }

        // Process teachers
        $teachers = Teacher::where('status', 'active')->get();
        foreach ($teachers as $teacher) {
            $this->createTaxRecord($teacher, 'teacher', $month, $year);
        }
        
        // Process staff
        $staffMembers = Staff::where('status', 'active')->get();
        foreach ($staffMembers as $staff) {
            $this->createTaxRecord($staff, 'staff', $month, $year);
        }

        return redirect()->route('admin.finance.tax.index', ['month' => $month, 'year' => $year])
            ->with('success', 'Tax records generated successfully for all employees.');
    }
    
    /**
     * Create a tax record for an employee.
     *
     * @param  mixed  $employee
     * @param  string  $employeeType
     * @param  string  $month
     * @param  string  $year
     * @return void
     */
    private function createTaxRecord($employee, $employeeType, $month, $year)
    {
        // Get gross income for the employee
        $grossIncome = $this->getEmployeeGrossIncome($employee, $employeeType);
        
        // Calculate deductions
        $deductions = $this->calculateDeductions($employee, $employeeType);
        
        // Calculate taxable income
        $taxableIncome = $grossIncome - $deductions;
        
        // Calculate tax amount and rate
        list($taxAmount, $taxRate) = $this->calculateTax($taxableIncome);
        
        Tax::create([
            'employee_type' => $employeeType,
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
            'gross_income' => $grossIncome,
            'taxable_income' => $taxableIncome,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'deductions' => $deductions,
            'notes' => 'Automatically generated',
        ]);
    }
    
    /**
     * Get employee's gross income.
     *
     * @param  mixed  $employee
     * @param  string  $employeeType
     * @return float
     */
    private function getEmployeeGrossIncome($employee, $employeeType)
    {
        // This would typically come from salary records or payroll
        // For demo purposes, using placeholder values
        if ($employeeType == 'teacher') {
            $basicSalary = 50000;
            $allowances = $basicSalary * 0.25; // Example: 25% allowances
            return $basicSalary + $allowances;
        } else {
            // Different departments might have different salary scales
            switch ($employee->department ?? 'general') {
                case 'administration':
                    $basicSalary = 45000;
                    break;
                case 'maintenance':
                    $basicSalary = 35000;
                    break;
                case 'security':
                    $basicSalary = 30000;
                    break;
                default:
                    $basicSalary = 40000;
            }
            
            $allowances = $basicSalary * 0.20; // Example: 20% allowances
            return $basicSalary + $allowances;
        }
    }
    
    /**
     * Calculate deductions for an employee.
     *
     * @param  mixed  $employee
     * @param  string  $employeeType
     * @return float
     */
    private function calculateDeductions($employee, $employeeType)
    {
        // This would typically include standard deductions like pension, insurance, etc.
        // For demo purposes, using placeholder values
        $grossIncome = $this->getEmployeeGrossIncome($employee, $employeeType);
        
        // Standard deductions
        $pension = $grossIncome * 0.05; // 5% pension contribution
        $insurance = 2000; // Fixed insurance premium
        
        return $pension + $insurance;
    }
    
    /**
     * Calculate tax amount and rate.
     *
     * @param  float  $taxableIncome
     * @return array [taxAmount, taxRate]
     */
    private function calculateTax($taxableIncome)
    {
        // This would implement the tax brackets and calculation rules
        // For demo purposes, using a simplified progressive tax system
        if ($taxableIncome <= 30000) {
            $taxRate = 5;
            $taxAmount = $taxableIncome * 0.05;
        } elseif ($taxableIncome <= 60000) {
            $taxRate = 10;
            $taxAmount = 1500 + ($taxableIncome - 30000) * 0.10;
        } elseif ($taxableIncome <= 120000) {
            $taxRate = 15;
            $taxAmount = 4500 + ($taxableIncome - 60000) * 0.15;
        } elseif ($taxableIncome <= 200000) {
            $taxRate = 20;
            $taxAmount = 13500 + ($taxableIncome - 120000) * 0.20;
        } else {
            $taxRate = 25;
            $taxAmount = 29500 + ($taxableIncome - 200000) * 0.25;
        }
        
        return [$taxAmount, $taxRate];
    }
    
    /**
     * Generate tax reports.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function reports(Request $request)
    {
        $request->validate([
            'year' => 'required|string|size:4',
        ]);

        $year = $request->year;
        
        // Get all tax records for the year
        $taxRecords = Tax::with('employee')
            ->where('year', $year)
            ->get();
            
        // Group by month
        $taxByMonth = $taxRecords->groupBy('month')
            ->map(function ($items) {
                return [
                    'total_tax' => $items->sum('tax_amount'),
                    'total_income' => $items->sum('gross_income'),
                    'count' => $items->count(),
                ];
            });
            
        // Group by employee type
        $taxByEmployeeType = $taxRecords->groupBy('employee_type')
            ->map(function ($items) {
                return [
                    'total_tax' => $items->sum('tax_amount'),
                    'total_income' => $items->sum('gross_income'),
                    'count' => $items->count(),
                ];
            });
        
        // Summary statistics
        $totalTax = $taxRecords->sum('tax_amount');
        $totalIncome = $taxRecords->sum('gross_income');
        $averageTaxRate = $taxRecords->count() > 0 ? 
            ($totalTax / $totalIncome) * 100 : 0;
        
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];
        
        $years = Tax::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        
        return view('admin.finance.tax.reports', compact(
            'taxRecords',
            'taxByMonth',
            'taxByEmployeeType',
            'totalTax',
            'totalIncome',
            'averageTaxRate',
            'months',
            'years',
            'year'
        ));
    }
}