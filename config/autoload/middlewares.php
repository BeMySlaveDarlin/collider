<?php

declare(strict_types=1);

use App\Infrastructure\Middleware\HttpRequestLoggerMiddleware;

return [
    'http' => [
        HttpRequestLoggerMiddleware::class,
    ],
];
