<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\UserAnalytics\Entity\User;
use App\Domain\UserAnalytics\Repository\CachedRepositoryInterface;
use App\Infrastructure\Generator\RandomNameGenerator;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use PDO;

class UserRepository implements CachedRepositoryInterface
{
    #[Inject]
    protected RandomNameGenerator $randomNameGenerator;

    #[Cacheable(prefix: 'users', value: '_by_id_#{id}', ttl: 3600)]
    public function findOrCreate(int $id): int
    {
        $statement = Db::connection()
            ->getPdo()
            ->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');

        $statement->execute(['id' => $id]);
        $result = $statement->fetchColumn();
        if (!$result) {
            $name = $this->randomNameGenerator->generate();

            $insertSql = 'INSERT INTO users (id, name) VALUES (:id, :name)';
            $insertStatement = Db::connection()->getPdo()->prepare($insertSql);
            $insertStatement->execute([
                'id' => $id,
                'name' => $name,
            ]);

            return $id;
        }

        return $result;
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
