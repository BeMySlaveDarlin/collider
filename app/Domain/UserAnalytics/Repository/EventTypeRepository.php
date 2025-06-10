<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\EventType;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Database\Model\Collection;

class EventTypeRepository
{
    public function findById(int $id): ?EventType
    {
        return EventType::find($id);
    }

    public function findByName(string $name): ?EventType
    {
        return EventType::where('name', $name)->first();
    }

    public function findOrCreateByName(string $name): EventType
    {
        $eventType = $this->findByName($name);

        if (!$eventType) {
            $eventType = EventType::create(['name' => $name]);
        }

        return $eventType;
    }

    /**
     * @return Collection<int, EventType>
     */
    #[Cacheable(prefix: 'event_types', value: '_all', ttl: 1800)]
    public function findAll(): Collection
    {
        return EventType::all();
    }

    #[Cacheable(prefix: 'event_types', value: '_map', ttl: 3600)]
    public function getNameToIdMap(): array
    {
        return EventType::pluck('id', 'name')->toArray();
    }

    #[Cacheable(prefix: 'event_types', value: '_count', ttl: 1800)]
    public function countAll(): int
    {
        return EventType::count();
    }

    public function save(EventType $eventType): EventType
    {
        $eventType->save();
        $this->invalidateCaches($eventType);

        return $eventType;
    }

    #[CacheEvict(prefix: 'event_types', all: true)]
    private function invalidateCaches(EventType $eventType): void
    {
    }
}
