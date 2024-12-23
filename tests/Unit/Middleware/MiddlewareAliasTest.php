<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use RogerioPereira\ApiStatusFixer\Middleware\ApiResponseFixerMiddleware;
use RogerioPereira\ApiStatusFixer\ServiceProvider;

beforeEach(function () {
    // Set the status ranges
    Config::set('api-status-fixer.fix_status_ranges', [
        '20x' => true,
        '30x' => false,
        '40x' => true,
        '50x' => true,
    ]);
});


function aliasDataProvider(): array
{
    return [
        'no alias 201' => [
            201,
        ],
        'no alias 400' => [
            400,
        ],
        'no alias 500' => [
            500,
        ],
        'alias 201' => [
            201,
            'fix-status'
        ],
        'alias 400' => [
            400,
            'fix-status'
        ],
        'alias 500' => [
            500,
            'fix-status'
        ],
    ];
}


it('registers the middleware alias', function (
    int $statusCode,
    string $middlewareAlias = 'api-status-fixer'
) {
    Config::set('api-status-fixer.middleware_alias', $middlewareAlias);

    //Make sure Middleware is registered
    $this->app->make(Router::class)->aliasMiddleware($middlewareAlias, ApiResponseFixerMiddleware::class);

    Route::middleware($middlewareAlias)
        ->get('/test', function () use ($statusCode) {
            return response()->json(['message' => 'Middleware works'], $statusCode);
        });

    $this->get('/test')
        ->assertStatus(200)
        ->assertJson([
            'message' => 'Middleware works',
            'httpStatusCode' => $statusCode,
        ]);
})
->with(aliasDataProvider());