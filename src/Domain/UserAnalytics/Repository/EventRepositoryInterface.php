<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

interface EventRepositoryInterface extends CachedRepositoryInterface
{
    public function countAll(?int $userId = null): int;

    public function findByUserId(int $userId, int $limit = 1000): array;

    public function findWithPagination(int $limit, int $offset): array;

    public function getStats(int $limit = 3, ?string $from = null, ?string $to = null, ?string $eventType = null): array;

    public function findBeforeDate(string $date): int;

    public function deleteByBeforeDate(string $date): void;
}
