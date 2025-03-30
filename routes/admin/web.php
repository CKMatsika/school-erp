// In routes/web/admin.php (create this file)
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', 'Admin\AdminDashboardController@index')->name('dashboard');
    
    // HR Routes
    Route::resource('staff', 'Admin\HR\StaffController');
    Route::resource('recruitment', 'Admin\HR\RecruitmentController');
    Route::resource('performance', 'Admin\HR\PerformanceController');
    
    // Payroll & Finance Routes
    Route::resource('payroll', 'Admin\Finance\PayrollController');
    Route::resource('expenses', 'Admin\Finance\ExpenseController');
    
    // Attendance Routes
    Route::resource('teacher-attendance', 'Admin\Attendance\TeacherAttendanceController');
    Route::resource('staff-attendance', 'Admin\Attendance\StaffAttendanceController');
    
    // Leave Management Routes
    Route::resource('leave-applications', 'Admin\Leave\LeaveApplicationController');
    Route::resource('leave-policies', 'Admin\Leave\LeavePolicyController');
    
    // Asset Management Routes
    Route::resource('assets', 'Admin\Asset\InventoryController');
    Route::resource('maintenance', 'Admin\Asset\MaintenanceController');
    
    // Library Management Routes
    Route::resource('books', 'Admin\Library\BookController');
    Route::resource('circulation', 'Admin\Library\CirculationController');
    
    // Transport Management Routes
    Route::resource('vehicles', 'Admin\Transport\VehicleController');
    Route::resource('routes', 'Admin\Transport\RouteController');
    Route::resource('drivers', 'Admin\Transport\DriverController');
    
    // Facility Management Routes
    Route::resource('rooms', 'Admin\Facility\RoomController');
    Route::resource('events', 'Admin\Facility\EventController');
    
    // IT Management Routes
    Route::resource('it-equipment', 'Admin\IT\EquipmentController');
    Route::resource('support-tickets', 'Admin\IT\SupportTicketController');
});