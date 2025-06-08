<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'redis'),
    'storages' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'prefix' => env('CACHE_PREFIX', 'cache:'),
            'ttl' => env('CACHE_TTL', 3600),
        ],
    ],
];
