<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payroll_month',
        'payroll_year',
        'start_date',
        'end_date',
        'payment_date',
        'status', // 'draft', 'processing', 'approved', 'paid', 'cancelled'
        'remarks',
        'created_by',
        'approved_by',
        'approval_date',
        'payment_method',
        'total_basic_salary',
        'total_allowances',
        'total_deductions',
        'total_net_salary',
        'total_employees',
        'bank_file_generated',
        'bank_file_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'approval_date' => 'date',
        'total_basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net_salary' => 'decimal:2',
        'total_employees' => 'integer',
        'bank_file_generated' => 'boolean',
    ];

    /**
     * Get the staff member who created this payroll.
     */
    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the staff member who approved this payroll.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Get the salary records for this payroll.
     */
    public function salaryRecords()
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * Scope a query to only include payrolls for a specific month and year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $month
     * @param  int  $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMonthYear($query, $month, $year)
    {
        return $query->where('payroll_month', $month)
                    ->where('payroll_year', $year);
    }

    /**
     * Scope a query to only include payrolls with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include draft payrolls.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDrafts($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include paid payrolls.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Get formatted month and year.
     *
     * @return string
     */
    public function getFormattedMonthYearAttribute()
    {
        return date('F Y', mktime(0, 0, 0, $this->payroll_month, 1, $this->payroll_year));
    }

    /**
     * Generate salary records for all staff for this payroll.
     *
     * @param  bool  $includeInactive  Whether to include inactive staff
     * @return int  Number of records generated
     */
    public function generateSalaryRecords($includeInactive = false)
    {
        if ($this->status !== 'draft') {
            return 0;
        }
        
        // Get staff query
        $staffQuery = Staff::query();
        
        if (!$includeInactive) {
            $staffQuery->where('status', 'active');
        }
        
        $staffList = $staffQuery->get();
        $count = 0;
        
        foreach ($staffList as $staff) {
            // Skip if already has a salary record for this payroll
            if ($this->salaryRecords()->where('staff_id', $staff->id)->exists()) {
                continue;
            }
            
            // Calculate work days, leave days, etc.
            $workDays = $this->getWorkDaysCount($staff->id);
            $leaveDays = $this->getLeaveDaysCount($staff->id);
            $absentDays = $this->getAbsentDaysCount($staff->id);
            
            // Get deductions for leaves, absences, etc.
            $leaveDeductions = $this->calculateLeaveDeductions($staff->id, $leaveDays);
            $absenceDeductions = $this->calculateAbsenceDeductions($staff->id, $absentDays);
            $taxDeductions = $this->calculateTaxDeductions($staff->id);
            $otherDeductions = $this->calculateOtherDeductions($staff->id);
            
            // Get allowances
            $allowances = $this->calculateAllowances($staff->id);
            
            // Create salary record
            $salary = $this->salaryRecords()->create([
                'staff_id' => $staff->id,
                'basic_salary' => $staff->basic_salary,
                'work_days' => $workDays,
                'leave_days' => $leaveDays,
                'absent_days' => $absentDays,
                'overtime_hours' => 0, // This would need to be calculated
                'allowances' => $allowances,
                'deductions' => [
                    'leave' => $leaveDeductions,
                    'absence' => $absenceDeductions,
                    'tax' => $taxDeductions,
                    'other' => $otherDeductions,
                ],
                'gross_salary' => $staff->basic_salary + array_sum($allowances),
                'total_deductions' => $leaveDeductions + $absenceDeductions + $taxDeductions + $otherDeductions,
                'net_salary' => $staff->basic_salary + array_sum($allowances) - ($leaveDeductions + $absenceDeductions + $taxDeductions + $otherDeductions),
                'status' => 'pending',
            ]);
            
            if ($salary) {
                $count++;
            }
        }
        
        // Update payroll totals
        $this->updateTotals();
        
        return $count;
    }

    /**
     * Calculate work days for a staff member in this payroll period.
     *
     * @param  int  $staffId
     * @return int
     */
    protected function getWorkDaysCount($staffId)
    {
        // This is a placeholder. The actual implementation would:
        // 1. Get the total working days in the payroll period
        // 2. Subtract leave days, absent days, holidays, etc.
        
        $startDate = $this->start_date;
        $endDate = $this->end_date;
        $workingDays = 0;
        
        for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
            // Skip weekends
            if ($date->format('N') >= 6) {
                continue;
            }
            
            // Check if it's a holiday
            $isHoliday = false; // This would check against a holidays table
            
            if (!$isHoliday) {
                $workingDays++;
            }
        }
        
        // Subtract leave days and absent days
        $workingDays -= $this->getLeaveDaysCount($staffId);
        $workingDays -= $this->getAbsentDaysCount($staffId);
        
        return max(0, $workingDays);
    }

    /**
     * Calculate leave days for a staff member in this payroll period.
     *
     * @param  int  $staffId
     * @return int
     */
    protected function getLeaveDaysCount($staffId)
    {
        // Get approved leave applications for this staff in the payroll period
        $leaveDays = LeaveApplication::where('staff_id', $staffId)
                                    ->where('status', 'approved')
                                    ->where(function ($query) {
                                        $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                                              ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                                              ->orWhere(function ($q) {
                                                  $q->where('start_date', '<', $this->start_date)
                                                    ->where('end_date', '>', $this->end_date);
                                              });
                                    })
                                    ->sum('days_count');
        
        return $leaveDays;
    }

    /**
     * Calculate absent days for a staff member in this payroll period.
     *
     * @param  int  $staffId
     * @return int
     */
    protected function getAbsentDaysCount($staffId)
    {
        // Get absent records for this staff in the payroll period
        $absentDays = StaffAttendance::where('staff_id', $staffId)
                                     ->where('status', 'absent')
                                     ->whereBetween('date', [$this->start_date, $this->end_date])
                                     ->count();
        
        return $absentDays;
    }

    /**
     * Calculate leave deductions for a staff member.
     *
     * @param  int  $staffId
     * @param  int  $leaveDays
     * @return float
     */
    protected function calculateLeaveDeductions($staffId, $leaveDays)
    {
        // This is a placeholder. The actual implementation would:
        // 1. Get leave types and policies
        // 2. Calculate deductions based on leave type and days
        
        $staff = Staff::find($staffId);
        $dailyRate = $staff->basic_salary / 30; // Assuming 30-day month
        $deduction = 0;
        
        // Get leave applications with details
        $leaveApplications = LeaveApplication::with('leaveType')
                                            ->where('staff_id', $staffId)
                                            ->where('status', 'approved')
                                            ->where(function ($query) {
                                                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                                                      ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                                                      ->orWhere(function ($q) {
                                                          $q->where('start_date', '<', $this->start_date)
                                                            ->where('end_date', '>', $this->end_date);
                                                      });
                                            })
                                            ->get();
        
        foreach ($leaveApplications as $leave) {
            // If leave is not paid, deduct for those days
            if ($leave->leaveType && !$leave->leaveType->is_paid) {
                $deduction += $dailyRate * $leave->days_count;
            }
        }
        
        return $deduction;
    }

    /**
     * Calculate absence deductions for a staff member.
     *
     * @param  int  $staffId
     * @param  int  $absentDays
     * @return float
     */
    protected function calculateAbsenceDeductions($staffId, $absentDays)
    {
        $staff = Staff::find($staffId);
        $dailyRate = $staff->basic_salary / 30; // Assuming 30-day month
        
        return $dailyRate * $absentDays;
    }

    /**
     * Calculate tax deductions for a staff member.
     *
     * @param  int  $staffId
     * @return float
     */
    protected function calculateTaxDeductions($staffId)
    {
        // This is a placeholder. The actual implementation would:
        // 1. Get staff salary and other taxable income
        // 2. Apply tax brackets and rates
        // 3. Consider tax exemptions and rebates
        
        $staff = Staff::find($staffId);
        $grossSalary = $staff->basic_salary;
        
        // Simple tax calculation (this would be more complex in reality)
        // Example: 15% flat tax on income over $1,000
        $taxableIncome = max(0, $grossSalary - 1000);
        $tax = $taxableIncome * 0.15;
        
        return $tax;
    }

    /**
     * Calculate other deductions for a staff member.
     *
     * @param  int  $staffId
     * @return float
     */
    protected function calculateOtherDeductions($staffId)
    {
        // This is a placeholder. The actual implementation would:
        // 1. Get loans, advances, etc.
        // 2. Get pension contributions, insurance premiums, etc.
        
        return 0;
    }

    /**
     * Calculate allowances for a staff member.
     *
     * @param  int  $staffId
     * @return array
     */
    protected function calculateAllowances($staffId)
    {
        // This is a placeholder. The actual implementation would:
        // 1. Get various allowance types (housing, transport, etc.)
        // 2. Calculate amounts based on staff grade, position, etc.
        
        return [
            'housing' => 0,
            'transport' => 0,
            'meals' => 0,
            'other' => 0,
        ];
    }

    /**
     * Update payroll totals based on salary records.
     *
     * @return bool
     */
    public function updateTotals()
    {
        $this->total_basic_salary = $this->salaryRecords()->sum('basic_salary');
        $this->total_allowances = $this->salaryRecords()->sum(DB::raw('JSON_EXTRACT(allowances, "$.housing") + 
                                                                         JSON_EXTRACT(allowances, "$.transport") + 
                                                                         JSON_EXTRACT(allowances, "$.meals") + 
                                                                         JSON_EXTRACT(allowances, "$.other")'));
        $this->total_deductions = $this->salaryRecords()->sum('total_deductions');
        $this->total_net_salary = $this->salaryRecords()->sum('net_salary');
        $this->total_employees = $this->salaryRecords()->count();
        
        return $this->save();
    }

    /**
     * Approve the payroll.
     *
     * @param  int  $approvedBy  Staff ID
     * @param  string|null  $remarks
     * @return bool
     */
    public function approve($approvedBy, $remarks = null)
    {
        if ($this->status !== 'processing') {
            return false;
        }
        
        $this->status = 'approved';
        $this->approved_by = $approvedBy;
        $this->approval_date = now();
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Approval remarks: {$remarks}";
        }
        
        // Update status of all salary records
        $this->salaryRecords()->update(['status' => 'approved']);
        
        return $this->save();
    }

    /**
     * Mark the payroll as paid.
     *
     * @param  string  $paymentMethod
     * @param  string|null  $remarks
     * @return bool
     */
    public function markAsPaid($paymentMethod, $remarks = null)
    {
        if ($this->status !== 'approved') {
            return false;
        }
        
        $this->status = 'paid';
        $this->payment_method = $paymentMethod;
        $this->payment_date = now();
        
        if ($remarks) {
            $this->remarks = ($this->remarks ? $this->remarks . "\n" : '') . "Payment remarks: {$remarks}";
        }
        
        // Update status of all salary records
        $this->salaryRecords()->update(['status' => 'paid']);
        
        return $this->save();
    }

    /**
     * Generate bank payment file.
     *
     * @return string|null  Path to generated file
     */
    public function generateBankFile()
    {
        if ($this->status !== 'approved' && $this->status !== 'paid') {
            return null;
        }
        
        // This is a placeholder. The actual implementation would:
        // 1. Get bank account details for all staff
        // 2. Generate file in appropriate format (CSV, XML, etc.)
        // 3. Save file to storage
        
        $fileName = 'payroll_' . $this->payroll_year . '_' . $this->payroll_month . '_' . time() . '.csv';
        $filePath = 'bank_files/' . $fileName;
        
        // Update payroll record
        $this->bank_file_generated = true;
        $this->bank_file_path = $filePath;
        $this->save();
        
        return $filePath;
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'draft':
                return 'badge-secondary';
            case 'processing':
                return 'badge-info';
            case 'approved':
                return 'badge-success';
            case 'paid':
                return 'badge-primary';
            case 'cancelled':
                return 'badge-danger';
            default:
                return 'badge-dark';
        }
    }
}