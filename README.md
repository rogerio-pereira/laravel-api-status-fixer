# API Status Fixer

![License](https://img.shields.io/badge/license-MIT-green) 
![Workflow Status](https://github.com/rogerio-pereira/laravel-api-status-fixer/actions/workflows/ci.yml/badge.svg) 
![Stars](https://img.shields.io/github/stars/rogerio-pereira/laravel-api-status-fixer) 
![Contributors](https://img.shields.io/github/contributors/rogerio-pereira/laravel-api-status-fixer)

## Project Description

`API Status Fixer` is a Laravel middleware package that ensures consistent API responses by converting HTTP status codes like `4xx` and `5xx` into `200` responses. The original status code and message are embedded in the response payload, making it easier for front-end applications to handle errors uniformly.

## Installation

Install the package via Composer:

```bash
composer require rogeriopereira/api-status-fixer
```

## Configuration

After installation, publish the configuration file:

```bash
php artisan vendor:publish --provider="RogerioPereira\ApiStatusFixer\ServiceProvider" --tag=config
```

The configuration file `api-status-fixer.php` will be available in the `config` directory. You can customize the following options:

### Example Configuration:

```php
return [
    'fix_status_ranges' => [
        '20x' => false, // Don't convert 20x responses
        '30x' => false, // Don't convert 30x responses
        '40x' => true,  // Convert 40x responses
        '50x' => true,  // Convert 50x responses
    ],

    // Middleware alias (customizable by the user)
    'middleware_alias' => env('API_STATUS_FIXER_ALIAS', 'api-status-fixer'),
];
```

### Custom Middleware Alias

You can set a custom middleware alias by changing the `middleware_alias` in the configuration file or using an environment variable:

```env
API_STATUS_FIXER_ALIAS=custom-middleware-alias
```

## Usage

### Register Middleware

The package automatically registers the middleware using the configured alias. You can use the alias in your routes or globally.

#### Global Middleware

To apply the middleware globally, add it to the `$middleware` array in `app/Http/Kernel.php`:

```php
protected $middleware = [
    // Other middleware
    \RogerioPereira\ApiStatusFixer\Middleware\ApiResponseFixerMiddleware::class,
];
```

#### Route Middleware

To apply the middleware to specific routes, use the alias defined in your configuration file:

```php
Route::middleware('api-status-fixer')->group(function () {
    Route::get('/example', [ExampleController::class, 'index']);
});
```

If you’ve customized the alias:

```php
Route::middleware('custom-middleware-alias')->group(function () {
    Route::get('/example', [ExampleController::class, 'index']);
});
```

### Example Behavior (Failed validation request)

#### Input:
```json
{
  "name": null
}
```

#### Original Response:
```http
HTTP/1.1 422 Unprocessable Content
Content-Type: application/json

{
    "errors": [
        "name": [
            "The field name is required."
        ]
    ]
```

#### Transformed Response:
```http
HTTP/1.1 200 OK
Content-Type: application/json

{
    "errors": [
        "name": [
            "The field name is required."
        ]
    ]
    "httpStatusCode": 422,
    "httpStatus": "Unprocessable Content"
}
```

## Testing

This package includes comprehensive automated tests.

### Running Tests

To execute the tests, run:

```bash
composer test
```

### Test Environment

The package uses **Orchestra Testbench** to simulate a Laravel environment during testing. Middleware aliases and configurations are tested dynamically.

### Supported Laravel Version

This package is compatible with Laravel 10. Ensure your environment uses Laravel 10 for proper functionality.

## Features

- Converts HTTP status codes to `200` while embedding original codes and messages in the response payload.
- Fully configurable via `config/api-status-fixer.php`.
- Automatically registers middleware aliases.
- Includes robust automated tests with CI/CD support.

## Contributing

We welcome contributions! To get started:

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Submit a pull request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
