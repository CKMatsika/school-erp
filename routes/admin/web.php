<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\HR\StaffController;
use App\Http\Controllers\Admin\HR\RecruitmentController;
use App\Http\Controllers\Admin\HR\PerformanceController;
use App\Http\Controllers\Admin\Finance\PayrollController;
use App\Http\Controllers\Admin\Finance\ExpenseController;
use App\Http\Controllers\Admin\Attendance\TeacherAttendanceController;
use App\Http\Controllers\Admin\Attendance\StaffAttendanceController;
use App\Http\Controllers\Admin\Leave\LeaveApplicationController;
use App\Http\Controllers\Admin\Leave\LeavePolicyController;
use App\Http\Controllers\Admin\Asset\InventoryController;
use App\Http\Controllers\Admin\Asset\MaintenanceController;
use App\Http\Controllers\Admin\Library\BookController;
use App\Http\Controllers\Admin\Library\CirculationController;
use App\Http\Controllers\Admin\Transport\VehicleController;
use App\Http\Controllers\Admin\Transport\RouteController;
use App\Http\Controllers\Admin\Transport\DriverController;
use App\Http\Controllers\Admin\Facility\RoomController;
use App\Http\Controllers\Admin\Facility\EventController;
use App\Http\Controllers\Admin\IT\EquipmentController;
use App\Http\Controllers\Admin\IT\SupportTicketController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // HR Routes
    Route::resource('staff', StaffController::class);
    Route::resource('recruitment', RecruitmentController::class);
    Route::resource('performance', PerformanceController::class);
    
    // Payroll & Finance Routes
    Route::resource('payroll', PayrollController::class);
    Route::resource('expenses', ExpenseController::class);
    
    // Attendance Routes
    Route::resource('teacher-attendance', TeacherAttendanceController::class);
    Route::resource('staff-attendance', StaffAttendanceController::class);
    
    // Leave Management Routes
    Route::resource('leave-applications', LeaveApplicationController::class);
    Route::resource('leave-policies', LeavePolicyController::class);
    
    // Asset Management Routes
    Route::resource('assets', InventoryController::class);
    Route::resource('maintenance', MaintenanceController::class);
    
    // Library Management Routes
    Route::resource('books', BookController::class);
    Route::resource('circulation', CirculationController::class);
    
    // Transport Management Routes
    Route::resource('vehicles', VehicleController::class);
    Route::resource('routes', RouteController::class);
    Route::resource('drivers', DriverController::class);
    
    // Facility Management Routes
    Route::resource('rooms', RoomController::class);
    Route::resource('events', EventController::class);
    
    // IT Management Routes
    Route::resource('it-equipment', EquipmentController::class);
    Route::resource('support-tickets', SupportTicketController::class);
});