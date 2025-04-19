<?php

namespace App\Providers;

// Import necessary classes at the top
use App\Models\Accounting\SmsGateway; // <-- Import your SmsGateway model
use App\Policies\SmsGatewayPolicy;    // <-- Import the SmsGatewayPolicy you created

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate; // Keep Gate facade if you use Gates elsewhere

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Default example
        SmsGateway::class => SmsGatewayPolicy::class,     // <-- Add this line to register your policy

        // Add other policies here as needed, for example:
        // \App\Models\Accounting\Invoice::class => \App\Policies\InvoicePolicy::class,
        // \App\Models\Accounting\Contact::class => \App\Policies\ContactPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // This line automatically discovers and registers policies based on the $policies array
        $this->registerPolicies();

        // You can define Gates here if needed, but Policies handle model authorization
        // Gate::define('view-dashboard', function ($user) { ... });
    }
}