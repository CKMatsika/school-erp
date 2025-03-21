<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Accounting\DashboardController;
use App\Http\Controllers\Accounting\ChartOfAccountsController;
use App\Http\Controllers\Accounting\ContactsController;
use App\Http\Controllers\Accounting\InvoicesController;
use App\Http\Controllers\Accounting\PaymentsController;
use App\Http\Controllers\Accounting\JournalsController;
use App\Http\Controllers\Accounting\FeeStructureController;
use App\Http\Controllers\Accounting\PosController;
use App\Http\Controllers\Accounting\ReportsController;
use App\Http\Controllers\Accounting\NotificationsController;
use App\Http\Controllers\Accounting\StudentLedgerController;
use App\Http\Controllers\Accounting\BulkInvoiceController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\StudentPromotionController;
use App\Http\Controllers\Accounting\BudgetController;
use App\Http\Controllers\Accounting\PaymentPlanController;
use App\Http\Controllers\Accounting\DebtorController;
use App\Http\Controllers\Accounting\CreditorController;
use App\Http\Controllers\Accounting\StudentDebtController;
use App\Http\Controllers\Accounting\SmsController;

Route::middleware(['auth'])->prefix('accounting')->name('accounting.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Chart of Accounts
    Route::resource('accounts', ChartOfAccountsController::class);
    
    // Contacts (Customers, Vendors, Students)
    Route::resource('contacts', ContactsController::class);
    Route::get('contacts/type/{type}', [ContactsController::class, 'indexByType'])->name('contacts.type');
    
    // Invoices
    Route::resource('invoices', InvoicesController::class);
    Route::get('invoices/{invoice}/print', [InvoicesController::class, 'print'])->name('invoices.print');
    Route::get('invoices/{invoice}/email', [InvoicesController::class, 'email'])->name('invoices.email');
    
    // Bulk Invoicing - Corrected routes
    Route::get('/bulk-invoice', [BulkInvoiceController::class, 'index'])->name('bulk-invoice.index');
    Route::post('/bulk-invoice/generate', [BulkInvoiceController::class, 'generate'])->name('bulk-invoice.generate');
    
    // Payments
    Route::resource('payments', PaymentsController::class);
    Route::get('payments/{payment}/receipt', [PaymentsController::class, 'receipt'])->name('payments.receipt');
    
    // Payment Plans
    Route::resource('payment-plans', PaymentPlanController::class);
    Route::get('payment-plans/{id}/schedule', [PaymentPlanController::class, 'viewSchedule'])->name('payment-plans.schedule');
    Route::post('payment-plans/{id}/generate-schedule', [PaymentPlanController::class, 'generateSchedule'])->name('payment-plans.generate-schedule');
    
    // Journals
    Route::resource('journals', JournalsController::class);
    
    // Fee Structure
    Route::resource('fee-structures', FeeStructureController::class);
    
    // POS
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/sale', [PosController::class, 'sale'])->name('pos.sale');
    Route::post('pos/payment', [PosController::class, 'payment'])->name('pos.payment');
    Route::get('pos/z-reading', [PosController::class, 'zReading'])->name('pos.z-reading');
    
    // Budget Management
    Route::resource('budgets', BudgetController::class);
    Route::get('budgets/{id}/items', [BudgetController::class, 'showItems'])->name('budgets.items');
    Route::post('budgets/{id}/items', [BudgetController::class, 'storeItem'])->name('budgets.items.store');
    Route::get('budgets/{id}/approve', [BudgetController::class, 'approve'])->name('budgets.approve');
    Route::get('capex-projects', [BudgetController::class, 'capexIndex'])->name('capex.index');
    Route::get('capex-projects/create', [BudgetController::class, 'capexCreate'])->name('capex.create');
    Route::post('capex-projects', [BudgetController::class, 'capexStore'])->name('capex.store');
    Route::get('capex-projects/{id}', [BudgetController::class, 'capexShow'])->name('capex.show');
    
    // Debtors & Creditors
    Route::get('debtors', [DebtorController::class, 'index'])->name('debtors.index');
    Route::get('debtors/age-analysis', [DebtorController::class, 'ageAnalysis'])->name('debtors.age-analysis');
    Route::get('creditors', [CreditorController::class, 'index'])->name('creditors.index');
    Route::get('creditors/age-analysis', [CreditorController::class, 'ageAnalysis'])->name('creditors.age-analysis');
    Route::get('student-debts', [StudentDebtController::class, 'index'])->name('student-debts.index');
    Route::get('student-debts/age-analysis', [StudentDebtController::class, 'ageAnalysis'])->name('student-debts.age-analysis');
    
    // Reports
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('reports/income-statement', [ReportsController::class, 'incomeStatement'])->name('reports.income-statement');
    Route::get('reports/balance-sheet', [ReportsController::class, 'balanceSheet'])->name('reports.balance-sheet');
    Route::get('reports/tax', [ReportsController::class, 'tax'])->name('reports.tax');
    Route::get('reports/student-balances', [ReportsController::class, 'studentBalances'])->name('reports.student-balances');
    Route::get('reports/budget-vs-actual', [ReportsController::class, 'budgetVsActual'])->name('reports.budget-vs-actual');
    
    // Notifications & SMS
    Route::resource('notifications', NotificationsController::class);
    Route::get('sms/gateways', [SmsController::class, 'gateways'])->name('sms.gateways');
    Route::get('sms/templates', [SmsController::class, 'templates'])->name('sms.templates');
    Route::get('sms/logs', [SmsController::class, 'logs'])->name('sms.logs');
    Route::get('sms/send', [SmsController::class, 'showSendForm'])->name('sms.send');
    Route::post('sms/send', [SmsController::class, 'processSend'])->name('sms.process-send');
    
    // Student Ledger
    Route::get('student-ledger', [StudentLedgerController::class, 'index'])->name('student-ledger.index');
    Route::get('student-ledger/{id}', [StudentLedgerController::class, 'show'])->name('student-ledger.show');
    Route::get('student-ledger/{id}/statement', [StudentLedgerController::class, 'generateStatement'])->name('student-ledger.statement');
});

// Student Management (temporary until student module is built)
Route::middleware(['auth'])->prefix('students')->name('students.')->group(function () {
    // Student Import
    Route::get('/import', [StudentImportController::class, 'index'])->name('import');
    Route::post('/import', [StudentImportController::class, 'import'])->name('import.process');
    Route::get('/import/template', [StudentImportController::class, 'downloadTemplate'])->name('import.template');
    
    // Student Promotion
    Route::get('/promotion', [StudentPromotionController::class, 'index'])->name('promotion');
    Route::post('/promotion', [StudentPromotionController::class, 'promote'])->name('promotion.process');
    
    // Basic student index (for redirects)
    Route::get('/', function() {
        // Temporary redirect until student module is built
        return redirect()->route('accounting.dashboard');
    })->name('index');
});