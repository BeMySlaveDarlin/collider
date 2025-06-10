<?php

declare(strict_types=1);

use App\Infra\Http\Middleware\HttpRequestMiddleware;

return [
    'basePath' => '/',
    'headers' => [
        'Content-Type' => 'application/json',
    ],
    'middleware' => [
        HttpRequestMiddleware::class,
    ],
];
