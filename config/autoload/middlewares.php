<?php

declare(strict_types=1);

use App\Application\Middleware\HttpRequestLoggerMiddleware;

return [
    'http' => [
        HttpRequestLoggerMiddleware::class,
    ],
];
