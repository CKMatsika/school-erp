<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\School; // Adjust namespace if needed
use Illuminate\Support\Facades\Cache; // Optional caching
use Illuminate\Support\Facades\Schema; // To check if table exists
use Illuminate\Support\Facades\Log;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share $currentSchool with specific views or layouts
        // Adjust the view names/patterns as needed!
        View::composer([
            'accounting.invoices.print',    // Example invoice print view
            'accounting.payments.receipt', // Example payment receipt view
            'accounting.pos.receipt',      // Example POS receipt view (if you have one)
            'layouts.print-header',        // Example shared print header partial
            'layouts.report-header',       // Example shared report header partial
            // Add any other view that needs school details
        ], function ($view) {

             // Basic Caching (optional, adjust duration)
             $currentSchool = Cache::remember('current_school_details', now()->addMinutes(60), function () {
                try {
                    // Check if table exists before querying
                    if (Schema::hasTable('schools')) {
                        // Logic to determine the "current" school
                        // Option 1: If only ONE school will ever exist
                        return School::first();

                        // Option 2: If multi-tenant based on user's school_id
                        // if (auth()->check() && auth()->user()->school_id) {
                        //     return School::find(auth()->user()->school_id);
                        // }

                        // Option 3: Get from a setting?
                        // return School::find(Settings::get('active_school_id'));

                    }
                } catch (\Exception $e) {
                     Log::error("Failed to fetch school details for view composer: " . $e->getMessage());
                     return null; // Return null on error
                }
                return null; // Return null if no logic matches
             });

             // Share the variable with the view
             $view->with('currentSchool', $currentSchool);
        });
    }
}