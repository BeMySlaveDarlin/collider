<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\User;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Database\Model\Collection;

class UserRepository
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByName(string $name): ?User
    {
        return User::where('name', $name)->first();
    }

    public function findOrCreateByName(string $name): User
    {
        $user = $this->findByName($name);

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'created_at' => 'now()',
            ]);
        }

        return $user;
    }

    /**
     * @return Collection<int, User>
     */
    #[Cacheable(prefix: 'users', value: '_all', ttl: 3600)]
    public function findAll(): Collection
    {
        return User::all();
    }

    #[Cacheable(prefix: 'users', value: '_all_ids', ttl: 3600)]
    public function getAllIds(): array
    {
        return User::query()->pluck('id')->toArray();
    }

    public function save(User $user): User
    {
        $user->save();

        $this->invalidateCaches($user);

        return $user;
    }

    #[CacheEvict(prefix: 'users')]
    private function invalidateCaches(User $user): void
    {
    }
}
