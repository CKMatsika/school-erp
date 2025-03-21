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
    
    // Payments
    Route::resource('payments', PaymentsController::class);
    Route::get('payments/{payment}/receipt', [PaymentsController::class, 'receipt'])->name('payments.receipt');
    
    // Journals
    Route::resource('journals', JournalsController::class);
    
    // Fee Structure
    Route::resource('fee-structures', FeeStructureController::class);
    
    // POS
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/sale', [PosController::class, 'sale'])->name('pos.sale');
    Route::post('pos/payment', [PosController::class, 'payment'])->name('pos.payment');
    Route::get('pos/z-reading', [PosController::class, 'zReading'])->name('pos.z-reading');
    
    // Reports
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('reports/income-statement', [ReportsController::class, 'incomeStatement'])->name('reports.income-statement');
    Route::get('reports/balance-sheet', [ReportsController::class, 'balanceSheet'])->name('reports.balance-sheet');
    Route::get('reports/tax', [ReportsController::class, 'tax'])->name('reports.tax');
    Route::get('reports/student-balances', [ReportsController::class, 'studentBalances'])->name('reports.student-balances');
    
    // Notifications
    Route::resource('notifications', NotificationsController::class);
});