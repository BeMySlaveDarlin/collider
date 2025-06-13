<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\UserAnalytics\Entity\User;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\DbConnection\Db;
use PDO;

class UserRepository
{
    #[Cacheable(prefix: 'users', value: '_by_id_#{id}', ttl: 3600)]
    public function findById(int $id): ?int
    {
        $statement = Db::connection()
            ->getPdo()
            ->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');

        $statement->execute(['id' => $id]);
        $result = $statement->fetchColumn();

        return $result !== false ? (int) $result : null;
    }

    #[Cacheable(prefix: 'users', value: '_all_ids', ttl: 3600)]
    public function getAllIds(): array
    {
        return Db::connection()
            ->getPdo()
            ->query('SELECT id FROM users')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function save(User $user): User
    {
        $user->save();

        $this->invalidateCaches();

        return $user;
    }

    #[CacheEvict(prefix: 'users')]
    public function invalidateCaches(): void
    {
    }
}
