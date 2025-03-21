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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $query->whereHas('journal', function($q) use ($startDate, $endDate) {
                $q->whereBetween('journal_date', [$startDate, $endDate]);
            });
        }])->get();
        
        // Calculate income totals
        $incomeTotals = [];
        $totalIncome = 0;
        
        foreach ($incomeAccounts as $account) {
            $amount = $account->journalEntries->sum('credit') - $account->journalEntries->sum('debit');
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
            $query->where('code', 'EXPENSE');
        })->with(['journalEntries' => function($query) use ($startDate, $endDate) {
            $query->whereHas('journal', function($q) use ($startDate, $endDate) {
                $q->whereBetween('journal_date', [$startDate, $endDate]);
            });
        }])->get();
        
        // Calculate expense totals
        $expenseTotals = [];
        $totalExpenses = 0;
        
        foreach ($expenseAccounts as $account) {
            $amount = $account->journalEntries->sum('debit') - $account->journalEntries->sum('credit');
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
        $asOfDate = $request->input('as_of_date', Carbon::now()->format('Y-m-d'));
        
        // Get all asset accounts
        $assetAccounts = ChartOfAccount::whereHas('accountType', function($query) {
            $query->where('code', 'ASSET');
        })->with(['journalEntries' => function($query) use ($asOfDate) {
            $query->whereHas('journal', function($q) use ($asOfDate) {
                $q->where('journal_date', '<=', $asOfDate);
            });
        }])->get();
        
        // Calculate asset totals
        $assetTotals = [];
        $totalAssets = 0;
        
        foreach ($assetAccounts as $account) {
            $debits = $account->journalEntries->sum('debit');
            $credits = $account->journalEntries->sum('credit');
            $balance = $account->opening_balance + $debits - $credits;
            
            if ($balance != 0) {
                $assetTotals[$account->id] = [
                    'account' => $account,
                    'balance' => $balance
                ];
                $totalAssets += $balance;
            }
        }
        
        // Get all liability accounts
        $liabilityAccounts = ChartOfAccount::whereHas('accountType', function($query) {
            $query->where('code', 'LIABILITY');
        })->with(['journalEntries' => function($query) use ($asOfDate) {
            $query->whereHas('journal', function($q) use ($asOfDate) {
                $q->where('journal_date', '<=', $asOfDate);
            });
        }])->get();
        
        // Calculate liability totals
        $liabilityTotals = [];
        $totalLiabilities = 0;
        
        foreach ($liabilityAccounts as $account) {
            $debits = $account->journalEntries->sum('debit');
            $credits = $account->journalEntries->sum('credit');
            $balance = $account->opening_balance + $credits - $debits;
            
            if ($balance != 0) {
                $liabilityTotals[$account->id] = [
                    'account' => $account,
                    'balance' => $balance
                ];
                $totalLiabilities += $balance;
            }
        }
        
        // Get all equity accounts
        $equityAccounts = ChartOfAccount::whereHas('accountType', function($query) {
            $query->where('code', 'EQUITY');
        })->with(['journalEntries' => function($query) use ($asOfDate) {
            $query->whereHas('journal', function($q) use ($asOfDate) {
                $q->where('journal_date', '<=', $asOfDate);
            });
        }])->get();
        
        // Calculate equity totals
        $equityTotals = [];
        $totalEquity = 0;
        
        foreach ($equityAccounts as $account) {
            $debits = $account->journalEntries->sum('debit');
            $credits = $account->journalEntries->sum('credit');
            $balance = $account->opening_balance + $credits - $debits;
            
            if ($balance != 0) {
                $equityTotals[$account->id] = [
                    'account' => $account,
                    'balance' => $balance
                ];
                $totalEquity += $balance;
            }
        }
        
        // Calculate retained earnings (net income)
        // This is a simplified calculation; in a real system, you would track retained earnings properly
        $startOfYear = Carbon::parse($asOfDate)->startOfYear()->format('Y-m-d');
        
        $incomeTotal = ChartOfAccount::whereHas('accountType', function($query) {
            $query->where('code', 'INCOME');
        })->with(['journalEntries' => function($query) use ($startOfYear, $asOfDate) {
            $query->whereHas('journal', function($q) use ($startOfYear, $asOfDate) {
                $q->whereBetween('journal_date', [$startOfYear, $asOfDate]);
            });
        }])->get()->sum(function($account) {
            return $account->journalEntries->sum('credit') - $account->journalEntries->sum('debit');
        });
        
        $expenseTotal = ChartOfAccount::whereHas('accountType', function($query) {
            $query->where('code', 'EXPENSE');
        })->with(['journalEntries' => function($query) use ($startOfYear, $asOfDate) {
            $query->whereHas('journal', function($q) use ($startOfYear, $asOfDate) {
                $q->whereBetween('journal_date', [$startOfYear, $asOfDate]);
            });
        }])->get()->sum(function($account) {
            return $account->journalEntries->sum('debit') - $account->journalEntries->sum('credit');
        });
        
        $retainedEarnings = $incomeTotal - $expenseTotal;
        $totalEquity += $retainedEarnings;
        
        // Calculate total liabilities and equity
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        
        return view('accounting.reports.balance-sheet', compact(
            'asOfDate',
            'assetTotals',
            'totalAssets',
            'liabilityTotals',
            'totalLiabilities',
            'equityTotals',
            'totalEquity',
            'retainedEarnings',
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
        
        // Get all invoices with taxes in the period
        $invoices = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->where('tax_total', '>', 0)
            ->with(['items.taxRate', 'contact'])
            ->get();
            
        // Group taxes by rate
        $taxesByRate = [];
        $totalTaxable = 0;
        $totalTax = 0;
        
        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                if ($item->tax_rate_id && $item->tax_amount > 0) {
                    $taxRate = $item->taxRate;
                    $taxableAmount = $item->price * $item->quantity - $item->discount;
                    
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
        
        // Get all student contacts
        $students = Contact::where('contact_type', 'student')
            ->with(['student', 'invoices' => function($query) use ($asOfDate) {
                $query->where('issue_date', '<=', $asOfDate);
            }, 'payments' => function($query) use ($asOfDate) {
                $query->where('payment_date', '<=', $asOfDate);
            }])
            ->get();
            
        // Calculate balances
        $studentBalances = [];
        $totalBalance = 0;
        
        foreach ($students as $student) {
            $invoiced = $student->invoices->sum('total');
            $paid = $student->payments->where('status', 'completed')->sum('amount');
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
}