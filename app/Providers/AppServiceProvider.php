<?php

namespace App\Providers;

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
        // Load modules config if the file exists
        if (file_exists(config_path('modules.php'))) {
            $this->mergeConfigFrom(config_path('modules.php'), 'modules');
        }
    }
}