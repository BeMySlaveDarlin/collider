<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\EventType;
use App\Domain\UserAnalytics\Entity\EventTypeCached;
use App\Domain\UserAnalytics\Entity\EventTypeEntityInterface;
use App\Infra\Redis\RedisCacheService;
use Cycle\ORM\EntityManagerInterface;

final class CachedEventTypeRepository
{
    private const int DEFAULT_TTL = 3600;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventTypeRepository $repository,
        private readonly RedisCacheService $cache
    ) {
    }

    public function findById(int $id): ?EventTypeEntityInterface
    {
        return $this->cache->remember(
            "event_type:id:{$id}",
            fn() => $this->repository->findById($id),
            self::DEFAULT_TTL,
            [$this, 'typeFromArray']
        );
    }

    public function findByName(string $name): ?EventTypeEntityInterface
    {
        return $this->cache->remember(
            "event_type:name:{$name}",
            fn() => $this->repository->findByName($name),
            self::DEFAULT_TTL,
            [$this, 'typeFromArray']
        );
    }

    public function save(EventType $eventType): EventType
    {
        $this->entityManager->persist($eventType);
        $this->entityManager->run();

        $this->invalidateCache($eventType);

        return $eventType;
    }

    public function delete(EventType $eventType): void
    {
        $this->entityManager->delete($eventType);
        $this->entityManager->run();

        $this->invalidateCache($eventType);
    }

    public function typeFromArray(mixed $data = null): ?EventTypeEntityInterface
    {
        if (empty($data)) {
            return null;
        }

        if (!is_array($data) || empty($data['id'])) {
            return null;
        }

        $user = new EventTypeCached();
        $user->id = $data['id'];
        $user->name = $data['name'];

        return $user;
    }

    private function invalidateCache(EventTypeEntityInterface $eventType): void
    {
        $this->cache->delete("event_type:id:{$eventType->id}");
        $this->cache->delete("event_type:name:{$eventType->name}");
    }
}
