<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Services\PaymentSettings;
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
        View::composer('*', function ($view): void {
            $settings = app(PaymentSettings::class)->all();
            $view->with('brandSettings', $settings)->with('paymentSettings', $settings);
        });
        Gate::define('manage-payments', fn ($user) => config('services.claims.admin_email')
            && hash_equals(strtolower((string) config('services.claims.admin_email')), strtolower((string) $user->email)));
    }
}
