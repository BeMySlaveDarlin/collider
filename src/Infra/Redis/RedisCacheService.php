<?php

declare(strict_types=1);

namespace App\Infra\Redis;

use Redis;

final readonly class RedisCacheService
{
    public function __construct(
        private RedisPoolInterface $redisPool,
        private string $prefix = 'cache:'
    ) {
    }

    public function get(string $key): mixed
    {
        $redis = $this->redisPool->get();
        try {
            $data = $redis->get($this->prefix . $key);

            return $data !== false ? json_decode($data, true) : null;
        } finally {
            $this->redisPool->put($redis);
        }
    }

    public function set(string $key, mixed $value, int $ttl = 3600): Redis | bool
    {
        $redis = $this->redisPool->get();
        try {
            return $redis->setex($this->prefix . $key, $ttl, json_encode($value));
        } finally {
            $this->redisPool->put($redis);
        }
    }

    public function delete(string $key): Redis | int | bool
    {
        $redis = $this->redisPool->get();
        try {
            return $redis->del($this->prefix . $key) > 0;
        } finally {
            $this->redisPool->put($redis);
        }
    }

    public function deleteBatch(string $pattern): Redis | int | bool
    {
        if (empty($pattern)) {
            return 0;
        }

        $redis = $this->redisPool->get();
        try {
            $keys = $redis->keys($this->prefix . $pattern);
            if (empty($keys)) {
                return true;
            }

            return $redis->del($keys);
        } finally {
            $this->redisPool->put($redis);
        }
    }

    public function exists(string $key): bool
    {
        $redis = $this->redisPool->get();
        try {
            return $redis->exists($this->prefix . $key) > 0;
        } finally {
            $this->redisPool->put($redis);
        }
    }

    public function flush(): bool
    {
        $redis = $this->redisPool->get();
        try {
            $keys = $redis->keys($this->prefix . '*');

            return !$keys || $redis->del($keys) > 0;
        } finally {
            $this->redisPool->put($redis);
        }
    }

    public function increment(string $key, int $value = 1): Redis | int | false
    {
        $redis = $this->redisPool->get();
        try {
            return $redis->incrBy($this->prefix . $key, $value);
        } finally {
            $this->redisPool->put($redis);
        }
    }

    public function decrement(string $key, int $value = 1): Redis | int | false
    {
        $redis = $this->redisPool->get();
        try {
            return $redis->decrBy($this->prefix . $key, $value);
        } finally {
            $this->redisPool->put($redis);
        }
    }

    public function getMultiple(array $keys): array
    {
        $redis = $this->redisPool->get();
        try {
            $prefixedKeys = array_map(fn($key) => $this->prefix . $key, $keys);
            $values = $redis->mget($prefixedKeys);

            $result = [];
            foreach ($keys as $index => $key) {
                $result[$key] = $values[$index] !== false ? json_decode($values[$index], true) : null;
            }

            return $result;
        } finally {
            $this->redisPool->put($redis);
        }
    }

    public function setMultiple(array $items, int $ttl = 3600): bool
    {
        $redis = $this->redisPool->get();
        try {
            $pipe = $redis->multi(Redis::PIPELINE);

            foreach ($items as $key => $value) {
                $pipe->setex($this->prefix . $key, $ttl, json_encode($value));
            }

            $results = $pipe->exec();

            return !in_array(false, $results, true);
        } finally {
            $this->redisPool->put($redis);
        }
    }

    public function remember(string $key, callable $callback, int $ttl = 3600, ?callable $map = null): mixed
    {
        $value = $this->get($key);

        if (!empty($value)) {
            if ($map !== null) {
                return $map($value);
            }

            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }
}
