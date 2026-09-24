<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
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
        Schema::defaultStringLength(191);
        Vite::prefetch(concurrency: 3);

        // Register Policies
        Gate::policy(\App\Models\Patient::class, \App\Policies\PatientPolicy::class);
        Gate::policy(\App\Models\Appointment::class, \App\Policies\AppointmentPolicy::class);
        Gate::policy(\App\Models\Invoice::class, \App\Policies\InvoicePolicy::class);
        Gate::policy(\App\Models\Visit::class, \App\Policies\VisitPolicy::class);
    }
}
