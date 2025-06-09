<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\Event;
use App\Infra\Redis\RedisCacheService;
use Cycle\ORM\EntityManagerInterface;
use DateTimeInterface;

final class CachedEventRepository
{
    private const int DEFAULT_TTL = 3600;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRepository $repository,
        private readonly RedisCacheService $cache
    ) {
    }

    public function findByUserId(int $uid, int $limit = 1000): array
    {
        return $this->cache->remember(
            "events:uid:{$uid}",
            fn() => $this->repository->findByUserId($uid, $limit),
            self::DEFAULT_TTL
        );
    }

    public function findWithPagination(int $limit, int $offset): array
    {
        return $this->cache->remember(
            "events:page:{$limit}:{$offset}",
            fn() => $this->repository->findWithPagination($limit, $offset),
            self::DEFAULT_TTL
        );
    }

    public function findBeforeDate(DateTimeInterface $date): int
    {
        return $this->cache->remember(
            "events:before:{$date->getTimestamp()}",
            fn() => $this->repository->findBeforeDate($date),
            self::DEFAULT_TTL
        );
    }

    public function getStats(
        int $limit = 3,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        ?int $eventTypeId = null
    ): array {
        $suffix = md5(serialize(['limit' => $limit, 'from' => $from, 'to' => $to, 'eventTypeId' => $eventTypeId]));

        return $this->cache->remember(
            "events:stats:{$suffix}",
            fn() => $this->repository->getStats($limit, $from, $to, $eventTypeId),
            self::DEFAULT_TTL
        );
    }

    public function countAll(): int
    {
        return $this->cache->remember(
            "events:count:all",
            fn() => $this->repository->countAll(),
            self::DEFAULT_TTL
        );
    }

    public function save(Event $event): Event
    {
        $this->entityManager->persist($event);
        $this->entityManager->run();

        $this->invalidateCache();

        return $event;
    }

    public function delete(Event $event): void
    {
        $this->entityManager->delete($event);
        $this->entityManager->run();

        $this->invalidateCache();
    }

    public function invalidateCache(): void
    {
        $this->cache->deleteBatch("events:*");
    }
}
