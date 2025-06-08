<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'redis'),
    'aliases' => [],
    'typeAliases' => [
        'redis' => 'redis',
    ],
    'storages' => [
        'array' => [
            'type' => 'array',
        ],
        'file' => [
            'type' => 'file',
            'path' => directory('cache'),
        ],
        'redis' => [
            'type' => 'redis',
            'server' => [
                'host' => env('REDIS_HOST', 'redis'),
                'port' => (int)env('REDIS_PORT', 6379),
                'password' => env('REDIS_PASSWORD', ''),
                'database' => (int)env('REDIS_DATABASE', 0),
            ],
        ],
    ],
];
