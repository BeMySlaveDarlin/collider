<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\UserAnalytics\Repository\EventTypeRepositoryInterface;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\DbConnection\Db;
use PDO;

class EventTypeRepository implements EventTypeRepositoryInterface
{
    #[Cacheable(prefix: 'event_types', value: '_id_by_name_#{name}', ttl: 3600)]
    public function findOrCreate(string $name, ?int $id = null): int
    {
        $statement = Db::connection()->getPdo()->prepare('SELECT id FROM event_types WHERE name = :name LIMIT 1');
        $statement->execute(['name' => $name]);
        $result = $statement->fetchColumn();
        if (!$result) {
            if ($id !== null) {
                $insertSql = 'INSERT INTO event_types (id, name) VALUES (:id, :name)';
                $insertStatement = Db::connection()->getPdo()->prepare($insertSql);
                $insertStatement->execute(['id' => $id, 'name' => $name]);
            } else {
                $insertSql = 'INSERT INTO event_types (name) VALUES (:name) RETURNING id';
                $insertStatement = Db::connection()->getPdo()->prepare($insertSql);
                $insertStatement->execute(['name' => $name]);
                $id = $insertStatement->fetchColumn();
            }

            return $id;
        }

        return $result;
    }

    #[Cacheable(prefix: 'event_types', value: '_map', ttl: 3600)]
    public function getNameToIdMap(): array
    {
        $statement = Db::connection()
            ->getPdo()
            ->query('SELECT name, id FROM event_types');

        return $statement?->fetchAll(PDO::FETCH_KEY_PAIR) ?? [];
    }

    #[CacheEvict(prefix: 'event_types', all: true)]
    public function invalidateCaches(): void
    {
    }
}
