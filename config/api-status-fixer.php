<?php

return [
    'fix_status_ranges' => [
        '20x' => false,
        '30x' => false,
        '40x' => true,
        '50x' => true,
    ],

    // Middleware alias (customizable by the user)
    'middleware_alias' => env('API_STATUS_FIXER_ALIAS', 'api-status-fixer'),
];
