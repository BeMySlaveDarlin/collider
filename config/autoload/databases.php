<?php

declare(strict_types=1);

use Hyperf\Codec\Packer\IgbinarySerializerPacker;
use Hyperf\ModelCache\Handler\RedisHandler;

use function Hyperf\Support\env;

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'pgsql'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => (int) env('DB_PORT', 5432),
        'database' => env('DB_DATABASE', 'collider_db'),
        'username' => env('DB_USERNAME', 'collider_user'),
        'password' => env('DB_PASSWORD', 'collider_secret'),
        'charset' => env('DB_CHARSET', 'utf8'),
        'collation' => env('DB_COLLATION', 'utf8'),
        'prefix' => env('DB_PREFIX', ''),
        'schema' => 'public',
        'sslmode' => 'prefer',
        'pool' => [
            'min_connections' => (int) env('DB_POOL_MIN_CONNECTIONS', 100),
            'max_connections' => (int) env('DB_POOL_MAX_CONNECTIONS', 1000),
            'connect_timeout' => (float) env('DB_POOL_CONNECT_TIMEOUT', 10.0),
            'wait_timeout' => (float) env('DB_POOL_WAIT_TIMEOUT', 3.0),
            'heartbeat' => (int) env('DB_POOL_HEARTBEAT', -1),
            'max_idle_time' => (float) env('DB_POOL_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
        'cache' => [
            'handler' => RedisHandler::class,
            'packer' => IgbinarySerializerPacker::class,
            'cache_key' => '{mc:%s:m:%s}:%s:%s',
            'prefix' => 'd:',
            'ttl' => 300,
            'empty_model_ttl' => 5,
            'load_script' => true,
        ],
    ],
];
