<?php

namespace RogerioPereira\ApiStatusFixer;

use Illuminate\Support\ServiceProvider as LaravelProvider;
use RogerioPereira\ApiStatusFixer\Middleware\ApiResponseFixerMiddleware;

class ServiceProvider extends LaravelProvider
{
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/api-status-fixer.php', 'api-status-fixer');
    }

    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/api-status-fixer.php' => config_path('api-status-fixer.php'),
        ], 'config');

        // Register the middleware alias using the configured name
        $alias = config('api-status-fixer.middleware_alias', 'api-status-fixer');
        $this->app['router']->aliasMiddleware($alias, ApiResponseFixerMiddleware::class);
    }
}
