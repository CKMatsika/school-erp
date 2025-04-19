<?php

use Illuminate\Support\Facades\Route;

// --- Core & Accounting Controllers ---
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
use App\Http\Controllers\Accounting\CapexBudgetController;
use App\Http\Controllers\Accounting\PaymentPlanController;
use App\Http\Controllers\Accounting\DebtorController;
use App\Http\Controllers\Accounting\CreditorController;
use App\Http\Controllers\Accounting\StudentDebtController;
use App\Http\Controllers\Accounting\SmsController; // Keep this controller
use App\Http\Controllers\Accounting\SubjectController; // <-- ADD THIS LINE
use App\Http\Controllers\Accounting\AccountTypeController;
use App\Http\Controllers\Accounting\AcademicYearController;
use App\Http\Controllers\Accounting\TermController;
use App\Http\Controllers\Accounting\SchoolClassController;
use App\Http\Controllers\Accounting\PaymentMethodController;
use App\Http\Controllers\Accounting\CashierDashboardController;
use App\Http\Controllers\Accounting\PosSessionController;
use App\Http\Controllers\Accounting\PosTerminalController;
use App\Http\Controllers\Accounting\PosPaymentController;

// --- Banking & Cash Management Controllers ---
use App\Http\Controllers\Accounting\CashbookController;
use App\Http\Controllers\Accounting\BankReconciliationController;
use App\Http\Controllers\Accounting\BankTransferController;
use App\Http\Controllers\Accounting\BankAccountController;

// --- Procurement Controllers ---
use App\Http\Controllers\Accounting\ProcurementController;
use App\Http\Controllers\Accounting\PurchaseRequestController;
use App\Http\Controllers\Accounting\PurchaseOrderController;
use App\Http\Controllers\Accounting\SupplierController;
use App\Http\Controllers\Accounting\ItemCategoryController;
use App\Http\Controllers\Accounting\InventoryItemController;
use App\Http\Controllers\Accounting\GoodsReceiptController;
use App\Http\Controllers\Accounting\ProcurementContractController;
use App\Http\Controllers\Accounting\TenderController;

// --- Human Resources Controllers ---
use App\Http\Controllers\HumanResources\StaffController;
use App\Http\Controllers\HumanResources\AttendanceController;
use App\Http\Controllers\HumanResources\PayrollController;

// --- Dashboard Controllers ---
use App\Http\Controllers\Dashboards\HeadmasterDashboardController;
use App\Http\Controllers\Dashboards\DeputyDashboardController;

// --- Other Controllers ---
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\StudentPromotionController;


