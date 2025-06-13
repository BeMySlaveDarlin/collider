<?php

declare(strict_types=1);

use App\Infrastructure\Server\CachedServer;
use Hyperf\Server\Event;
use Hyperf\Server\ServerInterface;
use Swoole\Constant;

return [
    'mode' => SWOOLE_PROCESS,
    'servers' => [
        [
            'name' => 'http',
            'type' => ServerInterface::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [CachedServer::class, 'onRequest'],
            ],
            'options' => [
                'enable_request_lifecycle' => true,
            ],
        ],
    ],
    'settings' => [
        Constant::OPTION_DEBUG_MODE => false,
        Constant::OPTION_ENABLE_COROUTINE => true,
        Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 4,
        Constant::OPTION_PID_FILE => BASE_PATH . '/runtime/app.pid',
        Constant::OPTION_OPEN_TCP_NODELAY => true,
        Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
        Constant::OPTION_TCP_FASTOPEN => true,
        Constant::OPTION_MAX_REQUEST => 0,
        Constant::OPTION_MAX_COROUTINE => 1000000,
        Constant::OPTION_PACKAGE_MAX_LENGTH => 128 * 1024 * 1024,
        Constant::OPTION_SOCKET_BUFFER_SIZE => 128 * 1024 * 1024,
        Constant::OPTION_BUFFER_OUTPUT_SIZE => 256 * 1024 * 1024,
        Constant::OPTION_HTTP_PARSE_COOKIE => false,
        Constant::OPTION_HTTP_PARSE_POST => true,
        Constant::OPTION_HTTP_COMPRESSION => true,
        Constant::OPTION_ENABLE_UNSAFE_EVENT => true,
        Constant::OPTION_DISCARD_TIMEOUT_REQUEST => true,
        Constant::OPTION_RELOAD_ASYNC => true,
        Constant::OPTION_MAX_WAIT_TIME => 1,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
        Event::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
        Event::ON_WORKER_EXIT => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
    ],
];
