<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use App\Models\HumanResources\Staff;
use App\Models\HumanResources\PayrollElement;
use App\Models\HumanResources\Payslip;
use App\Models\HumanResources\PayslipItem;
use App\Models\Accounting\Journal; // For posting
use App\Models\Accounting\JournalEntry; // For posting
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollController extends Controller
{
    // --- Payroll Elements CRUD (Simplified) ---

    public function elementsIndex()
    {
        // Gate::authorize('viewAny', PayrollElement::class);
        $elements = PayrollElement::orderBy('type')->orderBy('name')->get();
        return view('hr.payroll.elements.index', compact('elements'));
    }

    public function elementsStore(Request $request)
    {
        // Gate::authorize('create', PayrollElement::class);
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:payroll_elements,name',
            'type' => 'required|in:allowance,deduction',
            'calculation_type' => 'required|in:fixed,percentage_basic',
            'default_amount_or_rate' => 'required|numeric|min:0',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);
        $validated['is_taxable'] = $request->boolean('is_taxable');
        $validated['is_active'] = $request->boolean('is_active');

        PayrollElement::create($validated);
        return redirect()->route('hr.payroll.elements.index')->with('success', 'Payroll Element created.');
    }
    // TODO: Add create, edit, update, destroy methods for PayrollElement

    // --- Staff Assignments ---

    public function staffAssignments(Staff $staff)
    {
        // Gate::authorize('assignPayroll', $staff);
        $staff->load('payrollElements');
        $availableElements = PayrollElement::active()->orderBy('type')->orderBy('name')->get();
        $assignedData = $staff->payrollElements->keyBy('id'); // For easy lookup in view

        return view('hr.payroll.assignments.staff', compact('staff', 'availableElements', 'assignedData'));
    }

    public function syncAssignments(Request $request, Staff $staff)
    {
        // Gate::authorize('assignPayroll', $staff);
         $validated = $request->validate([
            'assignments' => 'nullable|array',
            'assignments.*.element_id' => 'required|exists:payroll_elements,id',
            'assignments.*.amount_or_rate' => 'nullable|numeric|min:0', // Custom amount/rate
             // Add start/end date validation if needed
        ]);

        $syncData = [];
         if (isset($validated['assignments'])) {
            foreach($validated['assignments'] as $assignment) {
                // Prepare pivot data - use custom amount/rate if provided, otherwise it uses default from element
                $pivotData = ['amount_or_rate' => $assignment['amount_or_rate'] ?? null];
                // Add start_date, end_date if applicable
                $syncData[$assignment['element_id']] = $pivotData;
            }
        }

        try {
             $staff->payrollElements()->sync($syncData);
             return redirect()->route('hr.payroll.assignments.staff', $staff)->with('success', 'Payroll assignments updated.');
        } catch (\Exception $e) {
            Log::error("Payroll Assignment Sync Failed for Staff ID {$staff->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to update assignments.');
        }
    }


    // --- Payroll Processing ---

    public function showProcessForm()
    {
        // Gate::authorize('processPayroll', Payslip::class); // Permission to run payroll
        $staffGroups = Staff::nonTeaching()->active() // Example: Only non-teaching
                          ->selectRaw('department, COUNT(*) as count')
                          ->whereNotNull('department')
                          ->groupBy('department')
                          ->get();
        return view('hr.payroll.process', compact('staffGroups'));
    }

    public function runPayroll(Request $request)
    {
        // Gate::authorize('processPayroll', Payslip::class);
        $validated = $request->validate([
            'pay_period_month' => 'required|date_format:Y-m',
            'department' => 'nullable|string', // Process specific department or all
        ]);

        $payPeriod = Carbon::parse($validated['pay_period_month'].'-01');
        $startDate = $payPeriod->copy()->startOfMonth();
        $endDate = $payPeriod->copy()->endOfMonth();
        $payPeriodString = $payPeriod->format('Y-m');

        // Get staff to process
        $staffToProcess = Staff::nonTeaching()->active() // Adjust filter as needed
            ->when($request->filled('department'), function($q) use ($request) {
                $q->where('department', $request->department);
            })
            ->with(['payrollElements' => function($q) use ($endDate){ // Get elements active during the period
                $q->where(function($sub) use ($endDate){
                     $sub->whereNull('staff_payroll_elements.start_date')
                         ->orWhere('staff_payroll_elements.start_date', '<=', $endDate);
                  })
                  ->where(function($sub) use ($startDate){
                      $sub->whereNull('staff_payroll_elements.end_date')
                          ->orWhere('staff_payroll_elements.end_date', '>=', $startDate);
                  });
            }])
            ->whereDoesntHave('payslips', function($q) use ($payPeriodString) { // Don't re-process
                 $q->where('pay_period', $payPeriodString);
             })
            ->get();

        if ($staffToProcess->isEmpty()) {
             return back()->with('warning', 'No eligible staff found for processing in this period/department or they have already been processed.');
        }

        $processedCount = 0;
        $errorCount = 0;

        foreach ($staffToProcess as $staff) {
            DB::beginTransaction();
            try {
                 // TODO: Implement detailed calculation logic here
                 $basicSalary = $staff->basic_salary ?? 0;
                 $totalAllowances = 0;
                 $totalDeductions = 0;
                 $payslipItemsData = []; // To store items for payslip

                // Basic Salary Item
                 $payslipItemsData[] = [
                    'payroll_element_id' => null, // Or link to a basic salary element if defined
                    'type' => 'allowance', // Basic is treated as base for allowances
                    'description' => 'Basic Salary',
                    'amount' => $basicSalary
                 ];


                 // Calculate Allowances/Deductions based on assigned elements
                foreach ($staff->payrollElements as $element) {
                    $amount = 0;
                    $calcRate = $element->pivot->amount_or_rate ?? $element->default_amount_or_rate;

                    if ($element->calculation_type === 'percentage_basic') {
                        $amount = $basicSalary * $calcRate; // Assumes rate is decimal e.g., 0.10
                    } else { // fixed
                        $amount = $calcRate;
                    }

                    if ($amount != 0) {
                         $payslipItemsData[] = [
                            'payroll_element_id' => $element->id,
                            'type' => $element->type,
                            'description' => $element->name,
                            'amount' => $amount
                         ];

                        if ($element->type === 'allowance') {
                            $totalAllowances += $amount;
                        } else {
                            $totalDeductions += $amount;
                        }
                    }
                }

                // TODO: Calculate statutory deductions (Tax, Pension, etc.) - VERY COMPLEX
                // This requires specific rules for your country/region
                // Example placeholder:
                // $tax = calculate_tax($basicSalary + $totalAllowances, $staff); // Hypothetical function
                // if ($tax > 0) {
                //     $totalDeductions += $tax;
                //     $payslipItemsData[] = ['payroll_element_id' => /* Tax element ID */, 'type' => 'deduction', 'description' => 'Income Tax (PAYE)', 'amount' => $tax];
                // }


                // Calculate Gross and Net Pay
                 $grossPay = $basicSalary + $totalAllowances;
                 $netPay = $grossPay - $totalDeductions;

                // Create Payslip Header
                $payslip = Payslip::create([
                    'staff_id' => $staff->id,
                    'pay_period' => $payPeriodString,
                    'pay_period_start' => $startDate,
                    'pay_period_end' => $endDate,
                    'basic_salary' => $basicSalary,
                    'total_allowances' => $totalAllowances,
                    'total_deductions' => $totalDeductions,
                    'gross_pay' => $grossPay,
                    'net_pay' => $netPay,
                    'status' => 'processed', // Mark as processed
                    'processed_at' => now(),
                    'processed_by' => Auth::id(),
                ]);

                // Create Payslip Items
                 $payslip->items()->createMany($payslipItemsData);


                DB::commit();
                $processedCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Payroll Processing Failed for Staff ID {$staff->id}, Period {$payPeriodString}: " . $e->getMessage());
                $errorCount++;
                // Maybe store errors per staff?
            }
        } // End foreach loop

         $message = "Payroll processing completed. Processed: {$processedCount}. Errors: {$errorCount}.";
         $level = $errorCount > 0 ? ($processedCount > 0 ? 'warning' : 'error') : 'success';

        return redirect()->route('hr.payroll.payslips.index', ['pay_period' => $payPeriodString])->with($level, $message);
    }

     public function payslipsIndex(Request $request)
     {
        // Gate::authorize('viewAny', Payslip::class);
        $query = Payslip::with('staff:id,first_name,last_name');

        if($request->filled('pay_period')) {
            $query->where('pay_period', $request->pay_period);
        }
        if($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }
        if($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payslips = $query->orderBy('pay_period', 'desc')->orderBy('staff_id')->paginate(30)->withQueryString();
        $staffList = Staff::nonTeaching()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        // Get distinct pay periods for filter
        $payPeriods = Payslip::distinct()->orderBy('pay_period', 'desc')->pluck('pay_period');

        return view('hr.payroll.payslips.index', compact('payslips', 'staffList', 'payPeriods'));
     }

     public function payslipShow(Payslip $payslip)
     {
        // Gate::authorize('view', $payslip);
        $payslip->load('staff', 'items.payrollElement', 'processor', 'journal'); // Eager load details
        return view('hr.payroll.payslips.show', compact('payslip'));
     }

     public function postToJournal(Payslip $payslip)
     {
        // Gate::authorize('postPayroll', Payslip::class); // Permission to post

         if($payslip->status !== 'processed') {
             return back()->with('error', 'Only processed payslips can be posted.');
         }
         if($payslip->journal_id) {
            return back()->with('warning', 'This payslip has already been posted to the journal.');
        }

        // TODO: Implement Accounting Journal Posting Logic
        // Requires mapping payroll elements/totals to specific Chart of Account IDs
        // Example structure:
         DB::beginTransaction();
         try {
             $journalDate = $payslip->pay_period_end; // Post at end of period?
             $description = "Payroll posting for {$payslip->staff->full_name} - Period: {$payslip->pay_period}";

             // Create Journal Header
             $journal = Journal::create([
                 'journal_date' => $journalDate,
                 'reference' => 'PAYSLIP-' . $payslip->id,
                 'description' => $description,
                 'type' => 'payroll', // Add this type if needed
                 'created_by' => Auth::id(),
                 // Add school_id if multi-tenant
             ]);

             $entries = [];

             // 1. Debit Expense Accounts (Basic Salary + Allowances)
             // --- This needs configuration ---
             // Example: Assuming one main Salary Expense Account
              $salaryExpenseAccountId = config('accounting.payroll.salary_expense_account_id'); // Get from config
              if(!$salaryExpenseAccountId) throw new \Exception("Salary Expense Account ID not configured.");
              $entries[] = [
                  'journal_id' => $journal->id,
                  'chart_of_account_id' => $salaryExpenseAccountId,
                  'debit' => $payslip->gross_pay, // Debit total gross pay to expense
                  'credit' => 0,
                  'description' => "Gross Pay: {$payslip->staff->full_name}",
                  // Add school_id
              ];


             // 2. Credit Liability / Bank Account for Net Pay
              // --- Needs configuration ---
              $payrollLiabilityAccountId = config('accounting.payroll.liability_account_id'); // Or Bank Account ID if paying directly
               if(!$payrollLiabilityAccountId) throw new \Exception("Payroll Liability/Bank Account ID not configured.");
               $entries[] = [
                  'journal_id' => $journal->id,
                  'chart_of_account_id' => $payrollLiabilityAccountId,
                  'debit' => 0,
                  'credit' => $payslip->net_pay, // Credit Net Pay payable
                  'description' => "Net Pay Payable: {$payslip->staff->full_name}",
                  // Add school_id
              ];

             // 3. Credit Liability accounts for deductions (e.g., Tax Payable, Pension Payable)
              // --- Needs configuration for each deduction type ---
              // Example: PAYE Tax
              // $taxItem = $payslip->items()->whereHas('payrollElement', fn($q)=>$q->where('name', 'Income Tax (PAYE)'))->first(); // Find tax item
              // if($taxItem && $taxItem->amount > 0) {
              //      $taxPayableAccountId = config('accounting.payroll.tax_payable_account_id');
              //      if(!$taxPayableAccountId) throw new \Exception("Tax Payable Account ID not configured.");
              //      $entries[] = [
              //          'journal_id' => $journal->id,
              //          'chart_of_account_id' => $taxPayableAccountId,
              //          'debit' => 0,
              //          'credit' => $taxItem->amount, // Credit Tax Payable liability
              //          'description' => "PAYE Payable: {$payslip->staff->full_name}",
              //      ];
              // }
             // Add similar entries for other deduction liabilities...


             // Validate debits = credits
             $totalDebits = collect($entries)->sum('debit');
             $totalCredits = collect($entries)->sum('credit');
             if (abs($totalDebits - $totalCredits) > 0.001) { // Check with tolerance
                 throw new \Exception("Journal entries do not balance (Debits: {$totalDebits}, Credits: {$totalCredits}).");
             }

             // Create Journal Entries
             JournalEntry::insert($entries); // Bulk insert

             // Update Payslip status and link to journal
             $payslip->journal_id = $journal->id;
             // Optionally move status to 'paid' if posting implies payment? Or separate step.
             // $payslip->status = 'paid';
             // $payslip->paid_at = now();
             $payslip->save();

             DB::commit();
             return redirect()->route('hr.payroll.payslips.show', $payslip)->with('success', 'Payslip successfully posted to journal.');

         } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Payslip Post to Journal Failed for Payslip ID {$payslip->id}: " . $e->getMessage());
             return back()->with('error', 'Failed to post payslip to journal: ' . $e->getMessage());
         }
     }

}