/*
|--------------------------------------------------------------------------
| Accounting Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('accounting')->name('accounting.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Chart of Accounts & Types
    Route::resource('accounts', ChartOfAccountsController::class);
    Route::resource('account-types', AccountTypeController::class);
    Route::resource('payment-methods', PaymentMethodController::class);

    // == Contacts ==
    // --- Bulk Import Routes ---
    Route::get('contacts/import/form', [ContactsController::class, 'showImportForm'])->name('contacts.import.form');
    Route::post('contacts/import/handle', [ContactsController::class, 'handleImport'])->name('contacts.import.handle');
    Route::get('contacts/import/template', [ContactsController::class, 'downloadTemplate'])->name('contacts.import.template');
    // --- Standard Contact Routes ---
    Route::get('contacts/{type?}', [ContactsController::class, 'index'])->whereIn('type', ['customer', 'vendor', 'student'])->name('contacts.index');
    Route::resource('contacts', ContactsController::class)->except(['index']);
    // == End Contacts ==

    // Invoices & Payments
    Route::resource('invoices', InvoicesController::class);
    Route::post('invoices/{invoice}/approve', [InvoicesController::class, 'approve'])->name('invoices.approve');
    Route::post('invoices/{invoice}/send', [InvoicesController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/void', [InvoicesController::class, 'voidInvoice'])->name('invoices.void');
    Route::get('invoices/{invoice}/email', [InvoicesController::class, 'emailInvoice'])->name('invoices.email');
    Route::get('invoices/{invoice}/download', [InvoicesController::class, 'downloadPdf'])->name('invoices.download');
    Route::get('invoices/{invoice}/print', [InvoicesController::class, 'printInvoice'])->name('invoices.print');
    Route::get('/bulk-invoice', [BulkInvoiceController::class, 'index'])->name('bulk-invoice.index');
    Route::post('/bulk-invoice/generate', [BulkInvoiceController::class, 'generate'])->name('bulk-invoice.generate');
    Route::resource('payments', PaymentsController::class);
    Route::get('payments/{payment}/download-receipt', [PaymentsController::class, 'downloadReceipt'])->name('payments.download-receipt');
    Route::get('payments/{payment}/receipt', [PaymentsController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments/contact-invoices', [PaymentsController::class, 'getContactInvoices'])->name('payments.contact-invoices');

    // Payment Plans
    Route::resource('payment-plans', PaymentPlanController::class);
    Route::get('payment-plans/{paymentPlan}/schedule', [PaymentPlanController::class, 'viewSchedule'])->name('payment-plans.schedule');
    Route::post('payment-plans/{paymentPlan}/generate-schedule', [PaymentPlanController::class, 'generateSchedule'])->name('payment-plans.generate-schedule');

    // Journals
    Route::resource('journals', JournalsController::class);

    // === Banking & Cash Management ===
    Route::resource('bank-accounts', BankAccountController::class);
    Route::resource('cashbook', CashbookController::class);
    Route::get('cashbook/entries', [CashbookController::class, 'entries'])->name('cashbook.entries');
    Route::post('cashbook/entries', [CashbookController::class, 'storeEntry'])->name('cashbook.entries.store');
    Route::resource('bank-reconciliation', BankReconciliationController::class);
    Route::get('bank-reconciliation/{bank_reconciliation}/match', [BankReconciliationController::class, 'match'])->name('bank-reconciliation.match');
    Route::post('bank-reconciliation/{bank_reconciliation}/confirm', [BankReconciliationController::class, 'confirm'])->name('bank-reconciliation.confirm');
    Route::post('bank-reconciliation/{bank_reconciliation}/import-statement', [BankReconciliationController::class, 'importStatement'])->name('bank-reconciliation.import-statement');
    Route::resource('bank-transfers', BankTransferController::class);
    // === End Banking & Cash Management ===

    // === Procurement & Inventory Management ===
    Route::get('procurement', [ProcurementController::class, 'index'])->name('procurement.index');
    Route::resource('suppliers', SupplierController::class);
    Route::get('suppliers/{supplier}/performance', [SupplierController::class, 'performance'])->name('suppliers.performance');
    Route::resource('item-categories', ItemCategoryController::class);
    Route::resource('inventory-items', InventoryItemController::class);
    Route::get('inventory-items/{inventoryItem}/stock-history', [InventoryItemController::class, 'stockHistory'])->name('inventory-items.stock-history');
    // Purchase Requests
    Route::resource('purchase-requests', PurchaseRequestController::class);
    Route::get('purchase-requests/{purchaseRequest}/items', [PurchaseRequestController::class, 'items'])->name('purchase-requests.items');
    Route::post('purchase-requests/{purchaseRequest}/items', [PurchaseRequestController::class, 'storeItem'])->name('purchase-requests.items.store');
    Route::delete('purchase-requests/{purchaseRequest}/items/{purchaseRequestItem}', [PurchaseRequestController::class, 'destroyItem'])->name('purchase-requests.items.destroy');
    Route::post('purchase-requests/{purchaseRequest}/submit', [PurchaseRequestController::class, 'submit'])->name('purchase-requests.submit');
    Route::post('purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])->name('purchase-requests.approve');
    Route::post('purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])->name('purchase-requests.reject');
    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::get('purchase-orders/{purchaseOrder}/items', [PurchaseOrderController::class, 'items'])->name('purchase-orders.items');
    Route::post('purchase-orders/{purchaseOrder}/items', [PurchaseOrderController::class, 'storeItem'])->name('purchase-orders.items.store');
    Route::delete('purchase-orders/{purchaseOrder}/items/{purchaseOrderItem}', [PurchaseOrderController::class, 'destroyItem'])->name('purchase-orders.items.destroy');
    Route::post('purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])->name('purchase-orders.submit');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchaseOrder}/issue', [PurchaseOrderController::class, 'issue'])->name('purchase-orders.issue');
    Route::get('purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    // Goods Receipt
    Route::resource('goods-receipts', GoodsReceiptController::class);
    Route::get('goods-receipts/{goodsReceipt}/items', [GoodsReceiptController::class, 'items'])->name('goods-receipts.items');
    Route::put('goods-receipts/{goodsReceipt}/items', [GoodsReceiptController::class, 'updateItems'])->name('goods-receipts.items.update');
    Route::post('goods-receipts/{goodsReceipt}/complete', [GoodsReceiptController::class, 'complete'])->name('goods-receipts.complete');
    // Procurement Contracts
    Route::resource('procurement-contracts', ProcurementContractController::class);
    Route::get('procurement-contracts/{procurementContract}/download', [ProcurementContractController::class, 'download'])->name('procurement-contracts.download');
    // Tenders
    Route::resource('tenders', TenderController::class);
    Route::get('tenders/{tender}/bids', [TenderController::class, 'bids'])->name('tenders.bids');
    Route::post('tenders/{tender}/bids', [TenderController::class, 'storeBid'])->name('tenders.bids.store');
    Route::delete('tenders/{tender}/bids/{bid}', [TenderController::class, 'destroyBid'])->name('tenders.bids.destroy');
    Route::post('tenders/{tender}/award', [TenderController::class, 'award'])->name('tenders.award');
    Route::get('tenders/{tender}/download', [TenderController::class, 'download'])->name('tenders.download');
    Route::get('tenders/{tender}/bids/{bid}/download', [TenderController::class, 'downloadBid'])->name('tenders.bid.download');
    // === End Procurement & Inventory Management ===

    // === Setup ===
    Route::resource('academic-years', AcademicYearController::class);
    Route::post('academic-years/{academic_year}/terms', [TermController::class, 'store'])->name('academic-years.terms.store');
    Route::delete('academic-years/{academic_year}/terms/{term}', [TermController::class, 'destroy'])->name('academic-years.terms.destroy');
    Route::resource('classes', SchoolClassController::class);
    Route::resource('fee-structures', FeeStructureController::class);
    Route::post('fee-structures/{fee_structure}/items', [FeeStructureItemController::class, 'store'])->name('fee-structures.items.store');
    Route::delete('fee-structures/{fee_structure}/items/{item}', [FeeStructureItemController::class, 'destroy'])->name('fee-structures.items.destroy');
    Route::resource('subjects', SubjectController::class); // <-- ADD THIS LINE FOR SUBJECTS
    // === End Setup ===

    // POS
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/sale', [PosController::class, 'sale'])->name('pos.sale');
    Route::post('pos/payment', [PosController::class, 'payment'])->name('pos.payment');
    Route::get('pos/z-reading', [PosController::class, 'zReading'])->name('pos.z-reading');

    // === POS Terminals & Sessions ===
    Route::resource('pos/terminals', PosTerminalController::class)->parameters(['terminals' => 'terminal']);
    Route::get('pos/terminals/setup-test', [PosTerminalController::class, 'setupTest'])->name('pos.terminals.setup-test');
    Route::get('pos/sessions', [PosSessionController::class, 'index'])->name('pos.sessions.index');
    Route::get('pos/sessions/create', [PosSessionController::class, 'create'])->name('pos.sessions.create');
    Route::post('pos/sessions/start', [PosSessionController::class, 'startSession'])->name('pos.sessions.start');
    Route::get('pos/sessions/{session}', [PosSessionController::class, 'show'])->name('pos.sessions.show');
    Route::get('pos/sessions/{session}/end', [PosSessionController::class, 'endSession'])->name('pos.sessions.end');
    Route::post('pos/sessions/{session}/close', [PosSessionController::class, 'closeSession'])->name('pos.sessions.close');
    // === POS Payments ===
    Route::get('pos/payments', [PosPaymentController::class, 'index'])->name('pos.payments.index');
    Route::get('pos/payment/form', [PosPaymentController::class, 'showForm'])->name('pos.payment.form');
    Route::get('pos/payment/invoices', [PosPaymentController::class, 'getInvoices'])->name('pos.payment.invoices');
    Route::post('pos/payment/process', [PosPaymentController::class, 'processPayment'])->name('pos.payment.process');

    // === Cashier Dashboard ===
    Route::get('/cashier', [CashierDashboardController::class, 'index'])->name('cashier.dashboard');
    Route::post('/cashier/sessions/start', [CashierDashboardController::class, 'startSession'])->name('cashier.start-session');
    Route::get('/cashier/sessions/{session}/end', [CashierDashboardController::class, 'endSession'])->name('cashier.end-session');
    Route::post('/cashier/sessions/{session}/close', [CashierDashboardController::class, 'closeSession'])->name('cashier.close-session');
    Route::get('/cashier/sessions/{session}/report', [CashierDashboardController::class, 'sessionReport'])->name('cashier.session-report');

    // === Budget Management ===
    Route::resource('budgets', BudgetController::class);
    Route::get('budgets/{budget}/items', [BudgetController::class, 'showItems'])->name('budgets.items');
    Route::post('budgets/{budget}/items', [BudgetController::class, 'storeItem'])->name('budgets.items.store');
    Route::post('budgets/{budget}/approve-status', [BudgetController::class, 'approveStatus'])->name('budgets.approve-status');
    Route::resource('capex-projects', CapexBudgetController::class)->parameters(['capex-projects' => 'capexProject']);
    // === End Budget Management ===

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
    // Add other report routes as needed...

    // === Notifications & SMS (Refactored) ===
    Route::resource('notifications', NotificationsController::class)->only(['index', 'show']);

    // SMS Resourceful Routes (using SmsController methods)
    Route::get('sms-gateways', [SmsController::class, 'gateways'])->name('sms-gateways.index');
    Route::get('sms-gateways/create', [SmsController::class, 'createGateway'])->name('sms-gateways.create');
    Route::post('sms-gateways', [SmsController::class, 'storeGateway'])->name('sms-gateways.store');
    Route::get('sms-gateways/{gateway}/edit', [SmsController::class, 'editGateway'])->name('sms-gateways.edit');
    Route::put('sms-gateways/{gateway}', [SmsController::class, 'updateGateway'])->name('sms-gateways.update');
    Route::delete('sms-gateways/{gateway}', [SmsController::class, 'destroyGateway'])->name('sms-gateways.destroy');

    Route::get('sms-templates', [SmsController::class, 'templates'])->name('sms.templates');
    Route::get('sms-templates/create', [SmsController::class, 'createTemplate'])->name('sms.templates.create');
    Route::post('sms-templates', [SmsController::class, 'storeTemplate'])->name('sms.templates.store');
    Route::get('sms-templates/{template}/edit', [SmsController::class, 'editTemplate'])->name('sms.templates.edit');
    Route::put('sms-templates/{template}', [SmsController::class, 'updateTemplate'])->name('sms.templates.update');
    Route::delete('sms-templates/{template}', [SmsController::class, 'destroyTemplate'])->name('sms.templates.destroy');

    Route::get('sms/logs', [SmsController::class, 'logs'])->name('sms.logs');
    Route::get('sms/send', [SmsController::class, 'showSendForm'])->name('sms.send');
    Route::post('sms/send', [SmsController::class, 'processSend'])->name('sms.process-send');
    // === End Notifications & SMS ===


    // Student Ledger
    Route::get('student-ledger', [StudentLedgerController::class, 'index'])->name('student-ledger.index');
    Route::get('student-ledger/{contact}', [StudentLedgerController::class, 'show'])->name('student-ledger.show');
    Route::get('student-ledger/{contact}/statement', [StudentLedgerController::class, 'generateStatement'])->name('student-ledger.statement');

}); // End Accounting Group

// =============================================
// == Human Resources Module Routes ==
// =============================================
Route::middleware(['auth'])->prefix('hr')->name('hr.')->group(function () {

    // Staff Management
    Route::resource('staff', StaffController::class);
    Route::get('staff/{staff}/assign-subjects', [StaffController::class, 'assignSubjectsForm'])->name('staff.assign-subjects.form');
    Route::post('staff/{staff}/assign-subjects', [StaffController::class, 'syncSubjects'])->name('staff.assign-subjects.sync');
    Route::get('staff/{staff}/assign-classes', [StaffController::class, 'assignClassesForm'])->name('staff.assign-classes.form');
    Route::post('staff/{staff}/assign-classes', [StaffController::class, 'syncClasses'])->name('staff.assign-classes.sync');

    // Attendance Management
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/record', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('attendance/record', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/reports', [AttendanceController::class, 'reports'])->name('attendance.reports');
    Route::get('attendance/reports/generate', [AttendanceController::class, 'generateReport'])->name('attendance.reports.generate');

    // Payroll Management
    Route::prefix('payroll')->name('payroll.')->group(function () {
        // Elements
        Route::get('/elements', [PayrollController::class, 'elementsIndex'])->name('elements.index');
        Route::get('/elements/create', [PayrollController::class, 'elementsCreate'])->name('elements.create');
        Route::post('/elements', [PayrollController::class, 'elementsStore'])->name('elements.store');
        Route::get('/elements/{element}/edit', [PayrollController::class, 'elementsEdit'])->name('elements.edit');
        Route::put('/elements/{element}', [PayrollController::class, 'elementsUpdate'])->name('elements.update');
        Route::delete('/elements/{element}', [PayrollController::class, 'elementsDestroy'])->name('elements.destroy');
        // Assignments
        Route::get('/staff-assignments/{staff}', [PayrollController::class, 'staffAssignments'])->name('assignments.staff');
        Route::post('/staff-assignments/{staff}', [PayrollController::class, 'syncAssignments'])->name('assignments.sync');
        // Processing & Payslips
        Route::get('/process', [PayrollController::class, 'showProcessForm'])->name('process.form');
        Route::post('/process', [PayrollController::class, 'runPayroll'])->name('process.run');
        Route::get('/payslips', [PayrollController::class, 'payslipsIndex'])->name('payslips.index');
        Route::get('/payslips/{payslip}', [PayrollController::class, 'payslipShow'])->name('payslips.show');
        Route::post('/payslips/{payslip}/post', [PayrollController::class, 'postToJournal'])->name('payslips.post');
    });

}); // End HR Group


// =============================================
// == Student Management Routes (Separate) ==
// =============================================
Route::middleware(['auth'])->prefix('students')->name('students.')->group(function () {
    // Student Import
    Route::get('/import', [StudentImportController::class, 'index'])->name('import');
    Route::post('/import', [StudentImportController::class, 'import'])->name('import.process');
    Route::get('/import/template', [StudentImportController::class, 'downloadTemplate'])->name('import.template');

    // Student Promotion
    Route::get('/promotion', [StudentPromotionController::class, 'index'])->name('promotion');
    Route::post('/promotion', [StudentPromotionController::class, 'promote'])->name('promotion.process');

    // Basic student index (consider linking to a real student listing if available)
    Route::get('/', function() { return redirect()->route('accounting.dashboard'); })->name('index');
}); // End Students Group

// =============================================
// == Dashboard Routes ==
// =============================================
Route::middleware(['auth'])->group(function () {
    // Dashboard routes (assuming these are top-level or defined elsewhere)
    Route::get('/headmaster', [HeadmasterDashboardController::class, 'index'])->name('headmaster.dashboard');
    Route::get('/deputy', [DeputyDashboardController::class, 'index'])->name('deputy.dashboard');

    // Add your default dashboard or other top-level authenticated routes here if needed
    // Example: Route::get('/dashboard', function () { ... })->name('dashboard');

});

// Note: Include default Laravel auth routes if needed
// require __DIR__.'/auth.php';