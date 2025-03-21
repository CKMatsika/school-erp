<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the accounting dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get current month for financial summary
        $currentMonth = now()->format('Y-m');
        
        // Calculate total income for current month
        $income = $this->calculateIncome($currentMonth);
        
        // Calculate total expenses for current month
        $expenses = $this->calculateExpenses($currentMonth);
        
        // Get outstanding invoices
        $outstandingInvoices = $this->getOutstandingInvoices();
        
        // Get bank account balances
        $bankBalance = $this->getBankBalance();
        
        // Get recent transactions
        $recentInvoices = $this->getRecentInvoices();
        $recentPayments = $this->getRecentPayments();
            
        return view('accounting.dashboard.index', compact(
            'income',
            'expenses',
            'outstandingInvoices',
            'bankBalance',
            'recentInvoices',
            'recentPayments'
        ));
    }

    /**
     * Calculate the total income for a given month.
     *
     * @param string $month The month in Y-m format
     * @return float
     */
    private function calculateIncome($month)
    {
        try {
            // Look for accounts with type 'INCOME'
            return ChartOfAccount::whereHas('accountType', function($query) {
                $query->where('code', 'INCOME');
            })->with(['journalEntries' => function($query) use ($month) {
                $query->whereHas('journal', function($q) use ($month) {
                    $q->whereRaw("DATE_FORMAT(journal_date, '%Y-%m') = ?", [$month]);
                });
            }])->get()->sum(function($account) {
                return $account->journalEntries->sum('credit') - $account->journalEntries->sum('debit');
            });
        } catch (\Exception $e) {
            // In case there's no account type or journal entries yet
            return 0;
        }
    }

    /**
     * Calculate the total expenses for a given month.
     *
     * @param string $month The month in Y-m format
     * @return float
     */
    private function calculateExpenses($month)
    {
        try {
            // Look for accounts with type 'EXPENSE'
            return ChartOfAccount::whereHas('accountType', function($query) {
                $query->where('code', 'EXPENSE');
            })->with(['journalEntries' => function($query) use ($month) {
                $query->whereHas('journal', function($q) use ($month) {
                    $q->whereRaw("DATE_FORMAT(journal_date, '%Y-%m') = ?", [$month]);
                });
            }])->get()->sum(function($account) {
                return $account->journalEntries->sum('debit') - $account->journalEntries->sum('credit');
            });
        } catch (\Exception $e) {
            // In case there's no account type or journal entries yet
            return 0;
        }
    }

    /**
     * Get the total amount of outstanding invoices.
     *
     * @return float
     */
    private function getOutstandingInvoices()
    {
        return Invoice::whereIn('status', ['draft', 'sent', 'partial'])
            ->sum(DB::raw('total - amount_paid'));
    }

    /**
     * Get the total balance of all bank accounts.
     *
     * @return float
     */
    private function getBankBalance()
    {
        try {
            return ChartOfAccount::where('is_bank_account', true)
                ->get()
                ->sum(function($account) {
                    return $account->getCurrentBalance();
                });
        } catch (\Exception $e) {
            // In case there are no bank accounts yet
            return 0;
        }
    }

    /**
     * Get the most recent invoices.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentInvoices($limit = 5)
    {
        return Invoice::with('contact')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the most recent payments.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentPayments($limit = 5)
    {
        return Payment::with(['contact', 'invoice', 'paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}