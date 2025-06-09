<?php

declare(strict_types=1);

namespace App\Infra\Redis;

use Redis;

interface RedisPoolInterface
{
    public function get(): Redis;

    public function put(Redis $connection): void;

    public function close(): void;
}
