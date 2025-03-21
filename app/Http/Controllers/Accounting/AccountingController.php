<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get summary data for the dashboard
        $currentMonth = now()->format('Y-m');
        
        // Get income and expenses for the current month
        $income = ChartOfAccount::whereHas('accountType', function($query) {
            $query->where('code', 'INCOME');
        })->with(['journalEntries' => function($query) use ($currentMonth) {
            $query->whereHas('journal', function($q) use ($currentMonth) {
                $q->whereRaw("DATE_FORMAT(journal_date, '%Y-%m') = ?", [$currentMonth]);
            });
        }])->get()->sum(function($account) {
            return $account->journalEntries->sum('credit');
        });
        
        $expenses = ChartOfAccount::whereHas('accountType', function($query) {
            $query->where('code', 'EXPENSE');
        })->with(['journalEntries' => function($query) use ($currentMonth) {
            $query->whereHas('journal', function($q) use ($currentMonth) {
                $q->whereRaw("DATE_FORMAT(journal_date, '%Y-%m') = ?", [$currentMonth]);
            });
        }])->get()->sum(function($account) {
            return $account->journalEntries->sum('debit');
        });
        
        // Get outstanding invoices
        $outstandingInvoices = Invoice::whereIn('status', ['draft', 'sent', 'partial'])
            ->sum(DB::raw('total - amount_paid'));
        
        // Get bank account balances
        $bankBalance = ChartOfAccount::where('is_bank_account', true)
            ->get()
            ->sum(function($account) {
                return $account->getCurrentBalance();
            });
        
        // Get recent transactions
        $recentInvoices = Invoice::orderBy('created_at', 'desc')
            ->with('contact')
            ->limit(5)
            ->get();
        
        $recentPayments = Payment::orderBy('created_at', 'desc')
            ->with(['contact', 'invoice'])
            ->limit(5)
            ->get();
            
        return view('accounting.dashboard.index', compact(
            'income',
            'expenses',
            'outstandingInvoices',
            'bankBalance',
            'recentInvoices',
            'recentPayments'
        ));
    }
}