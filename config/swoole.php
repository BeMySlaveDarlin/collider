<?php

declare(strict_types=1);

return [
    'host' => env('SWOOLE_HOST', '0.0.0.0'),
    'port' => env('SWOOLE_PORT', 9501),
    'worker_num' => env('SWOOLE_WORKER_NUM', 'auto') === 'auto' ? swoole_cpu_num() * 2 : (int)env('SWOOLE_WORKER_NUM'),
    'task_worker_num' => (int)env('SWOOLE_TASK_WORKER_NUM', 4),
    'max_request' => (int)env('SWOOLE_MAX_REQUEST', 10000),
    'max_coroutine' => (int)env('SWOOLE_MAX_COROUTINE', 10000),
    'package_max_length' => 2 * 1024 * 1024,
    'buffer_output_size' => 2 * 1024 * 1024,
    'socket_buffer_size' => 128 * 1024 * 1024,
    'log_file' => directory('logs') . 'swoole.log',
    'log_level' => SWOOLE_LOG_INFO,
    'pid_file' => directory('runtime') . 'swoole.pid',
    'enable_static_handler' => (bool)env('SWOOLE_ENABLE_STATIC_HANDLER', true),
    'document_root' => env('SWOOLE_DOCUMENT_ROOT', directory('public')),
    'static_handler_locations' => ['/assets'],

    'coroutine' => [
        'hook_flags' => SWOOLE_HOOK_ALL,
        'options' => [
            'enable_preemptive_scheduler' => true,
        ],
    ],

    'pools' => [
        'database' => [
            'min' => (int)env('DB_POOL_MIN_CONNECTIONS', 1),
            'max' => (int)env('DB_POOL_MAX_CONNECTIONS', 10),
        ],
        'redis' => [
            'min' => (int)env('REDIS_POOL_MIN_CONNECTIONS', 1),
            'max' => (int)env('REDIS_POOL_MAX_CONNECTIONS', 10),
        ],
    ],
];
