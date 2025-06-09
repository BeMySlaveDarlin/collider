<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'redis'),

    'prefix' => env('CACHE_PREFIX', 'cache:'),

    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', 'redis'),
            'port' => (int)env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', ''),
            'database' => env('REDIS_DATABASE', 0),
            'timeout' => 5.0,
            'retry_interval' => 100,
            'read_timeout' => 60,
        ],
    ],

    'ttl' => [
        'default' => (int)env('CACHE_TTL', 3600),
        'short' => 300,
        'medium' => 1800,
        'long' => 86400,
    ],

    'tags' => [
        'users' => ['user:', 'users:'],
        'events' => ['event:', 'events:'],
        'stats' => ['stats:', 'analytics:'],
    ],

    'serialization' => [
        'method' => 'serialize',
        'compression' => false,
    ],

    'pools' => [
        'min_connections' => (int)env('REDIS_POOL_MIN_CONNECTIONS', 1),
        'max_connections' => (int)env('REDIS_POOL_MAX_CONNECTIONS', 10),
        'connection_timeout' => 5.0,
        'idle_timeout' => 300,
    ],
];
