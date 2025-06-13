<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\User;

interface UserRepositoryInterface extends CachedRepositoryInterface
{
    public function findById(int $id): ?int;

    public function getAllIds(): array;

    public function save(User $user): User;
}
