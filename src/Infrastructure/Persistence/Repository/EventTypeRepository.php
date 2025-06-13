<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\UserAnalytics\Entity\EventType;
use App\Domain\UserAnalytics\Repository\EventTypeRepositoryInterface;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\DbConnection\Db;
use PDO;

class EventTypeRepository implements EventTypeRepositoryInterface
{
    #[Cacheable(prefix: 'event_types', value: '_id_by_name_#{name}', ttl: 3600)]
    public function findIdByName(string $name): ?int
    {
        $statement = Db::connection()
            ->getPdo()
            ->prepare('SELECT id FROM event_types WHERE name = :name LIMIT 1');

        $statement->execute(['name' => $name]);
        $id = $statement->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    #[Cacheable(prefix: 'event_types', value: '_map', ttl: 3600)]
    public function getNameToIdMap(): array
    {
        $statement = Db::connection()
            ->getPdo()
            ->query('SELECT name, id FROM event_types');

        return $statement?->fetchAll(PDO::FETCH_KEY_PAIR) ?? [];
    }

    public function save(EventType $eventType): EventType
    {
        $eventType->save();
        $this->invalidateCaches();

        return $eventType;
    }

    #[CacheEvict(prefix: 'event_types', all: true)]
    public function invalidateCaches(): void
    {
    }
}
