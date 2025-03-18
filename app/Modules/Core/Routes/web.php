// app/Modules/Core/Routes/web.php
<?php

use Illuminate\Support\Facades\Route;

// Core module routes
Route::middleware(['auth'])->group(function () {
    // School routes
    Route::resource('schools', 'SchoolController');
    
    // User routes
    Route::resource('users', 'UserController');
    Route::post('users/{user}/toggle-status', 'UserController@toggleStatus')->name('users.toggle-status');
    
    // Role routes
    Route::resource('roles', 'RoleController');
    
    // Module management routes
    Route::get('modules', 'ModuleController@index')->name('modules.index');
    Route::post('modules/{moduleKey}/toggle', 'ModuleController@toggle')->name('modules.toggle');
});