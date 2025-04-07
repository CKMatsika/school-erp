<?php

use Illuminate\Support\Facades\Route;

// --- Accounting Controllers ---
use App\Http\Controllers\Accounting\DashboardController;
use App\Http\Controllers\Accounting\ChartOfAccountsController;
use App\Http\Controllers\Accounting\ContactsController;
use App\Http\Controllers\Accounting\InvoicesController;
use App\Http\Controllers\Accounting\PaymentsController;
use App\Http\Controllers\Accounting\JournalsController;
use App\Http\Controllers\Accounting\FeeStructureController;
use App\Http\Controllers\Accounting\FeeStructureItemController;
use App\Http\Controllers\Accounting\PosController;
use App\Http\Controllers\Accounting\ReportsController;
use App\Http\Controllers\Accounting\NotificationsController;
use App\Http\Controllers\Accounting\StudentLedgerController;
use App\Http\Controllers\Accounting\BulkInvoiceController;
use App\Http\Controllers\Accounting\BudgetController;
use App\Http\Controllers\Accounting\PaymentPlanController;
use App\Http\Controllers\Accounting\DebtorController;
use App\Http\Controllers\Accounting\CreditorController;
use App\Http\Controllers\Accounting\StudentDebtController;
use App\Http\Controllers\Accounting\SmsController;
use App\Http\Controllers\Accounting\AccountTypeController;
use App\Http\Controllers\Accounting\AcademicYearController; // Controller for Academic Years
use App\Http\Controllers\Accounting\TermController;         // Controller for Terms
use App\Http\Controllers\Accounting\SchoolClassController;   // Controller for Classes

// --- Other Controllers ---
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\StudentPromotionController;


// Apply auth middleware and prefix/name grouping for all accounting routes
Route::middleware(['auth'])->prefix('accounting')->name('accounting.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Chart of Accounts & Types
    Route::resource('accounts', ChartOfAccountsController::class);
    Route::resource('account-types', AccountTypeController::class);
    Route::resource('payment-methods', \App\Http\Controllers\Accounting\PaymentMethodController::class); // Added based on need

    // Contacts
    Route::resource('contacts', ContactsController::class);
    Route::get('contacts/type/{type}', [ContactsController::class, 'indexByType'])->name('contacts.type');

    // Invoices & Payments
    Route::resource('invoices', InvoicesController::class);
    // Invoice Actions
    Route::post('invoices/{invoice}/approve', [InvoicesController::class, 'approve'])->name('invoices.approve');
    Route::post('invoices/{invoice}/send', [InvoicesController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/void', [InvoicesController::class, 'voidInvoice'])->name('invoices.void');
    Route::get('invoices/{invoice}/email', [InvoicesController::class, 'emailInvoice'])->name('invoices.email');
    Route::get('invoices/{invoice}/download', [InvoicesController::class, 'downloadPdf'])->name('invoices.download');
    Route::get('invoices/{invoice}/print', [InvoicesController::class, 'printInvoice'])->name('invoices.print');
    // Bulk Invoicing
    Route::get('/bulk-invoice', [BulkInvoiceController::class, 'index'])->name('bulk-invoice.index');
    Route::post('/bulk-invoice/generate', [BulkInvoiceController::class, 'generate'])->name('bulk-invoice.generate');
    // Payments
    Route::resource('payments', PaymentsController::class);
    Route::get('payments/{payment}/download-receipt', [PaymentsController::class, 'downloadReceipt'])->name('payments.download-receipt');
    Route::get('payments/{payment}/receipt', [PaymentsController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments/contact-invoices', [PaymentsController::class, 'getContactInvoices'])->name('payments.contact-invoices');

    // Payment Plans
    Route::resource('payment-plans', PaymentPlanController::class);
    Route::get('payment-plans/{id}/schedule', [PaymentPlanController::class, 'viewSchedule'])->name('payment-plans.schedule');
    Route::post('payment-plans/{id}/generate-schedule', [PaymentPlanController::class, 'generateSchedule'])->name('payment-plans.generate-schedule');

    // Journals
    Route::resource('journals', JournalsController::class);

    // === Setup (Moved Academic Years, Terms, Classes here) ===
    // Academic Years & Terms
    Route::resource('academic-years', AcademicYearController::class);
    Route::post('academic-years/{academic_year}/terms', [TermController::class, 'store'])->name('academic-years.terms.store');
    Route::delete('academic-years/{academic_year}/terms/{term}', [TermController::class, 'destroy'])->name('academic-years.terms.destroy');
    // Classes
    Route::resource('classes', SchoolClassController::class); // Manages TimetableSchoolClass model
    // Fee Structure & Items
    Route::resource('fee-structures', FeeStructureController::class);
    Route::post('fee-structures/{fee_structure}/items', [FeeStructureItemController::class, 'store'])->name('fee-structures.items.store');
    Route::delete('fee-structures/{fee_structure}/items/{item}', [FeeStructureItemController::class, 'destroy'])->name('fee-structures.items.destroy');
    // === End Setup ===


    // POS
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/sale', [PosController::class, 'sale'])->name('pos.sale');
    Route::post('pos/payment', [PosController::class, 'payment'])->name('pos.payment');
    Route::get('pos/z-reading', [PosController::class, 'zReading'])->name('pos.z-reading');

    // Budget Management
    Route::resource('budgets', BudgetController::class);
    Route::get('budgets/{id}/items', [BudgetController::class, 'showItems'])->name('budgets.items');
    Route::post('budgets/{id}/items', [BudgetController::class, 'storeItem'])->name('budgets.items.store');
    Route::post('budgets/{id}/approve-status', [BudgetController::class, 'approveStatus'])->name('budgets.approve-status');
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

// Student Management (separate prefix and name group - Keep this separate)
Route::middleware(['auth'])->prefix('students')->name('students.')->group(function () {
    // Student Import
    Route::get('/import', [StudentImportController::class, 'index'])->name('import');
    Route::post('/import', [StudentImportController::class, 'import'])->name('import.process');
    Route::get('/import/template', [StudentImportController::class, 'downloadTemplate'])->name('import.template');

    // Student Promotion
    Route::get('/promotion', [StudentPromotionController::class, 'index'])->name('promotion');
    Route::post('/promotion', [StudentPromotionController::class, 'promote'])->name('promotion.process');

    // Basic student index (for redirects or future student list)
    Route::get('/', function() {
        return redirect()->route('accounting.dashboard'); // Keep redirect for now
    })->name('index');

    // Add actual student management routes here later if needed
    // Route::resource('students', App\Http\Controllers\Student\ManagementStudentController::class);
});

// Removed the setup group from inside the students group
// Route::middleware(['auth'])->prefix('setup')->name('setup.')->group(function() { ... }); <-- This was incorrect placement