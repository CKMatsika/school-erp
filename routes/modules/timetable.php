<?php

use App\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    // Main timetable routes
    Route::get('/timetables', [TimetableController::class, 'index'])->name('timetables.index');
    
    // Current timetable view
    Route::get('/timetables/current', [TimetableController::class, 'currentTimetable'])->name('timetables.current');
    
    // Periods management
    Route::get('/timetables/periods', [TimetableController::class, 'managePeriods'])->name('timetables.periods');
    Route::get('/timetables/periods/create', [TimetableController::class, 'createPeriod'])->name('timetables.periods.create');
    Route::post('/timetables/periods', [TimetableController::class, 'storePeriod'])->name('timetables.periods.store');
    Route::get('/timetables/periods/{id}/edit', [TimetableController::class, 'editPeriod'])->name('timetables.periods.edit');
    Route::put('/timetables/periods/{id}', [TimetableController::class, 'updatePeriod'])->name('timetables.periods.update');
    Route::delete('/timetables/periods/{id}', [TimetableController::class, 'deletePeriod'])->name('timetables.periods.delete');
    
    // Classes management
    Route::get('/timetables/classes', [TimetableController::class, 'manageClasses'])->name('timetables.classes');
    Route::get('/timetables/classes/create', [TimetableController::class, 'createClass'])->name('timetables.classes.create');
    Route::post('/timetables/classes', [TimetableController::class, 'storeClass'])->name('timetables.classes.store');
    Route::get('/timetables/classes/{id}/edit', [TimetableController::class, 'editClass'])->name('timetables.classes.edit');
    Route::put('/timetables/classes/{id}', [TimetableController::class, 'updateClass'])->name('timetables.classes.update');
    Route::delete('/timetables/classes/{id}', [TimetableController::class, 'deleteClass'])->name('timetables.classes.delete');
    
    // Subjects management
    Route::get('/timetables/subjects', [TimetableController::class, 'manageSubjects'])->name('timetables.subjects');
    Route::get('/timetables/subjects/create', [TimetableController::class, 'createSubject'])->name('timetables.subjects.create');
    Route::post('/timetables/subjects', [TimetableController::class, 'storeSubject'])->name('timetables.subjects.store');
    Route::get('/timetables/subjects/{id}/edit', [TimetableController::class, 'editSubject'])->name('timetables.subjects.edit');
    Route::put('/timetables/subjects/{id}', [TimetableController::class, 'updateSubject'])->name('timetables.subjects.update');
    Route::delete('/timetables/subjects/{id}', [TimetableController::class, 'deleteSubject'])->name('timetables.subjects.delete');
    
    // Teachers management
    Route::get('/timetables/teachers', [TimetableController::class, 'manageTeachers'])->name('timetables.teachers');
    Route::get('/timetables/teachers/create', [TimetableController::class, 'createTeacher'])->name('timetables.teachers.create');
    Route::post('/timetables/teachers', [TimetableController::class, 'storeTeacher'])->name('timetables.teachers.store');
    Route::get('/timetables/teachers/{id}/edit', [TimetableController::class, 'editTeacher'])->name('timetables.teachers.edit');
    Route::put('/timetables/teachers/{id}', [TimetableController::class, 'updateTeacher'])->name('timetables.teachers.update');
    Route::delete('/timetables/teachers/{id}', [TimetableController::class, 'deleteTeacher'])->name('timetables.teachers.delete');

    // Timetable Templates management
    Route::get('/timetables/templates', [TimetableController::class, 'manageTemplates'])->name('timetables.templates');
    Route::get('/timetables/templates/create', [TimetableController::class, 'createTemplate'])->name('timetables.templates.create');
    Route::post('/timetables/templates', [TimetableController::class, 'storeTemplate'])->name('timetables.templates.store');
    Route::get('/timetables/templates/{id}/edit', [TimetableController::class, 'editTemplate'])->name('timetables.templates.edit');
    Route::put('/timetables/templates/{id}', [TimetableController::class, 'updateTemplate'])->name('timetables.templates.update');
    Route::delete('/timetables/templates/{id}', [TimetableController::class, 'deleteTemplate'])->name('timetables.templates.delete');
    Route::post('/timetables/templates/{id}/activate', [TimetableController::class, 'setActiveTemplate'])->name('timetables.templates.activate');

    // Timetable Entry Editor
    Route::get('/timetables/entries/editor/{templateId?}', [TimetableController::class, 'entryEditor'])->name('timetables.entries.editor');
    Route::post('/timetables/entries', [TimetableController::class, 'storeEntry'])->name('timetables.entries.store');
    Route::put('/timetables/entries/{id}', [TimetableController::class, 'updateEntry'])->name('timetables.entries.update');
    Route::delete('/timetables/entries/{id}', [TimetableController::class, 'deleteEntry'])->name('timetables.entries.delete');
});

