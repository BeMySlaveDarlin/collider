<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\User;
use App\Domain\UserAnalytics\Entity\UserCached;
use App\Domain\UserAnalytics\Entity\UserEntityInterface;
use App\Infra\Redis\RedisCacheService;
use Cycle\ORM\EntityManagerInterface;

final class CachedUserRepository
{
    private const int DEFAULT_TTL = 3600;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $repository,
        private readonly RedisCacheService $cache
    ) {
    }

    public function findById(int $id): ?UserEntityInterface
    {
        return $this->cache->remember(
            "user:id:{$id}",
            fn() => $this->repository->findById($id),
            self::DEFAULT_TTL,
            [$this, 'userFromArray']
        );
    }

    public function findByName(string $name): ?UserEntityInterface
    {
        return $this->cache->remember(
            "user:name:{$name}",
            fn() => $this->repository->findByName($name),
            self::DEFAULT_TTL,
            [$this, 'userFromArray']
        );
    }

    public function save(User $user): UserEntityInterface
    {
        $this->entityManager->persist($user);
        $this->entityManager->run();

        $this->invalidateCache($user);

        return $user;
    }

    public function delete(User $user): void
    {
        $this->entityManager->delete($user);
        $this->entityManager->run();

        $this->invalidateCache($user);
    }

    public function userFromArray(mixed $data = null): ?UserEntityInterface
    {
        if (empty($data)) {
            return null;
        }

        if (!is_array($data) || empty($data['id'])) {
            return null;
        }

        $user = new UserCached();
        $user->id = $data['id'];
        $user->name = $data['name'];

        return $user;
    }

    private function invalidateCache(UserEntityInterface $user): void
    {
        $this->cache->delete("user:id:{$user->id}");
        $this->cache->delete("user:name:{$user->name}");
    }
}
