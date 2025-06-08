<?php

declare(strict_types=1);

namespace App\Infra\Swoole;

use Swoole\Coroutine;

final class CoroutineHandler
{
    public function handle(callable $callback): void
    {
        if ($this->isInCoroutine()) {
            $callback();
        } else {
            Coroutine::create($callback);
        }
    }

    public function isInCoroutine(): bool
    {
        return $this->getCurrentCoId() > 0;
    }

    public function getCurrentCoId(): int
    {
        return Coroutine::getCid();
    }

    public function defer(callable $callback): void
    {
        Coroutine::defer($callback);
    }
}
