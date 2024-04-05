<?php

namespace SeQura\Middleware;

use Illuminate\Support\ServiceProvider;
use SeQura\Middleware\Http\Middleware\AdminAPIValidator;

class SeQuraMiddlewareServiceProvider extends ServiceProvider
{
    public const RESOURCE_NAMESPACE = 'sequra';

    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/database/migrations/' => database_path('migrations'),
            ],
            'migrations'
        );

        $this->loadViewsFrom(__DIR__ . '/resources/views', static::RESOURCE_NAMESPACE);

        $this->loadRoutesFrom(__DIR__ . '/routes/sequra.php');

        $this->loadMiddlewareAlias();
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        include __DIR__ . '/routes/sequra.php';
    }

    /**
     * Prevents alias loading if override is provided.
     *
     * @return void
     */
    private function loadMiddlewareAlias(): void
    {
        $router = $this->app['router'];
        $middlewareStack = $router->getMiddleware();

        if (!isset($middlewareStack['sequra.auth'])) {
            $router->aliasMiddleware('sequra.auth', AdminAPIValidator::class);
        }
    }
}
