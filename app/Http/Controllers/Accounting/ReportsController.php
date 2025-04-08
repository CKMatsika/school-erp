<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountType;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Contact;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\Payment;
// --- Add Models needed for Budget vs Actual ---
// use App\Models\Accounting\Budget;
// use App\Models\Accounting\BudgetItem;
// --------------------------------------------
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ReportsController extends Controller
{
    /**
     * Display the reports index page.
     */
    public function index()
    {
        return view('accounting.reports.index');
    }

    /**
     * Generate the income statement report.
     */
    public function incomeStatement(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get all income accounts with transactions in the period
        $incomeAccounts = ChartOfAccount::whereHas('accountType', function($query) {
            $query->where('code', 'INCOME');
        })->with(['journalEntries' => function($query) use ($startDate, $endDate) {
            // Assuming 'journalEntries' relationship links ChartOfAccount.id to JournalEntry.account_id
            // And JournalEntry has a 'journal' relationship based on JournalEntry.journal_id
            $query->whereHas('journal', function($q) use ($startDate, $endDate) {
                $q->whereBetween('journal_date', [$startDate, $endDate]);
            });
        }])->get();

        // Calculate income totals
        $incomeTotals = [];
        $totalIncome = 0;

        foreach ($incomeAccounts as $account) {
            $journalEntries = $account->journalEntries ?? collect();
            // Income increases with credit
            $amount = $journalEntries->sum('credit') - $journalEntries->sum('debit');
            if ($amount != 0) {
                $incomeTotals[$account->id] = [
                    'account' => $account,
                    'amount' => $amount
                ];
                $totalIncome += $amount;
            }
        }

        // Get all expense accounts with transactions in the period
        $expenseAccounts = ChartOfAccount::whereHas('accountType', function($query) {
            $query->whereIn('code', ['EXPENSE', 'COGS']); // Include COGS if applicable
        })->with(['journalEntries' => function($query) use ($startDate, $endDate) {
             $query->whereHas('journal', function($q) use ($startDate, $endDate) {
                $q->whereBetween('journal_date', [$startDate, $endDate]);
            });
        }])->get();

        // Calculate expense totals
        $expenseTotals = [];
        $totalExpenses = 0;

        foreach ($expenseAccounts as $account) {
            $journalEntries = $account->journalEntries ?? collect();
            // Expense increases with debit
            $amount = $journalEntries->sum('debit') - $journalEntries->sum('credit');
            if ($amount != 0) {
                $expenseTotals[$account->id] = [
                    'account' => $account,
                    'amount' => $amount
                ];
                $totalExpenses += $amount;
            }
        }

        // Calculate net income
        $netIncome = $totalIncome - $totalExpenses;

        return view('accounting.reports.income-statement', compact(
            'startDate',
            'endDate',
            'incomeTotals',
            'totalIncome',
            'expenseTotals',
            'totalExpenses',
            'netIncome'
        ));
    }

    /**
     * Generate the balance sheet report.
     */
    public function balanceSheet(Request $request)
    {
        // Define $asOfDate earlier
        $asOfDate = $request->input('as_of_date', Carbon::now()->format('Y-m-d'));

        // Function to get account balances up to a date
        $getAccountBalance = function ($accountId, $endDate) {
            $account = ChartOfAccount::with('accountType')->find($accountId);
            if (!$account) return 0;

            $balance = $account->opening_balance ?? 0;

            $entriesTotal = JournalEntry::where('account_id', $accountId) // Use account_id
                ->whereHas('journal', function($q) use ($endDate) {
                    $q->where('journal_date', '<=', $endDate);
                })
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();

            $totalDebit = $entriesTotal->total_debit ?? 0;
            $totalCredit = $entriesTotal->total_credit ?? 0;

            // Adjust balance based on account type's natural balance
            if (in_array($account->accountType->nature ?? '', ['asset', 'expense'])) {
                $balance += ($totalDebit - $totalCredit);
            } elseif (in_array($account->accountType->nature ?? '', ['liability', 'equity', 'income'])) {
                 $balance += ($totalCredit - $totalDebit);
            } // Add other natures if needed

            return $balance;
        };

        // Get Account IDs by type code
        $assetAccountIds = AccountType::where('code', 'ASSET')->first()?->accounts()->pluck('id') ?? collect();
        $liabilityAccountIds = AccountType::where('code', 'LIABILITY')->first()?->accounts()->pluck('id') ?? collect();
        $equityAccountIds = AccountType::where('code', 'EQUITY')->first()?->accounts()->pluck('id') ?? collect();

        // Calculate totals
        $assetTotals = [];
        $totalAssets = 0;
        foreach ($assetAccountIds as $id) {
            $balance = $getAccountBalance($id, $asOfDate);
            if ($balance != 0) {
                $account = ChartOfAccount::find($id); // Fetch needed details
                $assetTotals[$id] = ['account' => $account, 'balance' => $balance];
                $totalAssets += $balance;
            }
        }

        $liabilityTotals = [];
        $totalLiabilities = 0;
        foreach ($liabilityAccountIds as $id) {
            $balance = $getAccountBalance($id, $asOfDate);
             if ($balance != 0) {
                $account = ChartOfAccount::find($id);
                $liabilityTotals[$id] = ['account' => $account, 'balance' => $balance];
                $totalLiabilities += $balance;
            }
        }

        $equityTotals = [];
        $totalBaseEquity = 0;
         foreach ($equityAccountIds as $id) {
            $balance = $getAccountBalance($id, $asOfDate);
             if ($balance != 0) {
                $account = ChartOfAccount::find($id);
                $equityTotals[$id] = ['account' => $account, 'balance' => $balance];
                $totalBaseEquity += $balance;
            }
        }

        // Calculate retained earnings (net income for the current year up to asOfDate)
        $startOfYear = Carbon::parse($asOfDate)->startOfYear()->format('Y-m-d');
        $retainedEarnings = 0;

        $incomeAccountIds = AccountType::where('code', 'INCOME')->first()?->accounts()->pluck('id') ?? collect();
        $expenseAccountIds = AccountType::whereIn('code', ['EXPENSE', 'COGS'])->get()->flatMap->accounts->pluck('id') ?? collect();

        foreach($incomeAccountIds as $id) {
            $retainedEarnings += $getAccountBalance($id, $asOfDate) - $getAccountBalance($id, Carbon::parse($startOfYear)->subDay()->toDateString()); // Income for period
        }
         foreach($expenseAccountIds as $id) {
            $retainedEarnings -= ($getAccountBalance($id, $asOfDate) - $getAccountBalance($id, Carbon::parse($startOfYear)->subDay()->toDateString())); // Expense for period
        }


        // Add retained earnings to the Equity section for display
        $equityTotals['retained_earnings'] = [
            'account' => (object)['name' => 'Retained Earnings (Current Year)', 'code' => 'RE'],
            'balance' => $retainedEarnings
        ];
        $totalEquity = $totalBaseEquity + $retainedEarnings; // Base equity includes opening balances etc.


        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;


        return view('accounting.reports.balance-sheet', compact(
            'asOfDate',
            'assetTotals',
            'totalAssets',
            'liabilityTotals',
            'totalLiabilities',
            'equityTotals',
            'totalEquity',
            'totalLiabilitiesAndEquity'
        ));
    }

    /**
     * Generate the tax report.
     */
    public function tax(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfYear()->format('Y-m-d'));

        // Ensure Invoice model and relationships ('items.taxRate', 'contact') are correct
        $invoices = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->where('tax_total', '>', 0)
            ->with(['items.taxRate', 'contact']) // Ensure TaxRate relationship exists on InvoiceItem
            ->get();

        $taxesByRate = [];
        $totalTaxable = 0;
        $totalTax = 0;

        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                if ($item->tax_rate_id && $item->tax_amount > 0 && $item->taxRate) { // Check if taxRate loaded
                    $taxRate = $item->taxRate;
                    $taxableAmount = ($item->price * $item->quantity) - ($item->discount ?? 0);

                    if (!isset($taxesByRate[$taxRate->id])) {
                        $taxesByRate[$taxRate->id] = [
                            'rate' => $taxRate,
                            'taxable_amount' => 0,
                            'tax_amount' => 0
                        ];
                    }

                    $taxesByRate[$taxRate->id]['taxable_amount'] += $taxableAmount;
                    $taxesByRate[$taxRate->id]['tax_amount'] += $item->tax_amount;

                    $totalTaxable += $taxableAmount;
                    $totalTax += $item->tax_amount;
                }
            }
        }

        return view('accounting.reports.tax', compact(
            'startDate',
            'endDate',
            'taxesByRate',
            'totalTaxable',
            'totalTax'
        ));
    }

    /**
     * Generate the student balances report.
     */
    public function studentBalances(Request $request)
    {
        $asOfDate = $request->input('as_of_date', Carbon::now()->format('Y-m-d'));

        // Ensure Contact model and relationships ('student', 'invoices', 'payments') are correct
        $students = Contact::where('contact_type', 'student')
            ->with(['student', // Assuming a 'student' relationship exists for student details
                    'invoices' => function($query) use ($asOfDate) {
                        $query->where('issue_date', '<=', $asOfDate)
                              ->where('status', '!=', 'void'); // Exclude void invoices
                    },
                    'payments' => function($query) use ($asOfDate) {
                        $query->where('payment_date', '<=', $asOfDate)
                              ->where('status', 'completed'); // Only count completed payments
                    }])
            ->get();

        $studentBalances = [];
        $totalBalance = 0;

        foreach ($students as $student) {
            $invoiced = $student->invoices ? $student->invoices->sum('total') : 0;
            $paid = $student->payments ? $student->payments->sum('amount') : 0;
            // Consider credits/adjustments if applicable
            $balance = $invoiced - $paid;

             if ($balance != 0) {
                $studentBalances[] = [
                    'student' => $student,
                    'invoiced' => $invoiced,
                    'paid' => $paid,
                    'balance' => $balance
                ];
                $totalBalance += $balance;
            }
        }

        return view('accounting.reports.student-balances', compact(
            'asOfDate',
            'studentBalances',
            'totalBalance'
        ));
    }


    /**
     * Display the Budget vs Actual report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function budgetVsActual(Request $request)
    {
        $school_id = Auth::user()?->school_id;

        // --- 1. Get Filters ---
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $budgetId = $request->input('budget_id');

        // --- TEMP: Fetch available budgets for dropdown ---
        // $availableBudgets = Budget::when($school_id, fn($q, $id) => $q->where('school_id', $id))
        //     ->orderBy('name')
        //     ->get();
         $availableBudgets = []; // Placeholder

        // --- 2. Fetch Budget Data ---
        // ** IMPORTANT: Adapt this based on your Budget/BudgetItem models **
        // Example placeholder query:
        // $budgetQuery = BudgetItem::query()
        //     ->join('budgets', 'budget_items.budget_id', '=', 'budgets.id')
        //     ->join('chart_of_accounts', 'budget_items.account_id', '=', 'chart_of_accounts.id') // Assuming budget_items uses account_id
        //     ->when($school_id, fn($q, $id) => $q->where('budgets.school_id', $id))
        //     ->when($budgetId, fn($q, $id) => $q->where('budgets.id', $id))
        //     // Filter budgets relevant to the period $startDate / $endDate
        //     ->select(
        //         'budget_items.account_id', // Use account_id
        //         'chart_of_accounts.name as account_name',
        //         'chart_of_accounts.code as account_code',
        //         DB::raw('SUM(budget_items.amount) as budgeted_amount')
        //     )
        //     ->groupBy('budget_items.account_id', 'chart_of_accounts.name', 'chart_of_accounts.code');
        // $budgetData = $budgetQuery->get()->keyBy('account_id'); // Key by account_id
         $budgetData = collect([]); // Placeholder

        // --- 3. Fetch Actual Expenditure Data ---
        // Query Journal Entries using account_id and filter date via journal relationship
        $actualsQuery = JournalEntry::join('chart_of_accounts', 'journal_entries.account_id', '=', 'chart_of_accounts.id')
            ->when($school_id, fn($q, $id) => $q->where('journal_entries.school_id', $id))
             // ->whereBetween('journal_entries.entry_date', [$startDate, $endDate]) // REMOVED - Incorrect column
             ->whereHas('journal', function($q) use ($startDate, $endDate) { // UPDATED - Filter on related journal date
                 $q->whereBetween('journal_date', [$startDate, $endDate]); // Assumes 'journal_date' column in 'journals' table
             })
            ->whereHas('account.accountType', function($q) {
                $q->whereIn('code', ['EXPENSE', 'COGS']);
            })
            ->select(
                'journal_entries.account_id',
                DB::raw('SUM(journal_entries.debit) - SUM(journal_entries.credit) as actual_amount')
            )
            ->groupBy('journal_entries.account_id');

        $actualData = $actualsQuery->pluck('actual_amount', 'account_id');


        // --- 4. Combine and Calculate Variance ---
        $reportData = [];
        $allAccountIds = $actualData->keys()->merge($budgetData->keys())->unique();

        foreach ($allAccountIds as $accountId) {
            $budgetInfo = $budgetData->get($accountId);
            $actualAmount = $actualData->get($accountId, 0);
            $budgetedAmount = $budgetInfo?->budgeted_amount ?? 0;

            if ($budgetedAmount != 0 || $actualAmount != 0) {
                 $account = ChartOfAccount::find($accountId);
                 $accountName = $budgetInfo?->account_name ?? $account?->name ?? 'Unknown Account';
                 $accountCode = $budgetInfo?->account_code ?? $account?->code ?? 'N/A';

                $variance = $budgetedAmount - $actualAmount;

                $reportData[] = [
                    'account_id' => $accountId,
                    'account_code' => $accountCode,
                    'account_name' => $accountName,
                    'budgeted' => $budgetedAmount,
                    'actual' => $actualAmount,
                    'variance' => $variance,
                ];
            }
        }

        usort($reportData, function($a, $b) {
            return strcmp($a['account_code'] ?? '', $b['account_code'] ?? '');
        });


        // --- 5. Return the View ---
        return view('accounting.reports.budget-vs-actual', [
            'reportData' => $reportData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedBudgetId' => $budgetId,
            'availableBudgets' => $availableBudgets,
        ]);
    }

} // End of ReportsController class