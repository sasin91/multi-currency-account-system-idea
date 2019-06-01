<?php

namespace App\Providers;

use App\Billing;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Billing\PaymentMethodManager::class, function ($app) {
           return new Billing\PaymentMethodManager($app);
        });

        $this->app->alias(Billing\PaymentMethod::class, Billing\Contracts\PaymentMethod::class);
        $this->app->alias(Billing\PaymentMethod::class, 'PaymentMethod');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
