<?php

namespace Database\Seeders;

// Import necessary models or facades if needed elsewhere,
// but specifically for calling seeders, you don't need User here.
// use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Uncomment if needed for specific Laravel versions/features
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This method defines the order in which your seeders will run.
     */
    public function run(): void // Added return type hint void
    {
        $this->call([
            // Keep your existing CoreModuleSeeder - Ensure this runs first if others depend on it.
            CoreModuleSeeder::class,

            // *** ADDED UserSeeder ***
            // Make sure you have created database/seeders/UserSeeder.php
            UserSeeder::class,

            // *** ADDED AccountTypeSeeder ***
            // Make sure you have created database/seeders/AccountTypeSeeder.php
            AccountTypeSeeder::class,

            // Add any other seeders you need here, in the desired order
            // e.g., FeeTypeSeeder::class,
            //      DefaultSettingsSeeder::class,
        ]);
    }
}