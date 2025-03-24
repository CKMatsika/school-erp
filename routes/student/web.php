<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\ManagementStudentController;
use App\Http\Controllers\Student\GuardianController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\Student\ApplicationController;
use App\Http\Controllers\Student\AcademicYearController;
use App\Http\Controllers\Student\ClassPromotionController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\ApplicationWorkflowController;
use App\Http\Controllers\Student\DocumentController;
use App\Http\Controllers\Student\StudentSearchController;

/*
|--------------------------------------------------------------------------
| Student Module Routes
|--------------------------------------------------------------------------
|
| Routes for the Student Management module
|
*/

Route::group(['middleware' => ['auth'], 'prefix' => 'student', 'as' => 'student.'], function () {
    // Dashboard Routes
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/statistics', [StudentDashboardController::class, 'statistics'])->name('dashboard.statistics');
    Route::get('/search', [StudentDashboardController::class, 'search'])->name('search');
    Route::get('/export', [StudentDashboardController::class, 'export'])->name('export');
    
    // Student Routes
    Route::resource('students', ManagementStudentController::class);
    Route::get('/students/export/{format?}', [ManagementStudentController::class, 'export'])->name('students.export');
    
    // Guardian Routes
    Route::resource('guardians', GuardianController::class);
    Route::post('/guardians/add-to-student/{student}', [GuardianController::class, 'addToStudent'])->name('guardians.addToStudent');
    
    // Enrollment Routes
    Route::resource('enrollments', EnrollmentController::class)->except(['show', 'destroy']);
    Route::get('/enrollments/academic-year/{academicYear}', [EnrollmentController::class, 'showByYear'])->name('enrollments.showByYear');
    Route::get('/enrollments/export/{format?}', [EnrollmentController::class, 'export'])->name('enrollments.export');
    
    // Application Routes
    Route::resource('applications', ApplicationController::class);
    Route::get('/applications/export/{format?}', [ApplicationController::class, 'export'])->name('applications.export');
    Route::get('/applications/{application}/enroll', [ApplicationController::class, 'enroll'])->name('applications.enroll');
    Route::post('/applications/{application}/update-status', [ApplicationController::class, 'updateStatus'])->name('applications.updateStatus');
    
    // Academic Year Routes
    Route::resource('academic-years', AcademicYearController::class);
    Route::post('/academic-years/terms/{term}/set-current', [AcademicYearController::class, 'setCurrentTerm'])->name('academic-years.set-current-term');
    
    // Class Promotion Routes
    Route::get('/promotions', [ClassPromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/preview', [ClassPromotionController::class, 'preview'])->name('promotions.preview');
    Route::post('/promotions/promote', [ClassPromotionController::class, 'promote'])->name('promotions.promote');
    Route::get('/promotions/graduate', [ClassPromotionController::class, 'graduate'])->name('promotions.graduate');
    Route::get('/promotions/preview-graduation', [ClassPromotionController::class, 'previewGraduation'])->name('promotions.previewGraduation');
    Route::post('/promotions/process-graduation', [ClassPromotionController::class, 'processGraduation'])->name('promotions.processGraduation');
    
    // Application Workflow Routes
    Route::get('/application-workflow', [ApplicationWorkflowController::class, 'index'])->name('application-workflow.index');
    Route::get('/application-workflow/{application}/review', [ApplicationWorkflowController::class, 'review'])->name('application-workflow.review');
    Route::post('/application-workflow/{application}/update-status', [ApplicationWorkflowController::class, 'updateStatus'])->name('application-workflow.updateStatus');
    Route::post('/application-workflow/{application}/schedule-interview', [ApplicationWorkflowController::class, 'scheduleInterview'])->name('application-workflow.scheduleInterview');
    Route::post('/application-workflow/{application}/request-documents', [ApplicationWorkflowController::class, 'requestDocuments'])->name('application-workflow.requestDocuments');
    Route::post('/application-workflow/bulk-update', [ApplicationWorkflowController::class, 'bulkUpdate'])->name('application-workflow.bulkUpdate');
    Route::get('/application-workflow/{application}/enroll', [ApplicationWorkflowController::class, 'enroll'])->name('application-workflow.enroll');
    
    // Document Routes
    Route::resource('documents', DocumentController::class)->except(['destroy']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{document}/verify', [DocumentController::class, 'verify'])->name('documents.verify');
    Route::get('/documents-types', [DocumentController::class, 'types'])->name('documents.types');
    Route::post('/documents-types', [DocumentController::class, 'storeType'])->name('documents.storeType');
    Route::put('/documents-types/{documentType}', [DocumentController::class, 'updateType'])->name('documents.updateType');
    Route::delete('/documents-types/{documentType}', [DocumentController::class, 'destroyType'])->name('documents.destroyType');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
});