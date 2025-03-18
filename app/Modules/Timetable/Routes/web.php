// app/Modules/Timetable/Routes/web.php
<?php

use Illuminate\Support\Facades\Route;

// Timetable module routes
Route::middleware(['auth', 'module:timetable'])->group(function () {
    // Timetable routes
    Route::get('timetables', 'TimetableController@index')->name('timetables.index');
    Route::get('timetables/by-teacher/{teacher}', 'TimetableController@byTeacher')->name('timetables.by-teacher');
    Route::get('timetables/by-class/{class}', 'TimetableController@byClass')->name('timetables.by-class');
    Route::get('timetables/by-subject/{subject}', 'TimetableController@bySubject')->name('timetables.by-subject');
    
    // Timetable Builder routes
    Route::get('builder/{template}/{class?}', 'TimetableBuilderController@edit')->name('builder.edit');
    Route::post('builder/entry', 'TimetableBuilderController@createEntry')->name('builder.create-entry');
    Route::put('builder/entry/{entry}', 'TimetableBuilderController@updateEntry')->name('builder.update-entry');
    Route::delete('builder/entry/{entry}', 'TimetableBuilderController@deleteEntry')->name('builder.delete-entry');
});