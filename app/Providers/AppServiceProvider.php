<?php

namespace App\Providers;

// Added Imports for Morph Map
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Accounting\FeeStructureItem; // Make sure this path is correct
use App\Models\Accounting\InventoryItem;    // Make sure this path is correct

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        // Define the Morph Map
        Relation::morphMap([
            'fee_item'       => FeeStructureItem::class,
            'inventory_item' => InventoryItem::class,
            // Add any other models involved in polymorphic relationships here
            // 'invoice' => \App\Models\Accounting\Invoice::class, // Example
            // 'contact' => \App\Models\Accounting\Contact::class, // Example
        ]);

        // Load modules config if the file exists (Keep existing logic)
        if (file_exists(config_path('modules.php'))) {
            $this->mergeConfigFrom(config_path('modules.php'), 'modules');
        }
    }
}