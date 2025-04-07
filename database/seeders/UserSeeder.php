<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Import the User model
use Illuminate\Support\Facades\Hash; // Import Hash facade

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the user already exists to prevent duplicates if run multiple times
        if (!User::where('email', 'clemie90@gmail.com')->exists()) {
            User::create([
                'name' => 'Clemence Komorerai Matsika',
                'email' => 'clemie90@gmail.com',
                // IMPORTANT: Use Hash::make() for the password!
                'password' => Hash::make('Caution90@#$%'), // Choose a secure password!
                // Add any other required fields for your User model
                // 'email_verified_at' => now(), // Optional: Mark email as verified
            ]);
        }

         // Add more users if needed
         // if (!User::where('email', 'test@example.com')->exists()) {
         //     User::create([
         //         'name' => 'Test User',
         //         'email' => 'test@example.com',
         //         'password' => Hash::make('password'),
         //     ]);
         // }
    }
}