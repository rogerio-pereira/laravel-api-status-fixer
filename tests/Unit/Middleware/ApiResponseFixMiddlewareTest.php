<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RogerioPereira\ApiStatusFixer\Middleware\ApiResponseFixerMiddleware;
use Illuminate\Support\Facades\Config;

/*
 * =====================================================================================================================
 * Auxiliary Methods
 * =====================================================================================================================
 */
 #region auxiliaryMethods

/**
 * This function defines api-status-config during Test Runtime
 * 
 * middlewareConfig['20x'] bool Defines if 20x status code should be converted
 * middlewareConfig['30x'] bool Defines if 30x status code should be converted
 * middlewareConfig['40x'] bool Defines if 40x status code should be converted
 * middlewareConfig['50x'] bool Defines if 50x status code should be converted
 *
 * @param array $middlewareConfig
 * @return void
 */
function setMiddlewareConfig(array $middlewareConfig): void
{
    Config::set('api-status-fixer.fix_status_ranges', [
        '20x' => $middlewareConfig['20x'],
        '30x' => $middlewareConfig['30x'],
        '40x' => $middlewareConfig['40x'],
        '50x' => $middlewareConfig['50x'],
    ]);
}

/**
 * Provides test cases for middleware behavior tests.
 *
 * @return array
 */
function testDataProviders(): array
{
    return [
        /*
         * =============================================================================================================
         * Middleware disabled
         * =============================================================================================================
         */
        'shouldnt fix 201' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => false,
                '50x' => false,
            ],
            [
                'name' => 'Test',
            ],
            [
                'name' => 'Test',
            ],
            201,
            201,
        ],
        'shouldnt fix 40x' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => false,
                '50x' => false,
            ],
            [
                'error' => 'Bad Request',
            ],
            [
                'error' => 'Bad Request',
            ],
            400,
            400,
        ],
        'shouldnt fix 50x' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => false,
                '50x' => false,
            ],
            [
                'error' => 'Internal Server Error',
            ],
            [
                'error' => 'Internal Server Error',
            ],
            500,
            500,
        ],
        /*
         * =============================================================================================================
         * Middleware enabled
         * =============================================================================================================
         */
        // 20x
        'should fix 201' => [
            [
                '20x' => true,
                '30x' => false,
                '40x' => true,
                '50x' => false,
            ],
            [
                'name' => 'Test',
            ],
            [
                'name' => 'Test',
                'httpStatus' => 201,
            ],
            201,
            200,
        ],
        'should fix 204' => [
            [
                '20x' => true,
                '30x' => false,
                '40x' => true,
                '50x' => false,
            ],
            [],
            [
                // [], //It won't be included
                'httpStatus' => 204,
            ],
            204,
            200,
        ],
        // 40x
        'should fix 400' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => true,
                '50x' => false,
            ],
            [
                'message' => 'Bad Request',
            ],
            [
                'message' => 'Bad Request',
                'httpStatus' => 400,
            ],
            400,
            200,
        ],
        'should fix 401' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => true,
                '50x' => false,
            ],
            [
                'message' => 'Unauthorized',
            ],
            [
                'message' => 'Unauthorized',
                'httpStatus' => 401,
            ],
            401,
            200,
        ],
        'should fix 403' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => true,
                '50x' => false,
            ],
            [
                'message' => 'Forbidden',
            ],
            [
                'message' => 'Forbidden',
                'httpStatus' => 403,
            ],
            403,
            200,
        ],
        'should fix 404' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => true,
                '50x' => false,
            ],
            [
                'message' => 'Not Found',
            ],
            [
                'message' => 'Not Found',
                'httpStatus' => 404,
            ],
            404,
            200,
        ],
        'should fix 422 with validation errors' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => true,
                '50x' => false,
            ],
            [
                'errors' => [
                    'name' => 'The name field is required.',
                ],
            ],
            [
                'errors' => [
                    'name' => 'The name field is required.',
                ],
                'httpStatus' => 422,
            ],
            422,
            200,
        ],
        // 50x
        'should fix 500' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => false,
                '50x' => true,
            ],
            [
                'message' => 'Internal Server Error',
            ],
            [
                'message' => 'Internal Server Error',
                'httpStatus' => 500,
            ],
            500,
            200,
        ],
        'should fix 502' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => false,
                '50x' => true,
            ],
            [
                'message' => 'Bad Gateway',
            ],
            [
                'message' => 'Bad Gateway',
                'httpStatus' => 502,
            ],
            502,
            200,
        ],
        'should fix 503' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => false,
                '50x' => true,
            ],
            [
                'message' => 'Service Unavailable',
            ],
            [
                'message' => 'Service Unavailable',
                'httpStatus' => 503,
            ],
            503,
            200,
        ],
        'should fix 504' => [
            [
                '20x' => false,
                '30x' => false,
                '40x' => false,
                '50x' => true,
            ],
            [
                'message' => 'Gateway Timeout',
            ],
            [
                'message' => 'Gateway Timeout',
                'httpStatus' => 504,
            ],
            504,
            200,
        ],
    ];
}
#endregion

/*
 * =====================================================================================================================
 * Tests
 * =====================================================================================================================
 */
test('middleware behavior', function (
                                            array $middlewareConfig,
                                            array $originalResponseData,
                                            array $fixedResponseData,
                                            int $originalStatusCode,
                                            int $expectedStatusCode,
                                        ) 
{
    // Set middleware config dynamically
    setMiddlewareConfig($middlewareConfig);

    // Create a middleware instance
    $middleware = new ApiResponseFixerMiddleware();

    // Simulate a request and the original response
    $request = Request::create('/test', 'GET');
    $response = $middleware->handle($request, function() use ($originalResponseData, $originalStatusCode) {
        return response()->json($originalResponseData, $originalStatusCode);
    });
    $responseStatusCode = $response->getStatusCode();
    $responseData = $response->getData(true);

    // Assert the fixed response status
    expect($responseStatusCode)
        ->toBe($expectedStatusCode);

    // Assert the fixed response data
    expect($responseData)
        ->toBe($fixedResponseData);
})
->with(testDataProviders());
