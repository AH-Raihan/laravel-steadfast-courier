<?php

namespace AhRaihan\SteadfastCourier;

use AhRaihan\SteadfastCourier\Facades\SteadfastCourier;
use AhRaihan\SteadfastCourier\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class SteadfastServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/steadfast.php',
            'steadfast'
        );

        // Bind main client singleton
        $this->app->singleton('steadfast', function ($app) {
            return new SteadfastClient;
        });

        $this->app->alias('steadfast', SteadfastClient::class);
        $this->app->alias('steadfast', 'steadfast-courier');

        // Backward compatibility bindings
        if (! class_exists('SteadFast\SteadFastCourierLaravelPackage\SteadfastCourier')) {
            class_alias(SteadfastClient::class, 'SteadFast\\SteadFastCourierLaravelPackage\\SteadfastCourier');
        }

        if (! class_exists('SteadFast\SteadFastCourierLaravelPackage\Facades\SteadfastCourier')) {
            class_alias(SteadfastCourier::class, 'SteadFast\\SteadFastCourierLaravelPackage\\Facades\\SteadfastCourier');
        }
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Publish config file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/steadfast.php' => config_path('steadfast.php'),
            ], 'steadfast-config');

            $this->publishes([
                __DIR__.'/../config/steadfast.php' => config_path('steadfast-courier.php'),
            ], 'steadfast-courier-config');
        }

        // Register Webhook routes
        if ((bool) config('steadfast.webhook.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');
        }

        // Register route middleware alias
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('steadfast.webhook.signature', VerifyWebhookSignature::class);
    }
}
