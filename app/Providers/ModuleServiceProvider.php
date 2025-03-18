<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('module', function ($app) {
            return new \App\Services\ModuleService();
        });
    }

    public function boot()
    {
        // We'll register module routes, views, etc. here in the future
    }
}