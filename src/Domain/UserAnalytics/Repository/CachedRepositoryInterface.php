<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

interface CachedRepositoryInterface
{
    public function invalidateCaches(): void;
}
