<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\TimetableTemplateController;
use App\Http\Controllers\TimetablePeriodController;
use App\Http\Controllers\TimetableClassController;
use App\Http\Controllers\TimetableSubjectController;
use App\Http\Controllers\TimetableTeacherController;
use App\Http\Controllers\TimetableEntryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\DashboardController;

// Dashboard route
Route::get('/dashboard', function () {
    $user = auth()->user();
    $school = null;
    // If your user model has a relationship with school, you might get it like this:
    // if ($user->hasSchool()) {
    //     $school = $user->school;
    // }
    
    return view('dashboard', compact('user', 'school'));
})->middleware(['auth'])->name('dashboard');

// Auth middleware group - All routes requiring authentication
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });
    
    // Role Management
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
    
    // Module Management
    Route::prefix('modules')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('modules.index');
        Route::get('/create', [ModuleController::class, 'create'])->name('modules.create');
        Route::post('/', [ModuleController::class, 'store'])->name('modules.store');
        Route::get('/{module}', [ModuleController::class, 'show'])->name('modules.show');
        Route::get('/{module}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
        Route::put('/{module}', [ModuleController::class, 'update'])->name('modules.update');
        Route::delete('/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');
        // Add the missing toggle route
        Route::post('/{module}/toggle', [ModuleController::class, 'toggle'])->name('modules.toggle');
    });
    
    // School Management
    Route::prefix('schools')->group(function () {
        Route::get('/', [SchoolController::class, 'index'])->name('schools.index');
        Route::get('/create', [SchoolController::class, 'create'])->name('schools.create');
        Route::post('/', [SchoolController::class, 'store'])->name('schools.store');
        Route::get('/{id}', [SchoolController::class, 'show'])->name('schools.show');
        Route::get('/{id}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
        Route::put('/{id}', [SchoolController::class, 'update'])->name('schools.update');
        Route::delete('/{id}', [SchoolController::class, 'destroy'])->name('schools.destroy');
    });

    // Timetable module routes
    // Main timetable routes
    Route::get('/timetables', [TimetableController::class, 'index'])->name('timetables.index');
    Route::get('/timetables/current', [TimetableController::class, 'currentTimetable'])->name('timetables.current');

    // Timetable Templates
    Route::prefix('timetables/templates')->group(function () {
        Route::get('/', [TimetableTemplateController::class, 'index'])->name('timetables.templates');
        Route::get('/create', [TimetableTemplateController::class, 'create'])->name('timetables.templates.create');
        Route::post('/', [TimetableTemplateController::class, 'store'])->name('timetables.templates.store');
        Route::get('/{id}/edit', [TimetableTemplateController::class, 'edit'])->name('timetables.templates.edit');
        Route::put('/{id}', [TimetableTemplateController::class, 'update'])->name('timetables.templates.update');
        Route::delete('/{id}', [TimetableTemplateController::class, 'destroy'])->name('timetables.templates.destroy');
        Route::post('/{id}/active', [TimetableTemplateController::class, 'setActive'])->name('timetables.templates.active');
    });

    // Timetable Periods
    Route::prefix('timetables/periods')->group(function () {
        Route::get('/', [TimetablePeriodController::class, 'index'])->name('timetables.periods');
        Route::get('/create', [TimetablePeriodController::class, 'create'])->name('timetables.periods.create');
        Route::post('/', [TimetablePeriodController::class, 'store'])->name('timetables.periods.store');
        Route::get('/{id}/edit', [TimetablePeriodController::class, 'edit'])->name('timetables.periods.edit');
        Route::put('/{id}', [TimetablePeriodController::class, 'update'])->name('timetables.periods.update');
        Route::delete('/{id}', [TimetablePeriodController::class, 'destroy'])->name('timetables.periods.destroy');
    });

    // Timetable Classes
    Route::prefix('timetables/classes')->group(function () {
        Route::get('/', [TimetableClassController::class, 'index'])->name('timetables.classes');
        Route::get('/create', [TimetableClassController::class, 'create'])->name('timetables.classes.create');
        Route::post('/', [TimetableClassController::class, 'store'])->name('timetables.classes.store');
        Route::get('/{id}/edit', [TimetableClassController::class, 'edit'])->name('timetables.classes.edit');
        Route::put('/{id}', [TimetableClassController::class, 'update'])->name('timetables.classes.update');
        Route::delete('/{id}', [TimetableClassController::class, 'destroy'])->name('timetables.classes.destroy');
    });

    // Timetable Subjects
    Route::prefix('timetables/subjects')->group(function () {
        Route::get('/', [TimetableSubjectController::class, 'index'])->name('timetables.subjects');
        Route::get('/create', [TimetableSubjectController::class, 'create'])->name('timetables.subjects.create');
        Route::post('/', [TimetableSubjectController::class, 'store'])->name('timetables.subjects.store');
        Route::get('/{id}/edit', [TimetableSubjectController::class, 'edit'])->name('timetables.subjects.edit');
        Route::put('/{id}', [TimetableSubjectController::class, 'update'])->name('timetables.subjects.update');
        Route::delete('/{id}', [TimetableSubjectController::class, 'destroy'])->name('timetables.subjects.destroy');
    });

    // Timetable Teachers
    Route::prefix('timetables/teachers')->group(function () {
        Route::get('/', [TimetableTeacherController::class, 'index'])->name('timetables.teachers');
        Route::get('/create', [TimetableTeacherController::class, 'create'])->name('timetables.teachers.create');
        Route::post('/', [TimetableTeacherController::class, 'store'])->name('timetables.teachers.store');
        Route::get('/{id}/edit', [TimetableTeacherController::class, 'edit'])->name('timetables.teachers.edit');
        Route::put('/{id}', [TimetableTeacherController::class, 'update'])->name('timetables.teachers.update');
        Route::delete('/{id}', [TimetableTeacherController::class, 'destroy'])->name('timetables.teachers.destroy');
    });

    // Timetable Entries
    Route::prefix('timetables/entries')->group(function () {
        Route::get('/editor/{templateId?}', [TimetableEntryController::class, 'editor'])->name('timetables.entries.editor');
        Route::post('/', [TimetableEntryController::class, 'store'])->name('timetables.entries.store');
        Route::put('/{id}', [TimetableEntryController::class, 'update'])->name('timetables.entries.update');
        Route::delete('/{id}', [TimetableEntryController::class, 'destroy'])->name('timetables.entries.destroy');
    });
    
    // Dashboard Controller Routes (if needed)
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
});

// Include Breeze auth routes
require __DIR__.'/auth.php';