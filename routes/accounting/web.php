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
use App\Http\Controllers\Accounting\CapexBudgetController;
use App\Http\Controllers\Accounting\PaymentPlanController;
use App\Http\Controllers\Accounting\DebtorController;
use App\Http\Controllers\Accounting\CreditorController;
use App\Http\Controllers\Accounting\StudentDebtController;
use App\Http\Controllers\Accounting\SmsController;
use App\Http\Controllers\Accounting\AccountTypeController;
use App\Http\Controllers\Accounting\AcademicYearController;
use App\Http\Controllers\Accounting\TermController;
use App\Http\Controllers\Accounting\SchoolClassController;
use App\Http\Controllers\Accounting\PaymentMethodController;
// Banking & Cash Management Controllers
use App\Http\Controllers\Accounting\CashbookController;
use App\Http\Controllers\Accounting\BankReconciliationController;
use App\Http\Controllers\Accounting\BankTransferController;
use App\Http\Controllers\Accounting\BankAccountController;
// Procurement Controllers
use App\Http\Controllers\Accounting\ProcurementController;
use App\Http\Controllers\Accounting\PurchaseRequestController;
use App\Http\Controllers\Accounting\PurchaseOrderController;
use App\Http\Controllers\Accounting\SupplierController;
use App\Http\Controllers\Accounting\ItemCategoryController;
use App\Http\Controllers\Accounting\InventoryItemController;
use App\Http\Controllers\Accounting\GoodsReceiptController;
use App\Http\Controllers\Accounting\ProcurementContractController;
use App\Http\Controllers\Accounting\TenderController;

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
    Route::resource('payment-methods', PaymentMethodController::class);

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

    // === Banking & Cash Management ===
    // Bank Accounts
    Route::resource('bank-accounts', BankAccountController::class);
    // Cashbook
    Route::resource('cashbook', CashbookController::class);
    Route::get('cashbook/entries', [CashbookController::class, 'entries'])->name('cashbook.entries');
    Route::post('cashbook/entries', [CashbookController::class, 'storeEntry'])->name('cashbook.entries.store');
    // Bank Reconciliation
    Route::resource('bank-reconciliation', BankReconciliationController::class);
    Route::get('bank-reconciliation/{id}/match', [BankReconciliationController::class, 'match'])->name('bank-reconciliation.match');
    Route::post('bank-reconciliation/{id}/confirm', [BankReconciliationController::class, 'confirm'])->name('bank-reconciliation.confirm');
    Route::post('bank-reconciliation/{id}/import-statement', [BankReconciliationController::class, 'importStatement'])->name('bank-reconciliation.import-statement');
    // Bank Transfers
    Route::resource('bank-transfers', BankTransferController::class);
    // === End Banking & Cash Management ===

    // === Procurement & Inventory Management ===
    // Procurement Dashboard
    Route::get('procurement', [ProcurementController::class, 'index'])->name('procurement.index');
    
    // Suppliers
    Route::resource('suppliers', SupplierController::class);
    Route::get('suppliers/{supplier}/performance', [SupplierController::class, 'performance'])->name('suppliers.performance');
    
    // Item Categories
    Route::resource('item-categories', ItemCategoryController::class);
    
    // Inventory Items
    Route::resource('inventory-items', InventoryItemController::class);
    Route::get('inventory-items/{item}/stock-history', [InventoryItemController::class, 'stockHistory'])->name('inventory-items.stock-history');
    
    // Purchase Requests
    Route::resource('purchase-requests', PurchaseRequestController::class);
    Route::get('purchase-requests/{purchase_request}/items', [PurchaseRequestController::class, 'items'])->name('purchase-requests.items');
    Route::post('purchase-requests/{purchase_request}/items', [PurchaseRequestController::class, 'storeItem'])->name('purchase-requests.items.store');
    Route::delete('purchase-requests/{purchase_request}/items/{item}', [PurchaseRequestController::class, 'destroyItem'])->name('purchase-requests.items.destroy');
    Route::post('purchase-requests/{purchase_request}/approve', [PurchaseRequestController::class, 'approve'])->name('purchase-requests.approve');
    Route::post('purchase-requests/{purchase_request}/reject', [PurchaseRequestController::class, 'reject'])->name('purchase-requests.reject');
    
    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::get('purchase-orders/{purchase_order}/items', [PurchaseOrderController::class, 'items'])->name('purchase-orders.items');
    Route::post('purchase-orders/{purchase_order}/items', [PurchaseOrderController::class, 'storeItem'])->name('purchase-orders.items.store');
    Route::delete('purchase-orders/{purchase_order}/items/{item}', [PurchaseOrderController::class, 'destroyItem'])->name('purchase-orders.items.destroy');
    Route::post('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchase_order}/issue', [PurchaseOrderController::class, 'issue'])->name('purchase-orders.issue');
    Route::get('purchase-orders/{purchase_order}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    
    // Goods Receipt
    Route::resource('goods-receipts', GoodsReceiptController::class);
    Route::get('goods-receipts/{goods_receipt}/items', [GoodsReceiptController::class, 'items'])->name('goods-receipts.items');
    Route::post('goods-receipts/{goods_receipt}/items', [GoodsReceiptController::class, 'storeItem'])->name('goods-receipts.items.store');
    Route::post('goods-receipts/{goods_receipt}/complete', [GoodsReceiptController::class, 'complete'])->name('goods-receipts.complete');
    
    // Procurement Contracts
    Route::resource('procurement-contracts', ProcurementContractController::class);
    Route::get('procurement-contracts/{procurement_contract}/download', [ProcurementContractController::class, 'download'])->name('procurement-contracts.download');
    
    // Tenders
    Route::resource('tenders', TenderController::class);
    Route::get('tenders/{tender}/bids', [TenderController::class, 'bids'])->name('tenders.bids');
    Route::post('tenders/{tender}/award', [TenderController::class, 'award'])->name('tenders.award');
    Route::get('tenders/{tender}/download', [TenderController::class, 'download'])->name('tenders.download');
    // === End Procurement & Inventory Management ===

    // === Setup (Moved Academic Years, Terms, Classes here) ===
    // Academic Years & Terms
    Route::resource('academic-years', AcademicYearController::class);
    Route::post('academic-years/{academic_year}/terms', [TermController::class, 'store'])->name('academic-years.terms.store');
    Route::delete('academic-years/{academic_year}/terms/{term}', [TermController::class, 'destroy'])->name('academic-years.terms.destroy');
    // Classes
    Route::resource('classes', SchoolClassController::class);
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

    // === Budget Management ===
    // -- Non-CAPEX Budgets --
    Route::resource('budgets', BudgetController::class);
    Route::get('budgets/{id}/items', [BudgetController::class, 'showItems'])->name('budgets.items');
    Route::post('budgets/{id}/items', [BudgetController::class, 'storeItem'])->name('budgets.items.store');
    Route::post('budgets/{id}/approve-status', [BudgetController::class, 'approveStatus'])->name('budgets.approve-status');

    // -- CAPEX Budgets/Projects --
    Route::get('capex-projects', [CapexBudgetController::class, 'index'])->name('capex.index');
    Route::get('capex-projects/create', [CapexBudgetController::class, 'create'])->name('capex.create');
    Route::post('capex-projects', [CapexBudgetController::class, 'store'])->name('capex.store');
    Route::get('capex-projects/{id}', [CapexBudgetController::class, 'show'])->name('capex.show');
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
    // Procurement Reports
    Route::get('reports/procurement-summary', [ReportsController::class, 'procurementSummary'])->name('reports.procurement-summary');
    Route::get('reports/inventory-valuation', [ReportsController::class, 'inventoryValuation'])->name('reports.inventory-valuation');
    Route::get('reports/supplier-spend', [ReportsController::class, 'supplierSpend'])->name('reports.supplier-spend');

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
        return redirect()->route('accounting.dashboard');
    })->name('index');
});