<?php
// File: test-hostel-models.php
// Place this in your project root and run: php test-hostel-models.php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Hostel Management Models...\n\n";

// Check if files exist
$models = [
    'app/Models/Student/HostelHouse.php',
    'app/Models/Student/HostelRoom.php',
    'app/Models/Student/HostelBed.php',
    'app/Models/Student/HostelAllocation.php',
];

foreach ($models as $model) {
    echo "Checking if file exists: " . $model . " - ";
    echo file_exists(base_path($model)) ? "YES\n" : "NO\n";
}

echo "\n";

// Check if classes exist
$classes = [
    'App\\Models\\Student\\HostelHouse',
    'App\\Models\\Student\\HostelRoom',
    'App\\Models\\Student\\HostelBed',
    'App\\Models\\Student\\HostelAllocation',
];

foreach ($classes as $class) {
    echo "Checking if class exists: " . $class . " - ";
    echo class_exists($class) ? "YES\n" : "NO\n";
}

echo "\n";

// Test database connection
try {
    echo "Testing database connection: ";
    $result = \DB::select('SELECT 1');
    echo "Success\n";
    
    // Check if tables exist
    $tables = [
        'hostel_houses',
        'hostel_rooms',
        'hostel_beds',
        'hostel_allocations',
    ];
    
    echo "\nChecking if tables exist:\n";
    foreach ($tables as $table) {
        echo "Table " . $table . " - ";
        echo \Schema::hasTable($table) ? "YES\n" : "NO\n";
    }
    
} catch (\Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}

echo "\nDone testing.\n";