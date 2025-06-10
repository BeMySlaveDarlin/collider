<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => (int) env('REDIS_POOL_MIN_CONNECTIONS', 1),
            'max_connections' => (int) env('REDIS_POOL_MAX_CONNECTIONS', 10),
            'connect_timeout' => (float) env('REDIS_POOL_CONNECT_TIMEOUT', 10.0),
            'wait_timeout' => (float) env('REDIS_POOL_WAIT_TIMEOUT', 3.0),
            'heartbeat' => (int) env('REDIS_POOL_HEARTBEAT', 60),
            'max_idle_time' => (float) env('REDIS_POOL_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            'serializer' => Redis::SERIALIZER_PHP,
            'scan' => Redis::SCAN_RETRY,
        ],
    ],
];
