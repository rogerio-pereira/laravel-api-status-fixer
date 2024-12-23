<?php

namespace RogerioPereira\ApiStatusFixer\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseFixerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //Run all chain of middlewares before fixing the return
        $response = $next($request);

        $config = config('api-status-fixer.fix_status_ranges');

        $status = $response->getStatusCode();

        if (
            ($config['20x'] == true && $status >= 200 && $status <= 299) ||
            ($config['30x'] == true && $status >= 300 && $status <= 399) ||
            ($config['40x'] == true && $status >= 400 && $status <= 499) ||
            ($config['50x'] == true && $status >= 500 && $status <= 599)
        ) {
            $statusText = $this->getStatusName($status);

            //Define response Data
            $responseData = [
                ...$response->original,     //Add all original response items to new response, keeping their keys
                'httpStatusCode' => $status,
                'httpStatus' => $statusText,
            ];

            return response()->json($responseData, 200);
        }

        return $response;
    }

    /**
     * Return HTTP Status name based on their cUndocumented functionode
     *
     * @param integer $statusCode
     * @return string
     */
    private function getStatusName(int $statusCode): string
    {
        return Response::$statusTexts[$statusCode] ?? 'Unknown Status';
    }
}
