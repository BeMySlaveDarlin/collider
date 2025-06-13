<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\User;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\DbConnection\Db;
use PDO;

class UserRepository
{
    public function findById(int $id): ?User
    {
        return User::find($id);
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
