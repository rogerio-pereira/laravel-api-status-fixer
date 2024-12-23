<?php

namespace RogerioPereira\ApiStatusFixer;

use Illuminate\Support\ServiceProvider as LaravelProvider;

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
    }
}
