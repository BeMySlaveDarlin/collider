<?php

declare(strict_types=1);

namespace App\Infra\Db;

use PDO;
use Swoole\ConnectionPool as SwooleConnectionPool;

final class ConnectionPool
{
    private SwooleConnectionPool $pool;
    private array $config;
    private int $freeConnections;

    public function __construct(
        string $poolName,
        callable $connectionFactory,
        int $size = 100
    ) {
        $this->freeConnections = $size;
        $this->pool = new SwooleConnectionPool($connectionFactory, $size);
        $this->config = [
            'name' => $poolName,
            'size' => $size,
        ];
    }

    public function get(): PDO
    {
        $this->freeConnections--;

        return $this->pool->get();
    }

    public function put(PDO $connection): void
    {
        $this->freeConnections++;
        $this->pool->put($connection);
    }

    public function close(): void
    {
        $this->freeConnections = 0;
        $this->pool->close();
    }

    public function getStats(): array
    {
        return [
            'name' => $this->config['name'],
            'size' => $this->config['size'],
            'connections' => $this->freeConnections,
        ];
    }
}
