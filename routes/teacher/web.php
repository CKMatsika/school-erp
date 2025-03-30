<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherProfileController;
use App\Http\Controllers\Teacher\SchemeOfWorkController;
use App\Http\Controllers\Teacher\LessonPlanController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\ContentController;
use App\Http\Controllers\Teacher\OnlineClassController;
use App\Http\Controllers\Teacher\MarkingSchemeController;

Route::group(['middleware' => ['auth'], 'prefix' => 'teacher', 'as' => 'teacher.'], function () {
    // Dashboard
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    
    // Teacher Profile
    Route::resource('profile', TeacherProfileController::class);
    
    // Schemes of Work
    Route::resource('schemes', SchemeOfWorkController::class);
    Route::post('/schemes/{scheme}/submit-for-approval', [SchemeOfWorkController::class, 'submitForApproval'])->name('schemes.submit-for-approval');
    Route::get('/schemes/{scheme}/approval-status', [SchemeOfWorkController::class, 'approvalStatus'])->name('schemes.approval-status');
    
    // Lesson Plans
    Route::resource('lessons', LessonPlanController::class);
    Route::post('/lessons/{lesson}/submit-for-approval', [LessonPlanController::class, 'submitForApproval'])->name('lessons.submit-for-approval');
    
    // Assignments
    Route::resource('assignments', AssignmentController::class);
    Route::get('/assignments/{assignment}/submissions', [AssignmentController::class, 'viewSubmissions'])->name('assignments.submissions');
    Route::get('/assignments/{assignment}/submissions/{submission}', [AssignmentController::class, 'viewSubmission'])->name('assignments.submission');
    Route::post('/assignments/{assignment}/submissions/{submission}/grade', [AssignmentController::class, 'gradeSubmission'])->name('assignments.grade');
    
    // Content
    Route::resource('contents', ContentController::class);
    Route::post('/contents/{content}/publish', [ContentController::class, 'publish'])->name('contents.publish');
    
    // Online Classes
    Route::resource('online-classes', OnlineClassController::class);
    Route::get('/online-classes/{class}/join', [OnlineClassController::class, 'join'])->name('online-classes.join');
    Route::post('/online-classes/{class}/end', [OnlineClassController::class, 'end'])->name('online-classes.end');
    
    // Marking Schemes
    Route::resource('marking-schemes', MarkingSchemeController::class);
    Route::post('/marking-schemes/{scheme}/apply/{submission}', [MarkingSchemeController::class, 'applyToSubmission'])->name('marking-schemes.apply');
});