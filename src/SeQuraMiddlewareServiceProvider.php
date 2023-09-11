<?php

namespace SeQura\Middleware;

use Illuminate\Support\ServiceProvider;

class SeQuraMiddlewareServiceProvider extends ServiceProvider
{
    public const RESOURCE_NAMESPACE = 'sequra';

    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', static::RESOURCE_NAMESPACE);
        $this->publishes(
            [
                __DIR__ . '/database/migrations/' => database_path('migrations'),
            ],
            'migrations'
        );

        $this->loadRoutesFrom(__DIR__ . '/routes/sequra.php');
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        include __DIR__ . '/routes/sequra.php';
    }
}
