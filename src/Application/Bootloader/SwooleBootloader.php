<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Infra\Db\ConnectionPool;
use App\Infra\Db\DbPoolInterface;
use App\Infra\Redis\RedisPoolInterface;
use App\Infra\Swoole\CoroutineHandler;
use PDO;
use Redis;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;

final class SwooleBootloader extends Bootloader
{
    protected const array SINGLETONS = [
        ConnectionPool::class => ConnectionPool::class,
        CoroutineHandler::class => CoroutineHandler::class,
    ];

    public function boot(Container $container): void
    {
        $container->bindSingleton(DbPoolInterface::class, function (Container $container) {
            $config = $container->get(ConfiguratorInterface::class)->getConfig('database');
            $dbConfig = $config['databases']['default'] ?? [];

            return new ConnectionPool('database', function () use ($dbConfig) {
                $dsn = sprintf(
                    '%s:host=%s;port=%s;dbname=%s',
                    $dbConfig['driver'] ?? 'pgsql',
                    $dbConfig['host'] ?? 'localhost',
                    $dbConfig['port'] ?? 5432,
                    $dbConfig['database'] ?? 'app'
                );

                return new PDO(
                    $dsn,
                    $dbConfig['username'] ?? 'postgres',
                    $dbConfig['password'] ?? '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            }, 10);
        });

        $container->bindSingleton(RedisPoolInterface::class, function (Container $container) {
            $config = $container->get(ConfiguratorInterface::class)->getConfig('cache');
            $redisConfig = $config['stores']['redis'] ?? [];

            return new ConnectionPool('redis', function () use ($redisConfig) {
                $redis = new Redis();
                $redis->connect(
                    $redisConfig['host'] ?? 'localhost',
                    $redisConfig['port'] ?? 6379
                );

                if (!empty($redisConfig['password'])) {
                    $redis->auth($redisConfig['password']);
                }

                if (!empty($redisConfig['database'])) {
                    $redis->select($redisConfig['database']);
                }

                return $redis;
            }, 5);
        });
    }
}
