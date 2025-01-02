<?php

namespace Webkul\Stripe\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class StripeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/shop-routes.php');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'stripe');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'stripe');

        Event::listen('sales.order.cancel.before', 'Webkul\Stripe\Http\Controllers\PaymentController@cancel');
        Event::listen('sales.refund.save.after', 'Webkul\Stripe\Http\Controllers\PaymentController@refund');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/paymentmethods.php', 'payment_methods'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/system.php', 'core'
        );
    }
}
